<?php
    declare(strict_types=1);
    namespace App\Repositories;
    
    use App\Config\Database;
    use App\Entities\PersonaJuridica;
    use App\Entities\Cliente;
    use App\Interfaces\RepositoryInterface;
    use PDO;

    class PersonaJuridicaRepo implements RepositoryInterface
    {
        private PDO $db;
        public function __construct(){
            $this->db=Database::getConnection();
        }

        //CREATE PERSONA JURIDICA
        public function create(object $entity):bool{
            if(!$entity instanceof PersonaJuridica){
                throw new \InvalidArgumentException('Persona Juridica Expected');
            }

            $stmt = $this->db->prepare("CALL sp_create_persona_juridica(
                :email,
                :telefono,
                :direccion,
                :razonSocial,
                :ruc,
                :representanteLegal
                )"
            );
            $ok = $stmt->execute([
                ':email'                =>$entity->getEmail(),
                ':telefono'             =>$entity->getTelefono(),
                ':direccion'            =>$entity->getDireccion(),
                ':razonSocial'          =>$entity->getRazonSocial(),
                ':ruc'                  =>$entity->getRuc(),
                ':representanteLegal'   =>$entity->getRepresentanteLegal()
            ]);

            if($ok){
                $stmt->fetch();
            }
            $stmt->closeCursor();
            return $ok;
        }

        //FIND ID PERSONA JURIDICA
        public function findById(int $id): ?object{
            $stmt = $this->db->prepare("CALL sp_persona_juridica_find_id(:id);");
            $stmt->execute([':id'=> $id]);
            $row = $stmt->fetch();
            $stmt->closeCursor();
            return $row ? $this->hydrate($row) : null;
        }

        //UPDATE PERSONA JURIDICA
        public function update(object $entity):bool{
            if(!$entity instanceof PersonaJuridica){
                throw new \InvalidArgumentException('Persona Juridica Expected');
            }

            $stmt = $this->db->prepare("CALL sp_update_persona_juridica(
                :id,
                :email,
                :telefono,
                :direccion,
                :razonSocial,
                :ruc,
                :representanteLegal
                )"
            );
            $ok = $stmt->execute([
                ':id'                   =>$entity->getId(),
                ':email'                =>$entity->getEmail(),
                ':telefono'             =>$entity->getTelefono(),
                ':direccion'            =>$entity->getDireccion(),
                ':razonSocial'          =>$entity->getRazonSocial(),
                ':ruc'                  =>$entity->getRuc(),
                ':representanteLegal'   =>$entity->getRepresentanteLegal()
            ]);

            if($ok){
                $stmt->fetch();
            }
            $stmt->closeCursor();
            return $ok;
        }

        //DELETE PERSONA JURIDICA
        public function delete(int $id):bool{
            $stmt = $this->db->prepare("CALL sp_delete_persona_juridica(:id);");

            $ok = $stmt->execute([':id'=>$id]);
            if($ok){
                $stmt->fetch();
            }
            $stmt->closeCursor();
            return $ok;
        }

        //FIND ALL PERSONA JURIDICA
        public function findAll():array{
            $stmt = $this->db->query("CALL sp_persona_juridica_list();");
            $rows = $stmt->fetchAll();
            $stmt-> closeCursor();

            $out = [];

            foreach($rows as $row){
                $out[]= $this->hydrate($row);
            }
            return $out;
        }

        private function hydrate(array $row): PersonaJuridica{
            return new PersonaJuridica(
                (int)$row['id'],
                $row['direccion'],
                $row['email'],
                $row['telefono'],
                $row['razonSocial'],
                $row['ruc'],
                $row['representanteLegal']
            );
        }
    }