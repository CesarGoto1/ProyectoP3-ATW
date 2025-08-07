--FIND ALL VENTA

DELIMITER $$

CREATE PROCEDURE sp_venta_find_all()
BEGIN
    SELECT 
        venta.id,
        venta.fecha,
        venta.`idCliente`,
        venta.total,
        venta.estado,
        cliente.email,
        cliente.telefono,
        cliente.direccion,
        cliente.`tipoCliente`,
        personanatural.nombres,
        personanatural.apellidos,
        personanatural.cedula,
        personajuridica.`razonSocial`,
        personajuridica.ruc,
        personajuridica.`representanteLegal`
    FROM VENTA
        JOIN cliente ON venta.`idCliente`=cliente.id
        LEFT JOIN personanatural ON cliente.id = personanatural.cliente_id AND cliente.`tipoCliente`='PersonaNatural'
        LEFT JOIN personajuridica ON cliente.id = personajuridica.cliente_id AND cliente.`tipoCliente`='PersonaJuridica'
        ORDER BY venta.id ASC;
END$$

CALL sp_venta_find_all();

----FIND ID VENTA

DELIMITER $$
CREATE PROCEDURE sp_venta_find_id(IN venta_id INT)
BEGIN
    SELECT
        venta.id,
        venta.fecha,
        venta.idCliente,
        venta.total,
        venta.estado,
        cliente.email,
        cliente.telefono,
        cliente.direccion,
        cliente.`tipoCliente`,
        CASE 
            WHEN cliente.`tipoCliente` = 'PersonaNatural' THEN personanatural.nombres
            ELSE NULL
        END AS nombres,
        CASE 
            WHEN cliente.`tipoCliente` = 'PersonaNatural' THEN personanatural.apellidos
            ELSE NULL
        END AS apellidos,
        CASE 
            WHEN cliente.`tipoCliente` = 'PersonaNatural' THEN personanatural.cedula
            ELSE NULL
        END AS cedula,
        CASE 
            WHEN cliente.`tipoCliente` = 'PersonaJuridica' THEN personajuridica.`razonSocial`
            ELSE NULL
        END AS razonSocial,
        CASE 
            WHEN cliente.`tipoCliente` = 'PersonaJuridica' THEN personajuridica.ruc
            ELSE NULL
        END AS ruc,
        CASE 
            WHEN cliente.`tipoCliente` = 'PersonaJuridica' THEN personajuridica.`representanteLegal`
            ELSE NULL
        END AS representanteLegal
    FROM venta
        JOIN cliente ON venta.`idCliente`=cliente.id
        LEFT JOIN personanatural ON cliente.id = personanatural.cliente_id
        LEFT JOIN personajuridica ON cliente.id = personajuridica.cliente_id
    WHERE venta.id = venta_id;
END$$
CALL sp_venta_find_id(6);

--CREATE VENTA
DELIMITER$$
CREATE PROCEDURE sp_venta_create(
    IN v_fecha DATE,
    IN v_idCliente INT,
    IN v_total DECIMAL(10,2),
    IN v_estado ENUM('borrador','emitida','anulada')
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
    END;

    START TRANSACTION;

    INSERT INTO venta(fecha, idCliente, total, estado)
    VALUES (v_fecha, v_idCliente, v_total, v_estado);

    SET @new_venta_id := LAST_INSERT_ID();

    COMMIT;

    SELECT @new_venta_id AS venta_id;
END$$

CALL sp_venta_create('2025-08-06', 3, 100.55, 'borrador');


--UPDATE VENTA

DELIMITER $$
CREATE PROCEDURE sp_update_venta(
    IN venta_id INT,
    IN v_fecha DATE,
    IN v_idCliente INT,
    IN v_total DECIMAL(10,2),
    IN v_estado ENUM('borrador','emitida','anulada')
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
    END;

    START TRANSACTION;

    UPDATE venta
    SET venta.fecha = v_fecha,
        venta.`idCliente` = v_idCliente,
        venta.total = v_total,
        venta.estado = v_estado
    WHERE id = venta_id;

    COMMIT;

    SELECT venta_id AS id_updated_venta;

END$$

CALL sp_update_venta(6,'2024-08-06', 1, 50.60, 'borrador');


--DELETE VENTA

DELIMITER $$
CREATE PROCEDURE sp_delete_venta(IN id_venta INT)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
    END;

    START TRANSACTION;

    DELETE FROM detalleventa WHERE idVenta = id_venta;

    DELETE FROM venta WHERE id=id_venta;

    COMMIT;

    SELECT 1 AS OK;
END$$
CALL sp_delete_venta(6);