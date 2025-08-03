<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Entities\Categoria;
use App\Interfaces\RepositoryInterface;
use App\Config\database;
use PDO;

class CategoriaRepository implements RepositoryInterface
{
    private PDO $db;

    public function __construct()
    {
        $this->db = database::getConnection();
    }
    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM Categoria");
        $list = [];
        while ($row=$stmt->fetch()){
            $list[] = $this->hydrate($row);
        }
        return $list;
    }

    public function hydrate(array $row): Categoria
    {
        $categoria = new Categoria(
            (int)$row['id'],
            $row['nombre'],
            $row['descripcion'],
            $row['estado'],
            $row['idPadre'] ? (int)$row['idPadre'] : null
        );
        return $categoria;
    }

    public function create(object $entity): bool
    {
        if (!$entity instanceof Categoria) {
            throw new \InvalidArgumentException('Categoria expected');
        }
        $sql = "INSERT INTO Categoria (nombre, descripcion, estado, idPadre) 
                VALUES (:nombre, :descripcion, :estado, :idPadre)";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'nombre' => $entity->getNombre(),
            'descripcion' => $entity->getDescripcion(),
            'estado' => $entity->getEstado(),
            'idPadre' => $entity->getIdPadre()
        ]);
    }

    public function findById(int $id): ?object
    {
        $sql = "SELECT * FROM Categoria WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    public function update(object $entity): bool
    {
        if (!$entity instanceof Categoria) {
            throw new \InvalidArgumentException('Categoria expected');
        }
        $sql = "UPDATE Categoria SET nombre = :nombre, descripcion = :descripcion, estado = :estado, idPadre = :idPadre WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'nombre' => $entity->getNombre(),
            'descripcion' => $entity->getDescripcion(),
            'estado' => $entity->getEstado(),
            'idPadre' => $entity->getIdPadre(),
            'id' => $entity->getId()
        ]);
    }
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM Categoria WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }



}