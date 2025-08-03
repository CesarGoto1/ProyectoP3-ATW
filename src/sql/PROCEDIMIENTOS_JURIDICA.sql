--PROCEDIMIENTO FIND ALL PERSONA JURIDICA
DELIMITER $$
CREATE PROCEDURE sp_persona_juridica_list()
    BEGIN
        SELECT 
            cliente.id,
            cliente.direccion,
            cliente.email,
            cliente.telefono,
            personajuridica.`razonSocial`,
            personajuridica.ruc,
            personajuridica.`representanteLegal`
        FROM cliente
            JOIN personajuridica 
            ON cliente.id = personajuridica.cliente_id
        ORDER BY cliente.id ASC;
    END$$

CALL sp_persona_juridica_list();

--FIND ID PERSONA JURIDICA
DELIMITER $$

CREATE PROCEDURE sp_persona_juridica_find_id(IN cliente_id INT)
    BEGIN
        SELECT 
            cliente.id,
            cliente.direccion,
            cliente.email,
            cliente.telefono,
            personajuridica.`razonSocial`,
            personajuridica.ruc,
            personajuridica.`representanteLegal`
        FROM cliente
            JOIN personajuridica
            ON cliente.id = personajuridica.cliente_id
        WHERE cliente.id = cliente_id
        ORDER BY cliente.id  ASC;
    END$$

CALL sp_persona_juridica_find_id(4);

--PROCEDIMIENTO CREAR PERSONA JURIDICA
DELIMITER $$

CREATE PROCEDURE sp_create_persona_juridica(
    IN c_email VARCHAR(255),
    IN c_telefono VARCHAR(15),
    IN c_direccion VARCHAR(255),
    IN pj_razon_social VARCHAR(255),
    IN pj_ruc VARCHAR(13),
    IN pj_representante_legal VARCHAR(255)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
        BEGIN
        ROLLBACK;
    END;
    
    START TRANSACTION;
    INSERT INTO cliente(email, telefono, direccion, tipoCliente)
    VALUES (c_email, c_telefono, c_direccion, 'PersonaJuridica');

    SET @new_cliente_id := LAST_INSERT_ID();

    INSERT INTO personajuridica (cliente_id, `razonSocial`, ruc, `representanteLegal`)
    VALUES(@new_cliente_id, pj_razon_social, pj_ruc, pj_representante_legal);

    COMMIT;

    SELECT @new_cliente_id AS cliente_id;

END $$

CALL sp_create_persona_juridica('bde@email.com','0997798128','Av. Amazonas - Plataforma Gubernamental','Banco de Desarrollo del Ecuador', '1342123456151','Rolando Gonzalez');

--PROCEDIMIENTO ELIMINAR PERSONA JURIDICA
DELIMITER $$

CREATE PROCEDURE sp_delete_persona_juridica(IN cliente_id INT)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
    END;

    START TRANSACTION;
        DELETE FROM personajuridica WHERE personajuridica.cliente_id = cliente_id;
        DELETE FROM cliente WHERE cliente.id = cliente_id;
    COMMIT;
    SELECT 1 AS OK;

END$$

CALL sp_delete_persona_juridica(6);

--PROCEDIMIENTO ACTUALIZAR PERSONA JURIDICA

DELIMITER $$

CREATE PROCEDURE sp_update_persona_juridica(
    IN cliente_id INT,
    IN c_email VARCHAR(255),
    IN c_telefono VARCHAR(15),
    IN c_direccion VARCHAR(255),
    IN pj_razon_social VARCHAR(255),
    IN pj_ruc VARCHAR(13),
    IN pj_representante_legal VARCHAR(255)
)

BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN   
        ROLLBACK;
    END;

    START TRANSACTION;

    UPDATE cliente
    SET cliente.email = c_email,
        cliente.telefono = c_telefono,
        cliente.direccion = c_direccion
    WHERE cliente.id = cliente_id AND cliente.`tipoCliente`='PersonaJuridica';

    UPDATE personajuridica
    SET `razonSocial` = pj_razon_social,
        ruc = pj_ruc,
        `representanteLegal` = pj_representante_legal
    WHERE personajuridica.cliente_id = cliente_id;

    COMMIT;

    SELECT cliente_id AS id_updated_client;

END$$

CALL sp_update_persona_juridica(6, 'bancodesarrolloecu@email.com','0991937009', 'Plataforma Financiera - Av. Amazonaz y Japón', 'BDE','1112343456151', 'Victor Reascos');


