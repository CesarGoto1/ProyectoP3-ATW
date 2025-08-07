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
                echo json_encode($categoria ? $this->categoriaToArray($categoria) : null);
            }else{
                $list = array_map([$this,'categoriaToArray'], $this->categoriaRepo->findAll());
                echo json_encode($list);
            }
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true);
        if ($method === 'POST') {

            $categoria = new Categoria(
                null,
                $payload['nombre'],
                $payload['descripcion'],
                $payload['estado'] ?? 'activo',
                $payload['idPadre'] ?? null
            );
        echo json_encode(['success'=>$this->categoriaRepo->create($categoria)]);
        return;
        }
        if($method==='PUT'){
                $id = (int)($payload['id']??0);

                $existing = $this->categoriaRepo->findById($id);
                if(!$existing){
                    http_response_code(404);
                    echo json_encode(['error'=>'Categoria not found']);
                    return;
                }
                if(isset($payload['nombre'])) $existing->setNombre($payload['nombre']);
                if(isset($payload['descripcion'])) $existing->setDescripcion($payload['descripcion']);
                if(isset($payload['estado'])) $existing->setEstado($payload['estado']);
                if(isset($payload['idPadre'])) $existing->setIdPadre($payload['idPadre']);
                

                echo json_encode(['success'=>$this->categoriaRepo->update($existing)]);
                return;
            }

            if($method==='DELETE'){
                echo json_encode(['success'=>$this->categoriaRepo->delete((int)($payload['id']??0))]);
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