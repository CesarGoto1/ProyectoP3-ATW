<?php
declare (strict_types=1);
namespace App\Controllers;

use App\Entities\Venta;
use App\Entities\PersonaNatural;
use App\Entities\PersonaJuridica;
use App\Repositories\VentaRepository;
use App\Repositories\PersonaNaturalRepository;
use App\Repositories\PersonaJuridicaRepository;
use DateTime;

class VentaController
{
    private VentaRepository $ventaRepository;
    private PersonaJuridicaRepository $personaJuridicaRepository;
    private PersonaNaturalRepository $personaNaturalRepository;
    public function __construct(){
        $this->ventaRepository = new VentaRepository();
        $this->personaJuridicaRepository = new PersonaJuridicaRepository();
        $this->personaNaturalRepository = new PersonaNaturalRepository();
    }

    public function ventaToArray(Venta $venta):array{
        $cliente = $venta->getCliente();
        $clienteArray = [
            'id'            => $cliente->getId(),
            'email'         => $cliente->getEmail(),
            'telefono'      => $cliente->getTelefono(),
            'direccion'     => $cliente->getDireccion(),
            'tipoCliente'          => get_class($cliente)
        ];
        if ($cliente instanceof PersonaNatural){
            $clienteArray['nombres'] = $cliente->getNombres();
            $clienteArray['apellidos'] = $cliente->getApellidos();
            $clienteArray['cedula'] = $cliente->getCedula();
        }elseif ($cliente instanceof PersonaJuridica){
            $clienteArray['razonSocial'] = $cliente->getRazonSocial();
            $clienteArray['ruc'] = $cliente->getRuc();
            $clienteArray['representanteLegal'] = $cliente->getRepresentanteLegal();
        }
        return [
            'id'            =>$venta->getId(),
            'fecha'         =>$venta->getFecha()->format('Y-m-d'),
            'idCliente'     =>$venta->getCliente()->getId(),
            'total'         =>$venta->getTotal(),
            'estado'        =>$venta->getEstado(),
            'cliente'       =>$clienteArray
        ];
    }

    public function handle():void{
        header('Content-Type: application/json');
            $method = $_SERVER['REQUEST_METHOD'];
            if($method==='GET'){
                if(isset($_GET['id'])){
                    $venta = $this->ventaRepository->findById((int)$_GET['id']);
                    echo json_encode($venta?$this->ventaToArray($venta):null);
                }else{
                    $list = array_map([$this, 'ventaToArray'], $this->ventaRepository->findAll());
                    echo json_encode($list);
                }
                return;
            }
            $payload = json_decode(file_get_contents('php://input'),true);
            
            if($method === 'POST'){
                $cliente = $this->personaJuridicaRepository->findById((int)$payload['idCliente'])
                        ?? $this->personaNaturalRepository->findById((int)$payload['idCliente']);

                if(!$cliente){
                    http_response_code(400);
                    echo json_encode(['error' => 'Cliente not found']);
                    return;
                }

                $venta = new Venta(
                    null,
                    new DateTime($payload['fecha']),
                    $cliente,
                    (float)$payload['total'],
                    $payload['estado'] ?? 'borrador'
                );
                echo json_encode(['success' => $this->ventaRepository->create($venta)]);
                return;
            }

        if($method === 'PUT'){
            $id = (int)($payload['id'] ?? 0);
            $existing = $this->ventaRepository->findById($id);
            
            if(!$existing){
                http_response_code(404);
                echo json_encode(['error' => 'Venta not found']);
                return;
            }

            if(isset($payload['fecha'])) $existing->setFecha(new DateTime($payload['fecha']));
            if(isset($payload['total'])) $existing->setTotal((float)$payload['total']);
            if(isset($payload['estado'])) $existing->setEstado($payload['estado']);
            
            if(isset($payload['idCliente'])) {
                $cliente = $this->personaJuridicaRepository->findById((int)$payload['idCliente']) 
                        ?? $this->personaNaturalRepository->findById((int)$payload['idCliente']);
                if ($cliente) {
                    $existing->setCliente($cliente);
                }
            }

            echo json_encode(['success' => $this->ventaRepository->update($existing)]);
            return;
        }
        if($method === 'DELETE'){
            echo json_encode(['success'=> $this->ventaRepository->delete((int)($payload['id'] ?? 0))]);
            return;
        }
            
    }
}