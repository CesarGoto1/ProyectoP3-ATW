<?php
declare (strict_types=1);
namespace App\Controllers;

use App\Entities\Categoria;
use App\Repositories\CategoriaRepository;

class CategoriaController
{
    private CategoriaRepository $categoriaRepo;

    public function __construct()
    {
        $this->categoriaRepo = new CategoriaRepository();
    }
    public function handle():void{
        header('Content-Type: application/json');
        $method = $_SERVER['REQUEST_METHOD'];
        
        if ($method === 'GET'){
            if(isset($_GET['id'])){
                $categoria = $this->categoriaRepo->findById((int)$_GET['id']);
                echo json_encode($categoria ? $categoria->toArray() : null);
            }else{
                $list = array_map([$this,'categoriaToArray'], $this->categoriaRepo->findAll());
                echo json_encode($list);
            }
        }
        return;
        $playload = json_decode(file_get_contents('php://input'), true);
        if ($method === 'POST') {
            $categoria = $this->categoriaRepo->findById((int)$playload['id']??0);
            if (!$categoria) {
                http_response_code(400);
                echo json_encode(['error' => 'categoria no válida']);
                return;
            }
            $categoria = new Categoria(
                0,
                $playload['nombre'],
                $playload['descripcion'],
                $playload['estado'] ?? 'activo',
                $playload['idPadre'] ?? null
            );
        echo json_encode(['success'=>$this->categoriaRepo->create($categoria)]);
        }   
    }

    public function categoriaToArray(Categoria $categoria): array
    {
        return [
            'id' => $categoria->getId(),
            'nombre' => $categoria->getNombre(),
            'descripcion' => $categoria->getDescripcion(),
            'estado' => $categoria->getEstado(),
            'idPadre' => $categoria->getIdPadre()
        ];
    }
}