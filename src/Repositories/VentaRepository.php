<?php
    declare(strict_types=1);
    namespace App\Repositories;

    use App\Config\Database;
    use App\Entities\Venta;
    use App\Entities\PersonaNatural;
    use App\Entities\PersonaJuridica;
    use App\Interfaces\RepositoryInterface;
    use PDO;

    class VentaRepository implements RepositoryInterface{
        private PDO $db;
        public function __construct(){
            $this->db=Database::getConnection();
        }

        public function create(object $entity):bool{
            if (!$entity instanceof Venta) {
                throw new \InvalidArgumentException('Venta expected');
            }
            $stmt = $this->db->prepare("CALL sp_venta_create(
            :fecha,
            :idCliente,
            :total,
            :estado
            )");

            $ok = $stmt->execute([
                ':fecha'            => $entity->getFecha()->format('Y-m-d'),
                ':idCliente'         => $entity->getCliente()->getId(),
                ':total'            => $entity->getTotal(),
                ':estado'           => $entity->getEstado()
            ]);
            
            if($ok){
                $stmt->fetch();
            }
            $stmt->closeCursor();
            return $ok;
        }

        public function findById(int $id): ?object{
            $stmt = $this->db->prepare("CALL sp_venta_find_id(:id);");
            $stmt->execute([':id'=>$id]);
            $row = $stmt->fetch();
            $stmt->closeCursor();
            return $row ? $this->hydrate($row):null;
        }

        public function update(object $entity):bool{
            if (!$entity instanceof Venta) {
                throw new \InvalidArgumentException('Venta expected');
            }

            $stmt = $this->db->prepare("CALL sp_update_venta(
            :id,
            :fecha,
            :idCliente,
            :total,
            :estado
            );");

            $ok = $stmt->execute([
                ':id'               => $entity->getId(),
                ':fecha'            => $entity->getFecha()->format('Y-m-d'),
                ':idCliente'         => $entity->getCliente()->getId(),
                ':total'            => $entity->getTotal(),
                ':estado'           => $entity->getEstado()
            ]);
            
            if($ok){
                $stmt->fetch();
            }
            $stmt->closeCursor();
            return $ok;
        }

        public function delete(int $id):bool{
            $stmt = $this->db->prepare("CALL sp_delete_venta(:id);");
            $ok = $stmt->execute([':id' => $id]);
            if($ok){
                $stmt -> fetch();
            }
            $stmt->closeCursor();
            return $ok;
        }

        public function findAll():array{
            $stmt = $this->db->query("CALL sp_venta_find_all();");
            $rows = $stmt->fetchAll();
            $stmt -> closeCursor();
            $out = [];
            foreach($rows as $row){
                $out[] = $this->hydrate($row);
            }
            return $out;
        }


        public function hydrate(array $row): Venta
        {
            $cliente = null;
            if($row['tipoCliente']==='PersonaNatural'){
                $cliente = new PersonaNatural(
                    (int)$row['idCliente'],
                    $row['direccion'],
                    $row['email'],
                    $row['telefono'],
                    $row['nombres'],
                    $row['apellidos'],
                    $row['cedula']
                );
            }elseif($row['tipoCliente']==='PersonaJuridica'){
                $cliente = new PersonaJuridica(
                    (int)$row['idCliente'],
                    $row['direccion'],
                    $row['email'],
                    $row['telefono'],
                    $row['razonSocial'],
                    $row['ruc'],
                    $row['representanteLegal']
                );
            }
            if (!$cliente) {
                throw new \RuntimeException('No se pudo crear el cliente para la venta');
            }
            return new Venta(
                (int)$row['id'],
                new \DateTime($row['fecha']),
                $cliente,
                (float)$row['total'],
                $row['estado']
            );
        }

    }