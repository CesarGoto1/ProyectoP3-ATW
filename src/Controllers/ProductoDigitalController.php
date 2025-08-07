<?php
declare (strict_types=1);
namespace App\Controllers;

use App\Entities\ProductoDigital;
use App\Repositories\ProductoDigitalRepository;
use App\Repositories\CategoriaRepository;
use App\Entities\Categoria;

class ProductoDigitalController
{
    private ProductoDigitalRepository $productoDigitalRepo;
    private CategoriaRepository $categoriaRepository;

    public function __construct()
    {
        $this->productoDigitalRepo = new ProductoDigitalRepository();
        $this->categoriaRepository = new CategoriaRepository();
    }

    public function handle(): void{
        header('Content-Type: application/json');
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET'){
            if(isset($_GET['id'])){
                $pd = $this->productoDigitalRepo->findById((int)$_GET['id']);
                echo json_encode($pd ? $this->productoDigitalToArray($pd) : null);
            }else{
                $list = array_map([$this,'productoDigitalToArray'], $this->productoDigitalRepo->findAll());
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
            $productoDigital = new ProductoDigital(
                null,
                $payload['nombre'],
                $payload['descripcion'],
                (float)$payload['precioUnitario'],
                (int)$payload['stock'],
                $categoria,
                $payload['urlDescarga'],
                $payload['licencia']
            );
            echo json_encode(['success'=>$this->productoDigitalRepo->create($productoDigital)]);
            return;
        }
        if($method==='PUT'){
            $id = (int)($payload['id']??0);
            $existing = $this->productoDigitalRepo->findById($id);
            if(!$existing){
                http_response_code(404);
                echo json_encode(['error'=>'Producto Digital not found']);
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
            if(isset($payload['urlDescarga'])) $existing->setUrlDescarga($payload['urlDescarga']);
            if(isset($payload['licencia'])) $existing->setLicencia($payload['licencia']);

            echo json_encode(['success'=>$this->productoDigitalRepo->update($existing)]);
            return;
        }

        if($method === 'DELETE'){
            echo json_encode(['success' => $this->productoDigitalRepo->delete((int)($payload['id']??0))]);
            return;
        }

    }
    
    private function productoDigitalToArray(ProductoDigital $producto): array
    {
        $categoria = $producto->getCategoria();
        $nombreCategoriaPadre = null;
        
        
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
            'informacionDigital' => [
                'urlDescarga' => $producto->getUrlDescarga(),
                'licencia' => $producto->getLicencia()
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
