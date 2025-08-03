--FIND ALL PERSONA NATURAL
DELIMITER $$
CREATE PROCEDURE sp_persona_natural_list()
    BEGIN
        SELECT 
            cliente.id,
            cliente.direccion,
            cliente.email,
            cliente.telefono,
            personanatural.nombres,
            personanatural.apellidos,
            personanatural.cedula
        FROM cliente
            JOIN personanatural 
            ON cliente.id = personanatural.cliente_id
        ORDER BY cliente.id ASC;
    END$$

CALL sp_persona_natural_list();

--FIND ID PERSONA NATURAL
DELIMITER $$
DROP PROCEDURE `sp_persona_NATURAL_find_id`;
CREATE PROCEDURE sp_persona_natural_find_id(IN cliente_id INT)
    BEGIN
        SELECT 
            cliente.id,
            cliente.direccion,
            cliente.email,
            cliente.telefono,
            personanatural.nombres,
            personanatural.apellidos,
            personanatural.cedula
        FROM cliente
            JOIN personanatural 
            ON cliente.id = personanatural.cliente_id
        WHERE cliente.id = cliente_id
        ORDER BY cliente.id  ASC;
    END$$

CALL sp_persona_natural_find_id(3);

--CREATE PERSONA NATURAL
DELIMITER $$

CREATE PROCEDURE sp_create_persona_natural(
    IN c_email VARCHAR(255),
    IN c_telefono VARCHAR(15),
    IN c_direccion VARCHAR(255),
    IN pn_nombres VARCHAR(255),
    IN pn_apellidos VARCHAR(255),
    IN pn_cedula VARCHAR(10)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
        BEGIN
        ROLLBACK;
    END;
    
    START TRANSACTION;
    INSERT INTO cliente(email, telefono, direccion, tipoCliente)
    VALUES (c_email, c_telefono, c_direccion, 'PersonaNatural');

    SET @new_cliente_id := LAST_INSERT_ID();

    INSERT INTO personanatural (cliente_id, nombres, apellidos, cedula)
    VALUES(@new_cliente_id, pn_nombres, pn_apellidos, pn_cedula);

    COMMIT;

    SELECT @new_cliente_id AS cliente_id;

END $$
CALL sp_create_persona_natural('cesargoat@gmail.com','0997798129','Florida de Alvear E17-24 Joaquín Gallegos', 'César Andrés', 'González Tobar', '1753391364');


--DELETE PERSONA NATURAL

DELIMITER $$

CREATE PROCEDURE sp_delete_persona_natural(IN cliente_id INT)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
    END;

    START TRANSACTION;
        DELETE FROM personanatural WHERE personanatural.cliente_id = cliente_id;
        DELETE FROM cliente WHERE cliente.id = cliente_id;
    COMMIT;
    SELECT 1 AS OK;

END$$

CALL sp_delete_persona_natural(7);

--UPDATE PERSONA NATURAL

DELIMITER $$

CREATE PROCEDURE sp_update_persona_natural(
    IN cliente_id INT,
    IN c_email VARCHAR(255),
    IN c_telefono VARCHAR(15),
    IN c_direccion VARCHAR(255),
    IN pn_nombres VARCHAR(255),
    IN pn_apellidos VARCHAR(255),
    IN pn_cedula VARCHAR(10)
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
    WHERE cliente.id = cliente_id AND cliente.`tipoCliente`='PersonaNatural';

    UPDATE personanatural
    SET nombres = pn_nombres,
        apellidos = pn_apellidos,
        cedula = pn_cedula
    WHERE personanatural.cliente_id = cliente_id;

    COMMIT;

    SELECT cliente_id AS id_updated_client;

END$$

CALL sp_update_persona_natural(8,'cesargoat123@outlook.com','0997798134','Florinda de Alvear E17-24 Joaquín Gallegos', 'César Andrés', 'González Tobar', '1708018658');

