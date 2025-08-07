<?php   
    declare(strict_types=1);
    namespace App\Controllers;

    use App\Repositories\PersonaNaturalRepo;
    use App\Entities\Cliente;
    use App\Entities\PersonaNatural;
    
    class PersonaNaturalController
    {
        private PersonaNaturalRepo $personaNaturalRepo;

        public function __construct(){
            $this->personaNaturalRepo = new PersonaNaturalRepo();
        }

        public function personaNaturalToArray(PersonaNatural $personNat): array{
            return [
                'id'                        =>$personNat->getId(),
                'direccion'                 =>$personNat->getDireccion(),
                'email'                     =>$personNat->getEmail(),
                'telefono'                  =>$personNat->getTelefono(),
                'nombres'                   =>$personNat->getNombres(),
                'apellidos'                 =>$personNat->getApellidos(),
                'cedula'                    =>$personNat->getCedula()
            ];
        }

        public function handle():void{
            header('Content-Type: application/json');
            $method = $_SERVER['REQUEST_METHOD'];
            if($method==='GET'){
                if(isset($_GET['id'])){
                    $personaNatural = $this->personaNaturalRepo->findById((int)$_GET['id']);
                    echo json_encode($personaNatural?$this->personaNaturalToArray($personaNatural):null);
                }else{
                    $list = array_map([$this, 'personaNaturalToArray'], $this->personaNaturalRepo->findAll());
                    echo json_encode($list);
                }
                return;
            }
            $payload = json_decode(file_get_contents('php://input'),true);

            if($method==='POST'){
                $personaNatural = new PersonaNatural(
                    null,
                    $payload['direccion'],
                    $payload['email'],
                    $payload['telefono'],
                    $payload['nombres'],
                    $payload['apellidos'],
                    $payload['cedula']
                );
                echo json_encode(['success'=>$this->personaNaturalRepo->create($personaNatural)]);
                return;
            }

            if($method==='PUT'){
                $id = (int)($payload['id']??0);

                $existing = $this->personaNaturalRepo->findById($id);
                if(!$existing){
                    http_response_code(404);
                    echo json_encode(['error'=>'Persona Natural not found']);
                    return;
                }
                if(isset($payload['direccion'])) $existing->setDireccion($payload['direccion']);
                if(isset($payload['email'])) $existing->setEmail($payload['email']);
                if(isset($payload['telefono'])) $existing->setTelefono($payload['telefono']);
                if(isset($payload['nombres'])) $existing->setNombres($payload['nombres']);
                if(isset($payload['apellidos'])) $existing->setApellidos($payload['apellidos']);
                if(isset($payload['cedula'])) $existing->setCedula($payload['cedula']);

                echo json_encode(['success'=>$this->personaNaturalRepo->update($existing)]);
                return;
            }

            if($method === 'DELETE'){
                echo json_encode(['success' => $this->personaNaturalRepo->delete((int)($payload['id']??0))]);
                return;
            }
        }
    }   