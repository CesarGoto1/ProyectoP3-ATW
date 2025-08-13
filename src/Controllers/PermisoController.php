<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Repositories\PermisoRepository;
use App\Entities\Permiso;

class PermisoController
{
    private PermisoRepository $permisoRepository;

    public function __construct()
    {
        $this->permisoRepository = new PermisoRepository();
    }

    public function permisoToArray(Permiso $permiso): array
    {
        return [
            'id' => $permiso->getId(),
            'codigo' => $permiso->getCodigo()
        ];
    }

    public function handle(): void
    {
        header('Content-Type: application/json');
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            if (isset($_GET['id'])) {
                $permiso = $this->permisoRepository->findById((int)$_GET['id']);
                echo json_encode($permiso ? $this->permisoToArray($permiso) : null);
            } else {
                $list = array_map([$this, 'permisoToArray'], $this->permisoRepository->findAll());
                echo json_encode($list);
            }
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true);

        if ($method === 'POST') {
            if (!isset($payload['codigo'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Falta el campo codigo']);
                return;
            }
            $permiso = new Permiso(null, $payload['codigo']);
            echo json_encode(['success' => $this->permisoRepository->create($permiso)]);
            return;
        }

        if ($method === 'PUT') {
            $id = (int)($payload['id'] ?? 0);
            $existing = $this->permisoRepository->findById($id);
            if (!$existing) {
                http_response_code(404);
                echo json_encode(['error' => 'Permiso no encontrado']);
                return;
            }
            if (isset($payload['codigo'])) $existing->setcodigo($payload['codigo']);
            echo json_encode(['success' => $this->permisoRepository->update($existing)]);
            return;
        }

        if ($method === 'DELETE') {
            echo json_encode(['success' => $this->permisoRepository->delete((int)($payload['id'] ?? 0))]);
            return;
        }
    }
}