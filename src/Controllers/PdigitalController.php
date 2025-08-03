<?php
declare (strict_types=1);
namespace App\Controllers;

use App\Entities\ProductoDigital;
use App\Repositories\ProductoDigitalRepository;
use App\Repositories\CategoriaRepository;
use App\Entities\Categoria;

class PdigitalController
{
    private ProductoDigitalRepository $pDrepository;
    private CategoriaRepository $categoriaRepository;

    public function __construct()
    {
        $this->pDrepository = new ProductoDigitalRepository();
        $this->categoriaRepository = new CategoriaRepository();
    }

    public function handle(): void{
        header('Content-Type: application/json');
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET'){
            if(isset($_GET['id'])){
                $pd = $this->pDrepository->findById((int)$_GET['id']);
                echo json_encode($pd ? $this->productoDigitalToArray($pd) : null);
            }else{
                $list = array_map([$this,'productoDigitalToArray'], $this->pDrepository->findAll());
                echo json_encode($list);
            }
        }
        return;
        $playload = json_decode(file_get_contents('php://input'), true);
        if ($method === 'POST'){
            $categoria = $this->categoriaRepository->findById((int)$playload['idCategoria']??0);
            if (!$categoria) {
                http_response_code(400);
                echo json_encode(['error' => 'Categoria not found']);
                return;
            }
            $productoDigital = new ProductoDigital(
                0, // ID will be set by the database
                $playload['nombre'],
                $playload['descripcion'],
                (float)$playload['precioUnitario'],
                (int)$playload['stock'],
                $categoria,
                $playload['urlDescarga'],
                $playload['licencia']
            );
        echo json_encode(['success'=>$this->pDrepository->create($productoDigital)]);
        }
    }
    
    private function productoDigitalToArray(ProductoDigital $producto): array
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
