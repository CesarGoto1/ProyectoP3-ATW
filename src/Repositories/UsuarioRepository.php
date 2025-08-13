<?php
declare(strict_types=1);
namespace App\Repositories;

use App\Config\Database;
use App\Entities\Usuario;
use App\Interfaces\RepositoryInterface;
use PDO;

class UsuarioRepository implements RepositoryInterface
{
    private PDO $db;
    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function create(object $entity): bool
    {
        if (!$entity instanceof Usuario) {
            throw new \InvalidArgumentException('Usuario expected');
        }
        $stmt = $this->db->prepare("CALL sp_usuario_create(:username, :passwordHash, :estado)");
        return $stmt->execute([
            ':username' => $entity->getUsername(),
            ':passwordHash' => $entity->getPasswordHash(),
            ':estado' => $entity->getEstado()
        ]);
    }

    public function findById(int $id): ?object
    {
        $stmt = $this->db->prepare("CALL sp_usuario_find_id(:id)");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        // Consumir todos los resultados para liberar el cursor
        while ($stmt->nextRowset()) {}
        return $row ? $this->hydrate($row) : null;
    }

    public function findAll(): array
    {
        $stmt = $this->db->query("CALL sp_usuario_list()");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Consumir todos los resultados para liberar el cursor
        while ($stmt->nextRowset()) {}
        return array_map([$this, 'hydrate'], $rows);
    }

    public function update(object $entity): bool
    {
        if (!$entity instanceof Usuario) {
            throw new \InvalidArgumentException('Usuario expected');
        }
        $stmt = $this->db->prepare("CALL sp_usuario_update(:id, :username, :passwordHash, :estado)");
        return $stmt->execute([
            ':id' => $entity->getId(),
            ':username' => $entity->getUsername(),
            ':passwordHash' => $entity->getPasswordHash(),
            ':estado' => $entity->getEstado()
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("CALL sp_usuario_delete(:id)");
        return $stmt->execute([':id' => $id]);
    }

    private function hydrate(array $row): Usuario
    {
        return new Usuario(
            isset($row['id']) ? (int)$row['id'] : null,
            $row['username'] ?? '',
            $row['passwordHash'] ?? '',
            $row['estado'] ?? ''
        );
    }
}