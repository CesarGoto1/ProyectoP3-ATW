<?php
declare(strict_types=1);
namespace App\Repositories;

use App\Config\Database;
use App\Entities\Rol;
use App\Interfaces\RepositoryInterface;
use PDO;

class RolRepository implements RepositoryInterface
{
    private PDO $db;
    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function create(object $entity): bool
    {
        if (!$entity instanceof Rol) {
            throw new \InvalidArgumentException('Rol expected');
        }
        $stmt = $this->db->prepare("CALL sp_rol_create(:nombre)");
        return $stmt->execute([
            ':nombre' => $entity->getNombre()
        ]);
    }

    public function findById(int $id): ?object
    {
        $stmt = $this->db->prepare("CALL sp_rol_find_id(:id)");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        while ($stmt->nextRowset()) {}
        return $row ? $this->hydrate($row) : null;
    }

    public function findAll(): array
    {
        $stmt = $this->db->query("CALL sp_rol_list()");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        while ($stmt->nextRowset()) {}
        return array_map([$this, 'hydrate'], $rows);
    }

    public function update(object $entity): bool
    {
        if (!$entity instanceof Rol) {
            throw new \InvalidArgumentException('Rol expected');
        }
        $stmt = $this->db->prepare("CALL sp_rol_update(:id, :nombre)");
        return $stmt->execute([
            ':id' => $entity->getId(),
            ':nombre' => $entity->getNombre()
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("CALL sp_rol_delete(:id)");
        return $stmt->execute([':id' => $id]);
    }

    private function hydrate(array $row): Rol
    {
        return new Rol(
            isset($row['id']) ? (int)$row['id'] : null,
            $row['nombre'] ?? ''
        );
    }
}