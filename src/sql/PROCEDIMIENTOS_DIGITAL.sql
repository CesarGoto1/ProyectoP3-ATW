DELIMITER $$
CREATE PROCEDURE sp_list_pDigital()
BEGIN
    SELECT 
        p.id,
        p.nombre,
        p.descripcion,
        p.precioUnitario,
        p.stock,
        c.id AS idCategoria,
        c.nombre AS categoriaNombre,
        c.descripcion AS categoriaDescripcion,
        c.estado AS categoriaEstado,
        c.idPadre AS categoriaIdPadre,
        pd.urlDescarga,
        pd.licencia

    FROM productodigital pd
        INNER JOIN producto p ON pd.producto_id = p.id
        INNER JOIN categoria c ON p.idCategoria = c.id;
END$$
DELIMITER ;
CALL sp_list_pDigital();

DELIMITER $$
CREATE PROCEDURE sp_find_pDigital(IN p_id INT)
BEGIN
    SELECT 
        p.id,
        p.nombre,
        p.descripcion,
        p.precioUnitario,
        p.stock,
        c.id AS idCategoria,
        c.nombre AS categoriaNombre,
        c.descripcion AS categoriaDescripcion,
        c.estado AS categoriaEstado,
        c.idPadre AS categoriaIdPadre,
        pd.urlDescarga,
        pd.licencia
    FROM productodigital pd
        INNER JOIN producto p ON pd.producto_id = p.id
        INNER JOIN categoria c ON p.idCategoria = c.id
    WHERE p.id = p_id;
END$$
DELIMITER ;
CALL sp_find_pDigital(9);

DELIMITER $$
CREATE PROCEDURE sp_create_pDigital(
    IN p_nombre VARCHAR(255),
    IN p_descripcion TEXT,
    IN p_precioUnitario DECIMAL(10, 2),
    IN p_stock INT,
    IN p_idCategoria INT,
    IN p_urlDescarga VARCHAR(255),
    IN p_licencia VARCHAR(255)
)
BEGIN
    INSERT INTO producto (nombre, descripcion, precioUnitario, stock, idCategoria, TYPE)
    VALUES (p_nombre, p_descripcion, p_precioUnitario, p_stock, p_idCategoria, 'ProductoDigital');

    SET @last_id = LAST_INSERT_ID();

    INSERT INTO productodigital (producto_id, urlDescarga, licencia)
    VALUES (@last_id, p_urlDescarga, p_licencia);
    COMMIT;
END$$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE sp_update_pDigital(
    IN p_id INT,
    IN p_nombre VARCHAR(255),
    IN p_descripcion TEXT,
    IN p_precioUnitario DECIMAL(10, 2),
    IN p_stock INT,
    IN p_idCategoria INT,
    IN p_urlDescarga VARCHAR(255),
    IN p_licencia VARCHAR(255)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
    END;
    START TRANSACTION;

    UPDATE producto 
    SET nombre = p_nombre, descripcion = p_descripcion, precioUnitario = p_precioUnitario, stock = p_stock, idCategoria = p_idCategoria
    WHERE id = p_id;

    UPDATE productodigital 
    SET urlDescarga = p_urlDescarga, licencia = p_licencia
    WHERE producto_id = p_id;

    COMMIT;
    SELECT 'Producto digital actualizado correctamente' AS mensaje;
END$$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE sp_delete_pDigital(IN p_id INT)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
    END;
    START TRANSACTION;

    DELETE FROM productodigital WHERE producto_id = p_id;
    DELETE FROM producto WHERE id = p_id;

    COMMIT;
END$$
DELIMITER ;

CALL sp_delete_pDigital(13);