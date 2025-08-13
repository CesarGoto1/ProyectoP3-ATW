<?php
declare(strict_types=1);
namespace App\Repositories;

use App\Config\Database;
use App\Entities\RolPermiso;
use App\Entities\Rol;
use App\Entities\Permiso;
use App\Interfaces\RepositoryInterface;
use PDO;

class RolPermisoRepository implements RepositoryInterface
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function create(object $entity): bool
    {
        if (!$entity instanceof RolPermiso) {
            throw new \InvalidArgumentException('RolPermiso expected');
        }
        $stmt = $this->db->prepare("CALL sp_rol_permiso_create(:idRol, :idPermiso)");
        $ok = $stmt->execute([
            ':idRol' => $entity->getRol()->getId(),
            ':idPermiso' => $entity->getPermiso()->getId()
        ]);
        if ($ok) {
            $stmt->fetch();
        }
        $stmt->closeCursor();
        return $ok;
    }

    public function findById(int $id): ?object
    {
        // No hay un id único, así que este método no aplica directamente.
        // Puedes lanzar una excepción o retornar null.
        throw new \BadMethodCallException('Use findByCompositeKey($idRol, $idPermiso) en su lugar.');
    }

    public function findByCompositeKey(int $idRol, int $idPermiso): ?RolPermiso
    {
        $stmt = $this->db->prepare("CALL sp_rol_permiso_find_id(:idRol, :idPermiso)");
        $stmt->execute([
            ':idRol' => $idRol,
            ':idPermiso' => $idPermiso
        ]);
        $row = $stmt->fetch();
        $stmt->closeCursor();
        if ($row) {
            return $this->hydrate($row);
        }
        return null;
    }

    public function findAll(): array
    {
        $stmt = $this->db->query("CALL sp_rol_permiso_list()");
        $rows = $stmt->fetchAll();
        $stmt->closeCursor();
        $list = [];
        foreach ($rows as $row) {
            $list[] = $this->hydrate($row);
        }
        return $list;
    }

    public function update(object $entity): bool
    {
        throw new \BadMethodCallException('No se puede actualizar una relación RolPermiso.');
    }

    public function delete(int $id): bool
    {
        throw new \BadMethodCallException('Use deleteByCompositeKey($idRol, $idPermiso) en su lugar.');
    }

    public function deleteByCompositeKey(int $idRol, int $idPermiso): bool
    {
        $stmt = $this->db->prepare("CALL sp_rol_permiso_delete(:idRol, :idPermiso)");
        $ok = $stmt->execute([
            ':idRol' => $idRol,
            ':idPermiso' => $idPermiso
        ]);
        if ($ok) {
            $stmt->fetch();
        }
        $stmt->closeCursor();
        return $ok;
    }

    private function hydrate(array $row): RolPermiso
    {
        $rol = new Rol((int)$row['idRol'], $row['rolNombre']);
        $permiso = new Permiso((int)$row['idPermiso'], $row['permisoCodigo']);
        return new RolPermiso($rol, $permiso);
    }
}