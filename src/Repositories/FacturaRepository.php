<?php
    declare(strict_types=1);
    namespace App\Repositories;

    use App\Config\Database;
    use App\Entities\Factura;
    use App\Entities\Venta;
    use App\Interfaces\RepositoryInterface;
    use App\Repositories\VentaRepository;
    use PDO;

    class FacturaRepository implements RepositoryInterface
    {
        private PDO $db;
        private VentaRepository $ventaRepository;

        public function __construct(){
            $this->db = Database::getConnection();
            $this->ventaRepository = new VentaRepository();
        }

        public function findAll(): array{
            $stmt = $this->db->query("CALL sp_factura_list();");
            $rows = $stmt->fetchAll();
            $stmt->closeCursor();

            $list = [];
            foreach ($rows as $row){
                $list[] = $this->hydrate($row);
            }
            return $list;
        }
        public function create(object $entity): bool {
            if (!$entity instanceof Factura) {
                throw new \InvalidArgumentException('Factura expected');
            }
            $stmt = $this->db->prepare("CALL sp_factura_create(:idVenta, :numero, :claveAcceso, :fechaEmision, :estado)");
            $ok = $stmt->execute([
                ':idVenta'      => $entity->getVenta()->getId(),
                ':numero'       => $entity->getNumero(),
                ':claveAcceso'  => $entity->getClaveAcceso(),
                ':fechaEmision' => $entity->getFechaEmision()->format('Y-m-d'),
                ':estado'       => $entity->getEstado()
            ]);
            if ($ok) {
                $stmt->fetch();
            }
            $stmt->closeCursor();
            return $ok;
        }

        public function findById(int $id): ?object {
            $stmt = $this->db->prepare("CALL sp_factura_find_id(:id)");
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch();
            $stmt->closeCursor();
            return $row ? $this->hydrate($row) : null;
        }

        public function update(object $entity): bool {
            if (!$entity instanceof Factura) {
                throw new \InvalidArgumentException('Factura expected');
            }
            $stmt = $this->db->prepare("CALL sp_factura_update(:id, :numero, :claveAcceso, :fechaEmision, :estado)");
            $ok = $stmt->execute([
                ':id'           => $entity->getId(),
                ':numero'       => $entity->getNumero(),
                ':claveAcceso'  => $entity->getClaveAcceso(),
                ':fechaEmision' => $entity->getFechaEmision()->format('Y-m-d'),
                ':estado'       => $entity->getEstado()
            ]);
            if ($ok) {
                $stmt->fetch();
            }
            $stmt->closeCursor();
            return $ok;
        }

        public function delete(int $id): bool {
            $stmt = $this->db->prepare("CALL sp_factura_delete(:id)");
            $ok = $stmt->execute([':id' => $id]);
            if ($ok) {
                $stmt->fetch();
            }
            $stmt->closeCursor();
            return $ok;
        }

        public function hydrate(array $row): Factura {
            $venta = $this->ventaRepository->findById((int)$row['idVenta']);
            return new Factura(
                (int)$row['id'],
                $venta,
                $row['numero'],
                $row['claveAcceso'],
                new \DateTime($row['fechaEmision']),
                $row['estado']
            );
        }
        
    }