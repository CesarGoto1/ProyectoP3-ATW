<?php
    declare(strict_types = 1);
    namespace App\Repositories;

    use App\Config\Database;
    use App\Entities\DetalleVenta;
    use App\Interfaces\RepositoryInterface;
    use PDO;

    class DetalleVentaRepository implements RepositoryInterface
    {
        private PDO $db;

        public function __construct(){
            $this->db=Database::getConnection();
        }

        //CREATE DETALLE VENTA
        public function create(object $entity):bool{
            if(!$entity instanceof DetalleVenta){
            throw new \InvalidArgumentException('Detalle Venta expected');
            }

            $stmt = $this->db->prepare("CALL sp_detalle_venta_create(
            :idVenta,
            :lineNumber,
            :idProducto,
            :cantidad,
            :precioUnitario)"
            );
            $ok = $stmt->execute([
                ':idVenta'          =>$entity->getIdVenta(),
                ':lineNumber'       =>$entity->getLineNumber(),
                ':idProducto'       =>$entity->getIdProducto(),
                ':cantidad'         =>$entity->getCantidad(),
                ':precioUnitario'   =>$entity->getPrecioUnitario()
            ]);


            if($ok){
                $stmt->fetch();
            }
            $stmt->closeCursor();
            return $ok;
        }

        public function findById(int $id): ?object{
            $detalles = $this->getDetallesByVentaId($id);
            return $detalles ? $detalles[0] : null;
        }

        public function findByVentaId(int $ventaId): array
        {
            return $this->getDetallesByVentaId($ventaId);
        }

        
        private function getDetallesByVentaId(int $ventaId): array
        {
            $stmt = $this->db->prepare("CALL sp_detalle_venta_find_id(:id)");
            $stmt->execute([':id' => $ventaId]);
            $rows = $stmt->fetchAll();
            $stmt->closeCursor();
            
            $list = [];
            foreach ($rows as $row) {
                $list[] = $this->hydrate($row);
            }
            return $list;
        }

        public function update(object $entity):bool{
            if(!$entity instanceof DetalleVenta){
            throw new \InvalidArgumentException('Detalle Venta expected');
            }

            $stmt = $this->db->prepare("CALL sp_detalle_venta_update(
            :idVenta,
            :lineNumber,
            :idProducto,
            :cantidad,
            :precioUnitario)"
            );
            $ok = $stmt->execute([
                ':idVenta'          =>$entity->getIdVenta(),
                ':lineNumber'       =>$entity->getLineNumber(),
                ':idProducto'       =>$entity->getIdProducto(),
                ':cantidad'         =>$entity->getCantidad(),
                ':precioUnitario'   =>$entity->getPrecioUnitario()
            ]);

            if($ok){
                $stmt->fetch();
            }
            $stmt->closeCursor();
            return $ok;
        }
        public function findMaxLineNumberByVenta(int $idVenta): int
        {
            $stmt = $this->db->prepare("SELECT MAX(lineNumber) as maxLine FROM detalleventa WHERE idVenta = :idVenta");
            $stmt->execute([':idVenta' => $idVenta]);
            $row = $stmt->fetch();
            $stmt->closeCursor();
            return $row && $row['maxLine'] !== null ? (int)$row['maxLine'] : 0;
        }
        public function delete(int $id):bool{
            throw new \BadMethodCallException(
                'Use deleteByCompositeKey(idVenta, lineNumber) instead'
            );
        }

        public function deleteByCompositeKey(int $idVenta, int $lineNumber): bool
        {
            $stmt = $this->db->prepare("CALL sp_detalle_venta_delete(:idVenta, :lineNumber)");
            $ok = $stmt->execute([
                ':idVenta' => $idVenta,
                ':lineNumber' => $lineNumber
            ]);

            if ($ok) {
                $stmt->fetch();
            }
            $stmt->closeCursor();
            return $ok;
        }
        public function findAll():array{
            $stmt = $this->db->query("CALL sp_detalle_venta_list();");
            $rows = $stmt->fetchAll();
            $stmt -> closeCursor();
            $out = [];
            foreach($rows as $row){
                $out[] = $this->hydrate($row);
            }
            return $out;
        }
        private function hydrate(array $row): DetalleVenta
        {
            return new DetalleVenta(
                (int)$row['idVenta'],
                (int)$row['lineNumber'],
                (int)$row['idProducto'],
                (int)$row['cantidad'],
                (float)$row['precioUnitario']
            );
        }
    }
