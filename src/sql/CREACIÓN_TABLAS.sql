
-- Tabla para Cliente (Clase abstracta)
CREATE TABLE Cliente (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL,
    telefono VARCHAR(15),
    direccion VARCHAR(255),
    tipoCliente ENUM('PersonaNatural', 'PersonaJuridica') NOT NULL
);

-- Tabla para PersonaNatural (Subclase de Cliente)
CREATE TABLE PersonaNatural (
    cliente_id INT PRIMARY KEY,
    nombres VARCHAR(255) NOT NULL,
    apellidos VARCHAR(255) NOT NULL,
    cedula VARCHAR(10) NOT NULL,
    FOREIGN KEY (cliente_id) REFERENCES Cliente(id)
    ON DELETE CASCADE
);

-- Tabla para PersonaJuridica (Subclase de Cliente)
CREATE TABLE PersonaJuridica (
    cliente_id INT PRIMARY KEY,
    razonSocial VARCHAR(255) NOT NULL,
    ruc VARCHAR(13) NOT NULL,
    representanteLegal VARCHAR(255),
    FOREIGN KEY (cliente_id) REFERENCES Cliente(id)
    ON DELETE CASCADE
);

-- Tabla para Categoria
CREATE TABLE Categoria (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    idPadre INT,  -- Para definir jerarquía, NULL si es raíz
    FOREIGN KEY (idPadre) REFERENCES Categoria(id) ON DELETE SET NULL
);

-- Tabla para Producto (Clase abstracta)
CREATE TABLE Producto (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    precioUnitario DECIMAL(10, 2) NOT NULL,
    stock INT DEFAULT 0,
    idCategoria INT,
    TYPE ENUM('ProductoFisico', 'ProductoDigital') NOT NULL,
    FOREIGN KEY (idCategoria) REFERENCES Categoria(id)
);

-- Tabla para ProductoFisico (Subclase de Producto)
CREATE TABLE ProductoFisico (
    producto_id INT PRIMARY KEY,
    peso DECIMAL(10, 2),
    alto DECIMAL(10, 2),
    ancho DECIMAL(10, 2),
    profundidad DECIMAL(10, 2),
    FOREIGN KEY (producto_id) REFERENCES Producto(id)
    ON DELETE CASCADE
);

-- Tabla para ProductoDigital (Subclase de Producto)
CREATE TABLE ProductoDigital (
    producto_id INT PRIMARY KEY,
    urlDescarga VARCHAR(255) NOT NULL,
    licencia VARCHAR(255) NOT NULL,
    FOREIGN KEY (producto_id) REFERENCES Producto(id)
    ON DELETE CASCADE
);

-- Tabla para Venta
CREATE TABLE Venta (
    id INT PRIMARY KEY AUTO_INCREMENT,
    fecha DATE NOT NULL,
    idCliente INT,
    total DECIMAL(10, 2) NOT NULL,
    estado ENUM('borrador', 'emitida', 'anulada') DEFAULT 'borrador',
    FOREIGN KEY (idCliente) REFERENCES Cliente(id)
);

-- Tabla para DetalleVenta
CREATE TABLE DetalleVenta (
    id INT PRIMARY KEY AUTO_INCREMENT,
    idVenta INT,
    lineNumber INT,
    idProducto INT,
    cantidad INT NOT NULL,
    precioUnitario DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) AS (cantidad * precioUnitario) STORED,
    FOREIGN KEY (idVenta) REFERENCES Venta(id),
    FOREIGN KEY (idProducto) REFERENCES Producto(id)
);

-- Tabla para Factura
CREATE TABLE Factura (
    id INT PRIMARY KEY AUTO_INCREMENT,
    idVenta INT,
    numero VARCHAR(50) NOT NULL,
    claveAcceso VARCHAR(50) NOT NULL,
    fechaEmision DATE NOT NULL,
    estado ENUM('pendiente', 'emitida') DEFAULT 'pendiente',
    FOREIGN KEY (idVenta) REFERENCES Venta(id)
);

-- Tabla para Usuario
CREATE TABLE Usuario (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    passwordHash VARCHAR(255) NOT NULL,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo'
);

-- Tabla para Rol
CREATE TABLE Rol (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL
);

-- Tabla para Permiso
CREATE TABLE Permiso (
    id INT PRIMARY KEY AUTO_INCREMENT,
    codigo VARCHAR(50) NOT NULL
);

-- Tabla para RolPermiso (Tabla puente)
CREATE TABLE RolPermiso (
    idRol INT,
    idPermiso INT,
    PRIMARY KEY (idRol, idPermiso),
    FOREIGN KEY (idRol) REFERENCES Rol(id),
    FOREIGN KEY (idPermiso) REFERENCES Permiso(id)
);
