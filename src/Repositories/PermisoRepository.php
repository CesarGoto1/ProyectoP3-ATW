<?php
declare(strict_types=1);
namespace App\Repositories;

use App\Config\Database;
use App\Entities\Permiso;
use App\Interfaces\RepositoryInterface;
use PDO;

class PermisoRepository implements RepositoryInterface
{
    private PDO $db;
    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function create(object $entity): bool
    {
        if (!$entity instanceof Permiso) {
            throw new \InvalidArgumentException('Permiso expected');
        }
        $stmt = $this->db->prepare("CALL sp_permiso_create(:codigo)");
        return $stmt->execute([
            ':codigo' => $entity->getCodigo()
        ]);
    }

    public function findById(int $id): ?object
    {
        $stmt = $this->db->prepare("CALL sp_permiso_find_id(:id)");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        while ($stmt->nextRowset()) {}
        return $row ? $this->hydrate($row) : null;
    }

    public function findAll(): array
    {
        $stmt = $this->db->query("CALL sp_permiso_list()");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        while ($stmt->nextRowset()) {}
        return array_map([$this, 'hydrate'], $rows);
    }

    public function update(object $entity): bool
    {
        if (!$entity instanceof Permiso) {
            throw new \InvalidArgumentException('Permiso expected');
        }
        $stmt = $this->db->prepare("CALL sp_permiso_update(:id, :codigo)");
        return $stmt->execute([
            ':id' => $entity->getId(),
            ':codigo' => $entity->getCodigo()
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("CALL sp_permiso_delete(:id)");
        return $stmt->execute([':id' => $id]);
    }

    private function hydrate(array $row): Permiso
    {
        return new Permiso(
            isset($row['id']) ? (int)$row['id'] : null,
            $row['codigo'] ?? ''
        );
    }
}