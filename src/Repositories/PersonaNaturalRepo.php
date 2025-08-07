<?php
    declare(strict_types=1);
    namespace App\Repositories;
    
    use App\Config\Database;
    use App\Entities\PersonaNatural;
    use App\Entities\Cliente;
    use App\Interfaces\RepositoryInterface;
    use PDO;

    class PersonaNaturalRepo implements RepositoryInterface
    {
        private PDO $db;
        public function __construct(){
            $this->db=Database::getConnection();
        }

        //CREATE PERSONA NATURAL
        public function create(object $entity):bool{
            if(!$entity instanceof PersonaNatural){
                throw new \InvalidArgumentException('Persona Natural Expected');
            }

            $stmt = $this->db->prepare("CALL sp_create_persona_natural(
                :email,
                :telefono,
                :direccion,
                :nombres,
                :apellidos,
                :cedula
                )"
            );
            $ok = $stmt->execute([
                ':email'                =>$entity->getEmail(),
                ':telefono'             =>$entity->getTelefono(),
                ':direccion'            =>$entity->getDireccion(),
                ':nombres'              =>$entity->getNombres(),
                ':apellidos'            =>$entity->getApellidos(),
                ':cedula'               =>$entity->getCedula()
            ]);

            if($ok){
                $stmt->fetch();
            }
            $stmt->closeCursor();
            return $ok;
        }

        //FIND ID PERSONA NATURAL
        public function findById(int $id): ?object{
            $stmt = $this->db->prepare("CALL sp_persona_natural_find_id(:id);");
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch();
            $stmt->closeCursor();
            return $row? $this->hydrate($row): null;
        }

        //UPDATE PERSONA NATURAL
        public function update(object $entity):bool{
            if(!$entity instanceof PersonaNatural){
                throw new \InvalidArgumentException('Persona Natural Expected');
            }

            $stmt = $this->db->prepare("CALL sp_update_persona_natural(
                :id,
                :email,
                :telefono,
                :direccion,
                :nombres,
                :apellidos,
                :cedula
                )"
            );
            $ok = $stmt->execute([
                ':id'                   =>$entity->getId(),
                ':email'                =>$entity->getEmail(),
                ':telefono'             =>$entity->getTelefono(),
                ':direccion'            =>$entity->getDireccion(),
                ':nombres'              =>$entity->getNombres(),
                ':apellidos'            =>$entity->getApellidos(),
                ':cedula'               =>$entity->getCedula()
            ]);

            if($ok){
                $stmt->fetch();
            }
            $stmt->closeCursor();
            return $ok;
        }

        //DELETE PERSONA NATURAL
        public function delete(int $id):bool{
            $stmt = $this->db->prepare("CALL sp_delete_persona_natural(:id);");
            $ok = $stmt->execute([':id' => $id]);
            if($ok){
                $stmt -> fetch();
            }
            $stmt->closeCursor();
            return $ok;
        }

        //FIND ALL PERSONA NATURAL
        public function findAll():array{
            $stmt = $this->db->query("CALL sp_persona_natural_list();");
            $rows = $stmt->fetchAll();
            $stmt-> closeCursor();

            $out = [];

            foreach($rows as $row){
                $out[]= $this->hydrate($row);
            }
            return $out;
        }

        private function hydrate(array $row): PersonaNatural{
            return new PersonaNatural(
                (int)$row['id'],
                $row['direccion'],
                $row['email'],
                $row['telefono'],
                $row['nombres'],
                $row['apellidos'],
                $row['cedula']
            );
        }
    }