<?php
declare (strict_types=1);
namespace App\Controllers;

use App\Entities\ProductoFisico;
use App\Repositories\ProductoFisicoRepository;
use App\Repositories\CategoriaRepository;
use App\Entities\Categoria;

class PfisicoController
{
    private ProductoFisicoRepository $pFrepository;
    private CategoriaRepository $categoriaRepository;

    public function __construct()
    {
        $this->pFrepository = new ProductoFisicoRepository();
        $this->categoriaRepository = new CategoriaRepository();
    }

    public function handle(): void{
        header('Content-Type: application/json');
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET'){
            if(isset($_GET['id'])){
                $pf = $this->pFrepository->findById((int)$_GET['id']);
                echo json_encode($pf ? $pf->toArray() : null);
            }else{
                $list = array_map([$this,'productoFisicoToArray'], $this->pFrepository->findAll());
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
            $productoFisico = new ProductoFisico(
                0, // ID will be set by the database
                $playload['nombre'],
                $playload['descripcion'],
                (float)$playload['precioUnitario'],
                (int)$playload['stock'],
                $categoria,
                (float)$playload['peso'],
                (float)$playload['alto'],
                (float)$playload['ancho'],
                (float)$playload['profundidad']
            );
        echo json_encode(['success'=>$this->pFrepository->create($productoFisico)]);
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