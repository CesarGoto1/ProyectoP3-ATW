<?php
declare (strict_types=1);
namespace App\Controllers;

use App\Entities\ProductoFisico;
use App\Repositories\ProductoFisicoRepository;
use App\Repositories\CategoriaRepository;
use App\Entities\Categoria;

class ProductoFisicoController
{
    private ProductoFisicoRepository $productoFisicorepository;
    private CategoriaRepository $categoriaRepository;

    public function __construct()
    {
        $this->productoFisicoRepository = new ProductoFisicoRepository();
        $this->categoriaRepository = new CategoriaRepository();
    }

    public function handle(): void{
        header('Content-Type: application/json');
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET'){
            if(isset($_GET['id'])){
                $pf = $this->productoFisicoRepository->findById((int)$_GET['id']);
                echo json_encode($pf ? $this->productoFisicoToArray($pf) : null);
            }else{
                $list = array_map([$this,'productoFisicoToArray'], $this->productoFisicoRepository->findAll());
                echo json_encode($list);
            }
            return;
        }
        
        $payload = json_decode(file_get_contents('php://input'), true);
        if ($method === 'POST'){
            $categoria = $this->categoriaRepository->findById((int)$payload['idCategoria']??0);
            if (!$categoria) {
                http_response_code(400);
                echo json_encode(['error' => 'Categoria not found']);
                return;
            }
            $productoFisico = new ProductoFisico(
                null, 
                $payload['nombre'],
                $payload['descripcion'],
                (float)$payload['precioUnitario'],
                (int)$payload['stock'],
                $categoria,
                (float)$payload['peso'],
                (float)$payload['alto'],
                (float)$payload['ancho'],
                (float)$payload['profundidad']
            );
            echo json_encode(['success'=>$this->productoFisicoRepository->create($productoFisico)]);
        return;
        }

        if($method==='PUT'){
            $id = (int)($payload['id']??0);
            $existing = $this->productoFisicoRepository->findById($id);
            if(!$existing){
                http_response_code(404);
                echo json_encode(['error'=>'Producto Fisico not found']);
                return;
            }
            if(isset($payload['nombre'])) $existing->setNombre($payload['nombre']);
            if(isset($payload['descripcion'])) $existing->setDescripcion($payload['descripcion']);
            if(isset($payload['precioUnitario'])) $existing->setPrecioUnitario((float)$payload['precioUnitario']);
            if(isset($payload['stock'])) $existing->setStock((int)$payload['stock']);
            if(isset($payload['idCategoria'])) {
                $categoria = $this->categoriaRepository->findById((int)$payload['idCategoria']);
                if ($categoria) {
                    $existing->setCategoria($categoria);
                }
            }
            if(isset($payload['peso'])) $existing->setPeso($payload['peso']);
            if(isset($payload['alto'])) $existing->setAlto($payload['alto']);
            if(isset($payload['ancho'])) $existing->setAncho($payload['ancho']);
            if(isset($payload['profundidad'])) $existing->setProfundidad($payload['profundidad']);

            echo json_encode(['success'=>$this->productoFisicoRepository->update($existing)]);
            return;
        }

        if($method === 'DELETE'){
            echo json_encode(['success' => $this->productoFisicoRepository->delete((int)($payload['id']??0))]);
            return;
        }

    }
    private function productoFisicoToArray(ProductoFisico $producto): array
    {
        $categoria = $producto->getCategoria();
        $nombreCategoriaPadre = null;
        
        // Obtener el nombre de la categoría padre si existe
        if ($categoria->getIdPadre() !== null) {
            $categoriaPadreObj = $this->categoriaRepository->findById($categoria->getIdPadre());
            if ($categoriaPadreObj) {
                $nombreCategoriaPadre = $categoriaPadreObj->getNombre();
            }
        }
        
        return [
            'id' => $producto->getId(),
            'nombre' => $producto->getNombre(),
            'descripcion' => $producto->getDescripcion(),
            'precioUnitario' => $producto->getPrecioUnitario(),
            'stock' => $producto->getStock(),
            'dimensiones' => [
                'peso' => $producto->getPeso(),
                'alto' => $producto->getAlto(),
                'ancho' => $producto->getAncho(),
                'profundidad' => $producto->getProfundidad()
            ],
            'categoria' => [
                'id' => $categoria->getId(),
                'Categoria' => $nombreCategoriaPadre,
                'Sub Categoria' => $categoria->getNombre(),
                'descripcion' => $categoria->getDescripcion(),
                'estado' => $categoria->getEstado(),
                'idPadre' => $categoria->getIdPadre()
            ]
        ];
    }
}