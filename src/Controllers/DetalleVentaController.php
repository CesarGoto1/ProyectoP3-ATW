<?php
    declare(strict_types=1);
    namespace App\Controllers;

    use App\Repositories\DetalleVentaRepository;
    use App\Entities\DetalleVenta;

    class DetalleVentaController
    {
        private DetalleVentaRepository $detalleVentaRepository;

        public function __construct(){
            $this->detalleVentaRepository = new DetalleVentaRepository();
        }

        public function detalleVentaToArray(DetalleVenta $detalleVenta):array{
            return [
                'idVenta'           =>$detalleVenta->getIdVenta(),
                'lineNumber'        =>$detalleVenta->getLineNumber(),
                'idProducto'        =>$detalleVenta->getIdProducto(),
                'cantidad'          =>$detalleVenta->getCantidad(),
                'precioUnitario'    =>$detalleVenta->getPrecioUnitario(),
                'subtotal'          =>$detalleVenta->getSubtotal()
            ];
        }

        public function handle():void{
            header('Content-Type: application/json');
            $method = $_SERVER['REQUEST_METHOD'];
            if($method==='GET'){
                if(isset($_GET['idVenta'])){
                    $detalles = $this->detalleVentaRepository->findByVentaId((int)$_GET['idVenta']);
                    $list = array_map([$this, 'detalleVentaToArray'], $detalles);
                    echo json_encode($list);
                }else{
                    $list = array_map([$this, 'detalleVentaToArray'], $this->detalleVentaRepository->findAll());
                    echo json_encode($list);
                }
                return;
            }
            $payload = json_decode(file_get_contents('php://input'),true);

            if($method === 'POST'){
                if(!isset($payload['idVenta'], $payload['lineNumber'], $payload['idProducto'], 
                        $payload['cantidad'], $payload['precioUnitario'])){
                    http_response_code(400);
                    echo json_encode(['error' => 'Faltan campos obligatorios']);
                    return;
                }
                $detalleVenta = new DetalleVenta(
                    (int)$payload['idVenta'],
                    (int)$payload['lineNumber'],
                    (int)$payload['idProducto'],
                    (int)$payload['cantidad'],
                    (float)$payload['precioUnitario']
                );
                
                echo json_encode(['success' => $this->detalleVentaRepository->create($detalleVenta)]);
                return;
            }

            if($method === 'PUT'){
                if(!isset($payload['idVenta'], $payload['lineNumber'])){
                    http_response_code(400);
                    echo json_encode(['error' => 'idVenta y lineNumber son obligatorios']);
                    return;
                }

                $detalleVenta = new DetalleVenta(
                    (int)$payload['idVenta'],
                    (int)$payload['lineNumber'],
                    (int)$payload['idProducto'],
                    (int)$payload['cantidad'],
                    (float)$payload['precioUnitario']
                );
                
                echo json_encode(['success' => $this->detalleVentaRepository->update($detalleVenta)]);
                return;
            }
            if($method === 'DELETE'){

                if(!isset($payload['idVenta'], $payload['lineNumber'])){
                    http_response_code(400);
                    echo json_encode(['error' => 'idVenta y lineNumber son obligatorios para eliminar']);
                    return;
                }

                $idVenta = (int)$payload['idVenta'];
                $lineNumber = (int)$payload['lineNumber'];
                
                echo json_encode(['success' => $this->detalleVentaRepository->deleteByCompositeKey($idVenta, $lineNumber)]);
                return;
            }
        }
    }
