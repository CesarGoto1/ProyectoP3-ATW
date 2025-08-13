<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Repositories\RolPermisoRepository;
use App\Repositories\RolRepository;
use App\Repositories\PermisoRepository;
use App\Entities\Rol;
use App\Entities\Permiso;
use App\Entities\RolPermiso;
use PDOException;

class RolPermisoController
{
    private RolPermisoRepository $rolPermisoRepository;
    private RolRepository $rolRepository;
    private PermisoRepository $permisoRepository;

    public function __construct()
    {
        $this->rolPermisoRepository = new RolPermisoRepository();
        $this->rolRepository = new RolRepository();
        $this->permisoRepository = new PermisoRepository();
    }

    public function rolPermisoToArray(RolPermiso $rolPermiso): array
    {
        return [
            'rol' => [
                'id' => $rolPermiso->getRol()->getId(),
                'nombre' => $rolPermiso->getRol()->getNombre()
            ],
            'permiso' => [
                'id' => $rolPermiso->getPermiso()->getId(),
                'codigo' => $rolPermiso->getPermiso()->getCodigo()
            ]
        ];
    }

    public function handle(): void
    {
        header('Content-Type: application/json');
        $method = $_SERVER['REQUEST_METHOD'];

        try {
            if ($method === 'GET') {
                if (isset($_GET['idRol']) && isset($_GET['idPermiso'])) {
                    $rol = $this->rolRepository->findById((int)$_GET['idRol']);
                    $permiso = $this->permisoRepository->findById((int)$_GET['idPermiso']);
                    if (!$rol || !$permiso) {
                        http_response_code(404);
                        echo json_encode(['error' => 'Rol o Permiso no encontrado']);
                        return;
                    }
                    $rel = $this->rolPermisoRepository->findByCompositeKey($rol->getId(), $permiso->getId());
                    echo json_encode($rel ? $this->rolPermisoToArray($rel) : null);
                } else {
                    $list = array_map([$this, 'rolPermisoToArray'], $this->rolPermisoRepository->findAll());
                    echo json_encode($list);
                }
                return;
            }

            $payload = json_decode(file_get_contents('php://input'), true);

            if ($method === 'POST') {
                if (!isset($payload['idRol'], $payload['idPermiso'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Faltan campos idRol o idPermiso']);
                    return;
                }
                $rol = $this->rolRepository->findById((int)$payload['idRol']);
                $permiso = $this->permisoRepository->findById((int)$payload['idPermiso']);
                if (!$rol || !$permiso) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Rol o Permiso no encontrado']);
                    return;
                }
                $rel = new RolPermiso($rol, $permiso);
                try {
                    $success = $this->rolPermisoRepository->create($rel);
                    echo json_encode(['success' => $success]);
                } catch (PDOException $e) {
                    http_response_code(400);
                    echo json_encode(['error' => $e->getMessage()]);
                }
                return;
            }

            if ($method === 'DELETE') {
                if (!isset($payload['idRol'], $payload['idPermiso'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Faltan campos idRol o idPermiso']);
                    return;
                }
                $rol = $this->rolRepository->findById((int)$payload['idRol']);
                $permiso = $this->permisoRepository->findById((int)$payload['idPermiso']);
                if (!$rol || !$permiso) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Rol o Permiso no encontrado']);
                    return;
                }
                try {
                    $success = $this->rolPermisoRepository->deleteByCompositeKey($rol->getId(), $permiso->getId());
                    echo json_encode(['success' => $success]);
                } catch (PDOException $e) {
                    http_response_code(400);
                    echo json_encode(['error' => $e->getMessage()]);
                }
                return;
            }

            // No se implementa PUT porque no tiene sentido actualizar una relación intermedia
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error interno: ' . $e->getMessage()]);
        }
    }
}