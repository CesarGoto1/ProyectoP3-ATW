<?php
    declare(strict_types=1);
    namespace App\Controllers;

    use App\Repositories\DetalleVentaRepository;
    use App\Repositories\ProductoFisicoRepository;
    use App\Repositories\ProductoDigitalRepository;
    use App\Entities\DetalleVenta;

    class DetalleVentaController
    {
        private DetalleVentaRepository $detalleVentaRepository;
        private ProductoFisicoRepository $productoFisicoRepository;
        private ProductoDigitalRepository $productoDigitalRepository;

        public function __construct(){
            $this->detalleVentaRepository = new DetalleVentaRepository();
            $this->productoFisicoRepository = new ProductoFisicoRepository();
            $this->productoDigitalRepository = new ProductoDigitalRepository();
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
                if(!isset($payload['idVenta'], $payload['idProducto'], $payload['cantidad'])){
                    http_response_code(400);
                    echo json_encode(['error' => 'Faltan campos obligatorios']);
                    return;
                }
                $idVenta = (int)$payload['idVenta'];
                if (!isset($payload['lineNumber']) || !$payload['lineNumber']) {
                    $ultimo = $this->detalleVentaRepository->findMaxLineNumberByVenta($idVenta);
                    $payload['lineNumber'] = $ultimo + 1;
                }
                $idProducto = (int)$payload['idProducto'];
                $producto = $this->productoFisicoRepository->findById($idProducto) ?? $this->productoDigitalRepository->findById($idProducto);
                if(!$producto){
                    http_response_code(404);
                    echo json_encode(['error' => 'Producto no encontrado']);
                    return;
                }
                $precioUnitario = $producto->getPrecioUnitario();

                $detalleVenta = new DetalleVenta(
                    $idVenta,
                    (int)$payload['lineNumber'],
                    $idProducto,
                    (int)$payload['cantidad'],
                    $precioUnitario
                );
                echo json_encode(['success' => $this->detalleVentaRepository->create($detalleVenta)]);
                return;
            }

            if($method === 'PUT'){
                if(!isset($payload['idVenta'], $payload['lineNumber'], $payload['idProducto'], $payload['cantidad'])){
                    http_response_code(400);
                    echo json_encode(['error' => 'Faltan campos obligatorios']);
                    return;
                }

                $idProducto = (int)$payload['idProducto'];
                $producto = $this->productoFisicoRepository->findById($idProducto) ?? $this->productoDigitalRepository->findById($idProducto);
                if(!$producto){
                    http_response_code(404);
                    echo json_encode(['error' => 'Producto no encontrado']);
                    return;
                }
                $precioUnitario = $producto->getPrecioUnitario();

                $detalleVenta = new DetalleVenta(
                    (int)$payload['idVenta'],
                    (int)$payload['lineNumber'],
                    $idProducto,
                    (int)$payload['cantidad'],
                    $precioUnitario
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
