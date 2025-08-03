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
                    $payload['telefono'],
                    $payload['direccion'],
                    $payload['nombres'],
                    $payload['apellidos'],
                    $payload['cedula']
                );
                echo json_encode(['success'=>$this->personaNaturalRepo->create($personaNatural)]);
                return;
            }
        }
    }   