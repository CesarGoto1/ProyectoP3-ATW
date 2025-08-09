# Proyecto final

## Contexto y propósito del trabajo de integración
La carrera de ITIN requiere un Sistema de Gestión Comercial para pymes locales que permita administrar clientes, productos, categorías, ventas y facturación – todo ello a través de una arquitectura PHP + Ext JS. El proyecto debe demostrar la integración de back‑end y front‑end sin usar ORMs; las operaciones de persistencia se implementarán únicamente con consultas SQL parametrizadas y procedimientos almacenados sobre PostgreSQL o MySQL.

## Modelo de dominio (capa de entidades)
| Entidad / Clase          | Tipo                                    | Atributos clave                                                                 | Observaciones                                             |
| ------------------------ | --------------------------------------- | ------------------------------------------------------------------------------- | --------------------------------------------------------- |
| **Cliente** (abstracta)  | `abstract class Cliente`                | `id:int`, `email:string`, `telefono:string`, `direccion:string`                 | No instanciable; factoriza atributos comunes.             |
| ├─ **PersonaNatural**    | `class PersonaNatural extends Cliente`  | `nombres`, `apellidos`, `cedula`                                                | Valida cédula (mód‑10 Ecuador, o equivalente).            |
| └─ **PersonaJuridica**   | `class PersonaJuridica extends Cliente` | `razonSocial`, `ruc`, `representanteLegal`                                      | Valida RUC.                                               |
| **Producto** (abstracta) | `abstract class Producto`               | `id`, `nombre`, `descripcion`, `precioUnitario`, `stock`, `idCategoria`         | Polimorfismo para futuros subtipos (p. ej. Servicio).     |
| ├─ **ProductoFisico**    |                                         | `peso`, `alto`, `ancho`, `profundidad`                                          | Maneja inventario.                                        |
| └─ **ProductoDigital**   |                                         | `urlDescarga`, `licencia`                                                       | No afecta inventario; gestiona claves/licencias.          |
| **Categoria**            | `class Categoria`                       | `id`, `nombre`, `descripcion`, `estado`                                         | Árbol simple (padre‑hijo) opcional.                       |
| **Venta**                | `class Venta`                           | `id`, `fecha`, `idCliente`, `total`, `estado`                                   | Estados: borrador, emitida, anulada.                      |
| **DetalleVenta**         | `class DetalleVenta`                    | `idVenta`, `lineNumber`, `idProducto`, `cantidad`, `precioUnitario`, `subtotal` | Se actualiza stock mediante trigger o lógica de servicio. |
| **Factura**              | `class Factura`                         | `id`, `idVenta`, `numero`, `claveAcceso`, `fechaEmision`, `estado`              | Puede integrarse con SRI/E‑invoice.                       |
| **Usuario**              | `class Usuario`                         | `id`, `username`, `passwordHash`, `estado`                                      | Contraseñas con Argon2id.                                 |
| **Rol**                  | `class Rol`                             | `id`, `nombre`                                                                  | Ej.: ADMIN, VENDEDOR, CONTADOR.                           |
| **Permiso**              | `class Permiso`                         | `id`, `codigo`                                                                  | CRUD\_CLIENTE, VER\_REPORTES, etc.                        |
| **RolPermiso**           | tabla puente                            | `idRol`, `idPermiso`                                                            | Configurable desde UI de administración.                  |


## Flujo resumido de una venta

1.	Vendedor busca cliente → agrega productos al carrito (UI).
2.	Front end invoca POST /api/ventas con JSON {cabecera, detalles}.
3.	Back end VentaService inicia transacción:
4.	Valida stock (SP sp_validar_stock).
5.	Inserta cabecera y detalles en tablas venta y detalle_venta.
6.	Actualiza stock (sp_descontar_stock).
7.	Devuelve idVenta. Front end muestra confirmación y ofrece Emitir factura.
8.	Si se emite: POST /api/facturas/{idVenta} → genera número, firma electrónica, PDF.
