--FIND ALL DETALLE VENTA
DELIMITER $$

CREATE PROCEDURE sp_detalle_venta_list()
BEGIN
    SELECT
        detalleventa.`idVenta`,
        detalleventa.`lineNumber`,
        detalleventa.`idProducto`,
        detalleventa.cantidad,
        detalleventa.`precioUnitario`,
        detalleventa.subtotal,
        producto.nombre AS producto_nombre,
        producto.descripcion AS producto_descripcion,
        venta.fecha AS venta_fecha,
        venta.estado AS venta_estado
    FROM detalleventa
        JOIN producto ON producto.id = detalleventa.`idProducto`
        JOIN venta ON venta.id = detalleventa.`idVenta`
    ORDER BY detalleventa.`idVenta`, detalleventa.`lineNumber`;
END$$

CALL sp_detalle_venta_list();

--FIND ID VENTA
DROP PROCEDURE sp_detalle_venta_find_id;
DELIMITER $$
CREATE PROCEDURE sp_detalle_venta_find_id(IN venta_id INT)
BEGIN
    SELECT
        detalleventa.`idVenta`,
        detalleventa.`lineNumber`,
        detalleventa.`idProducto`,
        detalleventa.cantidad,
        detalleventa.`precioUnitario`,
        detalleventa.subtotal,
        producto.nombre AS producto_nombre,
        producto.descripcion AS producto_descripcion
    FROM detalleventa
        JOIN producto ON producto.id = detalleventa.`idProducto`
    WHERE detalleventa.`idVenta` = venta_id
    ORDER BY detalleventa.`lineNumber`;
END $$

CALL sp_detalle_venta_find_id(3);

--CREATE VENTA

DELIMITER $$
CREATE PROCEDURE sp_detalle_venta_create(
    IN dv_idVenta INT,
    IN dv_lineNumber INT,
    IN dv_idProducto INT,
    IN dv_cantidad INT,
    IN dv_precioUnitario DECIMAL(10,2)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
    END;

    START TRANSACTION;

    INSERT INTO detalleventa(idVenta, `lineNumber`, `idProducto`, cantidad, `precioUnitario`)
    VALUES(dv_idVenta, dv_lineNumber, dv_idProducto, dv_cantidad, dv_precioUnitario);

    COMMIT;
    SELECT 'Detalle creado correctamente' AS mensajito;
END$$

CALL sp_detalle_venta_create(7, 2, 2, 1, 69.99);

--UPDATE DETALLE VENTA
DELIMITER $$
CREATE PROCEDURE sp_detalle_venta_update(
    IN dv_idVenta INT,
    IN dv_lineNumber INT,
    IN dv_idProducto INT,
    IN dv_cantidad INT,
    IN dv_precioUnitario DECIMAL(10,2)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
    END;

    START TRANSACTION;

    UPDATE detalleventa
    SET detalleventa.`idProducto` = dv_idProducto,
        detalleventa.cantidad = dv_cantidad,
        detalleventa.precioUnitario = dv_precioUnitario
    WHERE detalleventa.`idVenta` = dv_idVenta AND detalleventa.`lineNumber` = dv_lineNumber;

    COMMIT;

    SELECT 'Detalle actualizado correctamente' AS mensajito;
END$$
CALL sp_detalle_venta_update(7, 2, 2, 1, 269.99);

--DELETE DETALLE VENTA
DELIMITER $$
CREATE PROCEDURE sp_detalle_venta_delete(
    IN dv_idVenta INT,
    IN dv_lineNumber INT
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
    END;

    START TRANSACTION;

    DELETE FROM detalleventa
    WHERE detalleventa.`idVenta` = dv_idVenta AND detalleventa.`lineNumber`=dv_lineNumber;

    COMMIT;

    SELECT 'Detalle eliminado correctamente' AS mensajito;

END $$

CALL sp_detalle_venta_delete(7,1);


--TRIGGER ACTUALIZAR TOTAL DE LA VENTA AL INSERTAR EL DETALLE

DELIMITER $$
CREATE TRIGGER tr_detalle_venta_insert
    AFTER INSERT ON detalleventa
    FOR EACH ROW
BEGIN
    UPDATE venta
        SET total = (
        SELECT COALESCE(SUM(subtotal), 0) 
        FROM detalleventa
        WHERE detalleventa.idVenta = NEW.idVenta
    )
    WHERE venta.id = NEW.idVenta;
END$$

--TRIGGER ACTUALIZAR TOTAL DE LA VENTA AL ACTUALIZAR DETALLE

DELIMITER $$
CREATE TRIGGER tr_detalle_venta_update
    AFTER UPDATE ON detalleventa
    FOR EACH ROW
BEGIN
    UPDATE venta
    SET total = (
        SELECT COALESCE(SUM(subtotal), 0)
        FROM detalleventa
        WHERE detalleventa.`idVenta` = NEW.idVenta
    )
    WHERE venta.id = NEW.idVenta;
END$$

--TRIGGER PARA ACTUALIZAR EL TOTAL DE LA VENTA AL ELIMINAR EL DETALLE
DELIMITER $$
CREATE TRIGGER tr_detalle_venta_delete
    AFTER DELETE ON DetalleVenta
    FOR EACH ROW
BEGIN
    UPDATE Venta 
    SET total = (
        SELECT COALESCE(SUM(subtotal), 0) 
        FROM DetalleVenta 
        WHERE idVenta = OLD.idVenta
    )
    WHERE id = OLD.idVenta;
END$$
