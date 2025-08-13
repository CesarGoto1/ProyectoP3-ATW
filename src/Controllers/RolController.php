<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Repositories\RolRepository;
use App\Entities\Rol;

class RolController
{
    private RolRepository $rolRepository;

    public function __construct()
    {
        $this->rolRepository = new RolRepository();
    }

    public function rolToArray(Rol $rol): array
    {
        return [
            'id' => $rol->getId(),
            'nombre' => $rol->getNombre()
        ];
    }

    public function handle(): void
    {
        header('Content-Type: application/json');
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            if (isset($_GET['id'])) {
                $rol = $this->rolRepository->findById((int)$_GET['id']);
                echo json_encode($rol ? $this->rolToArray($rol) : null);
            } else {
                $list = array_map([$this, 'rolToArray'], $this->rolRepository->findAll());
                echo json_encode($list);
            }
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true);

        if ($method === 'POST') {
            if (!isset($payload['nombre'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Falta el campo nombre']);
                return;
            }
            $rol = new Rol(null, $payload['nombre']);
            echo json_encode(['success' => $this->rolRepository->create($rol)]);
            return;
        }

        if ($method === 'PUT') {
            $id = (int)($payload['id'] ?? 0);
            $existing = $this->rolRepository->findById($id);
            if (!$existing) {
                http_response_code(404);
                echo json_encode(['error' => 'Rol no encontrado']);
                return;
            }
            if (isset($payload['nombre'])) $existing->setNombre($payload['nombre']);
            echo json_encode(['success' => $this->rolRepository->update($existing)]);
            return;
        }

        if ($method === 'DELETE') {
            echo json_encode(['success' => $this->rolRepository->delete((int)($payload['id'] ?? 0))]);
            return;
        }
    }
}