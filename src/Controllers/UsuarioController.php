<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Repositories\UsuarioRepository;
use App\Entities\Usuario;

class UsuarioController
{
    private UsuarioRepository $usuarioRepository;

    public function __construct()
    {
        $this->usuarioRepository = new UsuarioRepository();
    }

    public function usuarioToArray(Usuario $usuario): array
    {
        return [
            'id' => $usuario->getId(),
            'username' => $usuario->getUsername(),
            'estado' => $usuario->getEstado()
        ];
    }

    public function handle(): void
    {
        header('Content-Type: application/json');
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            if (isset($_GET['id'])) {
                $usuario = $this->usuarioRepository->findById((int)$_GET['id']);
                echo json_encode($usuario ? $this->usuarioToArray($usuario) : null);
            } else {
                $list = array_map([$this, 'usuarioToArray'], $this->usuarioRepository->findAll());
                echo json_encode($list);
            }
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true);

        if ($method === 'POST') {
            if (!isset($payload['username'], $payload['password'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Faltan campos obligatorios']);
                return;
            }
            $passwordHash = password_hash($payload['password'], PASSWORD_ARGON2ID);
            $usuario = new Usuario(null, $payload['username'], $passwordHash, $payload['estado'] ?? 'activo');
            echo json_encode(['success' => $this->usuarioRepository->create($usuario)]);
            return;
        }

        if ($method === 'PUT') {
            $id = (int)($payload['id'] ?? 0);
            $existing = $this->usuarioRepository->findById($id);
            if (!$existing) {
                http_response_code(404);
                echo json_encode(['error' => 'Usuario no encontrado']);
                return;
            }
            if (isset($payload['username'])) $existing->setUsername($payload['username']);
            if (isset($payload['password'])) $existing->setPasswordHash(password_hash($payload['password'], PASSWORD_ARGON2ID));
            if (isset($payload['estado'])) $existing->setEstado($payload['estado']);
            echo json_encode(['success' => $this->usuarioRepository->update($existing)]);
            return;
        }

        if ($method === 'DELETE') {
            echo json_encode(['success' => $this->usuarioRepository->delete((int)($payload['id'] ?? 0))]);
            return;
        }
    }
}