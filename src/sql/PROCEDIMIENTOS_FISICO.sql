DELIMITER $$
CREATE PROCEDURE sp_list_pFisico()
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
        pf.peso,
        pf.alto,
        pf.ancho,
        pf.profundidad
    FROM productofisico pf
        INNER JOIN producto p ON pf.producto_id = p.id
        INNER JOIN categoria c ON p.idCategoria = c.id;
END$$
DELIMITER ;
CALL sp_list_pFisico();

DELIMITER $$
CREATE PROCEDURE sp_find_pFisico(IN p_id INT)
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
        pf.peso,
        pf.alto,
        pf.ancho,
        pf.profundidad
    FROM productofisico pf
        INNER JOIN producto p ON pf.producto_id = p.id
        INNER JOIN categoria c ON p.idCategoria = c.id
    WHERE p.id = p_id;
END$$
DELIMITER ;
CALL sp_find_pFisico(1);

DELIMITER $$
CREATE PROCEDURE sp_create_pFisico(
    IN p_nombre VARCHAR(255),
    IN p_descripcion TEXT,
    IN p_precioUnitario DECIMAL(10, 2),
    IN p_stock INT,
    IN p_idCategoria INT,
    IN p_peso DECIMAL(10, 2),
    IN p_alto DECIMAL(10, 2),
    IN p_ancho DECIMAL(10, 2),
    IN p_profundidad DECIMAL(10, 2)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
    END;
    START TRANSACTION;

    INSERT INTO producto (nombre, descripcion, precioUnitario, stock, idCategoria, TYPE)
    VALUES (p_nombre, p_descripcion, p_precioUnitario, p_stock, p_idCategoria, 'ProductoFisico');

    SET @last_id = LAST_INSERT_ID();

    INSERT INTO productofisico (producto_id, peso, alto, ancho, profundidad)
    VALUES (@last_id, p_peso, p_alto, p_ancho, p_profundidad);

    COMMIT;
END$$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE sp_update_pFisico(
    IN p_id INT,
    IN p_nombre VARCHAR(255),
    IN p_descripcion TEXT,
    IN p_precioUnitario DECIMAL(10, 2),
    IN p_stock INT,
    IN p_idCategoria INT,
    IN p_peso DECIMAL(10, 2),
    IN p_alto DECIMAL(10, 2),
    IN p_ancho DECIMAL(10, 2),
    IN p_profundidad DECIMAL(10, 2)
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

    UPDATE productofisico 
    SET peso = p_peso, alto = p_alto, ancho = p_ancho, profundidad = p_profundidad
    WHERE producto_id = p_id;

    COMMIT;
    SELECT 'Producto físico actualizado correctamente' AS mensaje;
END$$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE sp_delete_pFisico(IN p_id INT)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
    END;
    START TRANSACTION;

    DELETE FROM productofisico WHERE producto_id = p_id;
    DELETE FROM producto WHERE id = p_id;

    COMMIT;
    SELECT 'Producto físico eliminado correctamente' AS mensaje;
END$$
DELIMITER ;
CALL sp_delete_pFisico(7);

