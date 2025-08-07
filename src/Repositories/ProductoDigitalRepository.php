<?php
declare(strict_types=1);
namespace App\Repositories;

use App\Config\Database;
use App\Entities\Categoria;
use App\Entities\ProductoDigital;
use App\Interfaces\RepositoryInterface;
use PDO;

class ProductoDigitalRepository implements RepositoryInterface
{
    private PDO $db;
    private CategoriaRepository $categoriaRepository;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->categoriaRepository = new CategoriaRepository();
    }

    public function findAll(): array
    {
        $stmt = $this->db->query("CALL sp_list_pDigital()");
        $rows = $stmt->fetchAll();
        $stmt->closeCursor();
        $list = [];
        foreach ($rows as $row) {
            $list[] = $this->hydrate($row);
        }
        return $list;
    }

    public function hydrate(array $row): ProductoDigital
    {
        return new ProductoDigital(
            (int)$row['id'],
            $row['nombre'],
            $row['descripcion'],
            (float)$row['precioUnitario'],
            (int)$row['stock'],
            new Categoria(
                (int)$row['idCategoria'],
                $row['categoriaNombre'],
                $row['categoriaDescripcion'],
                $row['categoriaEstado'],
                $row['categoriaIdPadre'] ? (int)$row['categoriaIdPadre'] : null
            ),
            $row['urlDescarga'],
            $row['licencia']
        );
    }

    public function create(object $entity): bool{
        if (!$entity instanceof ProductoDigital) {
            throw new \InvalidArgumentException('ProductoDigital expected');
        }
        $stmt = $this->db->prepare("CALL sp_create_pDigital(
        :nombre, 
        :descripcion, 
        :precioUnitario, 
        :stock, 
        :idCategoria, 
        :urlDescarga, 
        :licencia)");

        $ok = $stmt->execute([
            ':nombre'           => $entity->getNombre(),
            ':descripcion'      => $entity->getDescripcion(),
            ':precioUnitario'   => $entity->getPrecioUnitario(),
            ':stock'            => $entity->getStock(),
            ':idCategoria'      => $entity->getCategoria()->getId(),
            ':urlDescarga'      => $entity->getUrlDescarga(),
            ':licencia'         => $entity->getLicencia()
        ]);
        if ($ok){
            $stmt->fetch();
        }
        $stmt->closeCursor();
        return $ok;
    }

    public function findById(int $id): ?object{
        $stmt = $this->db->prepare("CALL sp_find_pDigital(:id)");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    public function update(object $entity): bool{
        if (!$entity instanceof ProductoDigital) {
            throw new \InvalidArgumentException('Producto Digital expected');
        }
        $stmt = $this->db->prepare("CALL sp_update_pDigital(
        :id, 
        :nombre, 
        :descripcion, 
        :precioUnitario, 
        :stock, 
        :idCategoria, 
        :urlDescarga,
        :licencia)");
        $ok =  $stmt->execute([
            ':id'               => $entity->getId(),
            ':nombre'           => $entity->getNombre(),
            ':descripcion'      => $entity->getDescripcion(),
            ':precioUnitario'   => $entity->getPrecioUnitario(),
            ':stock'            => $entity->getStock(),
            ':idCategoria'      => $entity->getCategoria()->getId(),
            ':urlDescarga'      => $entity->getUrlDescarga(),
            ':licencia'         => $entity->getLicencia()
        ]);
        if ($ok){
            $stmt->fetch();
        }
        $stmt->closeCursor();
        return $ok;

    }
    public function delete(int $id): bool{
        $stmt = $this->db->prepare("CALL sp_delete_pDigital(:id)");
        $ok = $stmt->execute(['id' => $id]);
        $stmt->closeCursor();
        return $ok;
    }

    
}