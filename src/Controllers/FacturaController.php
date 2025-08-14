<?php
    declare(strict_types=1);
    namespace App\Controllers;

    use App\Entities\Factura;
    use App\Repositories\FacturaRepository;
    use App\Repositories\VentaRepository;
    use DateTime;

    class FacturaController
    {
        private FacturaRepository $facturaRepository;
        private VentaRepository $ventaRepository;

        public function __construct()
        {
            $this->facturaRepository = new FacturaRepository();
            $this->ventaRepository = new VentaRepository();
        }

        public function facturaToArray(Factura $factura): array
        {
            $venta = $factura->getVenta();
            return [
                'id'            => $factura->getId(),
                'idVenta'       => $venta ? $venta->getId() : null,
                'numero'        => $factura->getNumero(),
                'claveAcceso'   => $factura->getClaveAcceso(),
                'fechaEmision'  => $factura->getFechaEmision()->format('Y-m-d'),
                'estado'        => $factura->getEstado(),
                'venta'         => $venta ? [
                    'id'        => $venta->getId(),
                    'fecha'     => $venta->getFecha()->format('Y-m-d'),
                    'total'     => $venta->getTotal(),
                    'estado'    => $venta->getEstado()
                ] : null
            ];
        }

        public function handle(): void
        {
            header('Content-Type: application/json');
            $method = $_SERVER['REQUEST_METHOD'];

            if ($method === 'GET') {
                if (isset($_GET['id'])) {
                    $factura = $this->facturaRepository->findById((int)$_GET['id']);
                    echo json_encode($factura ? $this->facturaToArray($factura) : null);
                } else {
                    $list = array_map([$this, 'facturaToArray'], $this->facturaRepository->findAll());
                    echo json_encode($list);
                }
                return;
            }

            $payload = json_decode(file_get_contents('php://input'), true);

            if ($method === 'POST') {
                if (!isset($payload['idVenta'], $payload['numero'], $payload['claveAcceso'], $payload['fechaEmision'], $payload['estado'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Faltan campos obligatorios']);
                    return;
                }
                $venta = $this->ventaRepository->findById((int)$payload['idVenta']);
                if (!$venta) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Venta no encontrada']);
                    return;
                }
                $estado = $payload['estado'];
                if (!in_array($estado, ['pendiente', 'emitida'])) {
                    http_response_code(400);
                    echo json_encode(['error' => "Estado debe ser 'pendiente' o 'emitida'"]);
                    return;
                }
                $fechaStr = $payload['fechaEmision'];
                if (is_numeric($fechaStr)) {
                    $fechaStr = date('Y-m-d', strlen($fechaStr) === 10 ? intval($fechaStr) : intval($fechaStr)/1000);
                }
                $fecha = new DateTime($fechaStr);
                $factura = new Factura(
                    null,
                    $venta,
                    $payload['numero'],
                    $payload['claveAcceso'],
                    $fecha,
                    $estado
                );
                echo json_encode(['success' => $this->facturaRepository->create($factura)]);
                return;
            }

            if ($method === 'PUT') {
                $id = (int)($payload['id'] ?? 0);
                $existing = $this->facturaRepository->findById($id);
                if (!$existing) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Factura no encontrada']);
                    return;
                }
                if (isset($payload['numero'])) $existing->setNumero($payload['numero']);
                if (isset($payload['claveAcceso'])) $existing->setClaveAcceso($payload['claveAcceso']);
                if(isset($payload['fechaEmision'])) {
                    $fechaStr = $payload['fechaEmision'];
                    if (is_numeric($fechaStr)) {
                        $fechaStr = date('Y-m-d', strlen($fechaStr) === 10 ? intval($fechaStr) : intval($fechaStr)/1000);
                    }
                    $existing->setFechaEmision(new DateTime($fechaStr));
                }
                if (isset($payload['estado'])) $existing->setEstado($payload['estado']);
                echo json_encode(['success' => $this->facturaRepository->update($existing)]);
                return;
            }

            if ($method === 'DELETE') {
                $id = (int)($payload['id'] ?? 0);
                $factura = $this->facturaRepository->findById($id);
                if (!$factura) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Factura no encontrada']);
                    return;
                }
                if ($factura->getEstado() === 'emitida') {
                    http_response_code(400);
                    echo json_encode(['error' => 'No se pueden borrar facturas emitidas']);
                    return;
                }
                echo json_encode(['success' => $this->facturaRepository->delete($id)]);
                return;
            }
        }
    }