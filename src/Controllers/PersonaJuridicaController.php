<?php   
    declare(strict_types=1);
    namespace App\Controllers;

    use App\Repositories\PersonaJuridicaRepo;
    use App\Entities\Cliente;
    use App\Entities\PersonaJuridica;
    
    class PersonaJuridicaController
    {
        private PersonaJuridicaRepo $personaJuridicaRepo;

        public function __construct(){
            $this->personaJuridicaRepo = new PersonaJuridicaRepo();
        }

        public function personaJuridicaToArray(PersonaJuridica $personJur): array{
            return [
                'id'                        =>$personJur->getId(),
                'direccion'                 =>$personJur->getDireccion(),
                'email'                     =>$personJur->getEmail(),
                'telefono'                  =>$personJur->getTelefono(),
                'razonSocial'               =>$personJur->getRazonSocial(),
                'ruc'                       =>$personJur->getRuc(),
                'representanteLegal'        =>$personJur->getRepresentanteLegal()
            ];
        }

        public function handle():void{
            header('Content-Type: application/json');
            $method = $_SERVER['REQUEST_METHOD'];
            if($method==='GET'){
                if(isset($_GET['id'])){
                    $personaJuridica = $this->personaJuridicaRepo->findById((int)$_GET['id']);
                    echo json_encode($personaJuridica?$this->personaJuridicaToArray($personaJuridica):null);
                }else{
                    $list = array_map([$this, 'personaJuridicaToArray'], $this->personaJuridicaRepo->findAll());
                    echo json_encode($list);
                }
                return;
            }

            $payload = json_decode(file_get_contents('php://input'),true);

            if($method==='POST'){
                $personaJuridica = new PersonaJuridica(
                    null,
                    $payload['telefono'],
                    $payload['direccion'],
                    $payload['razonSocial'],
                    $payload['ruc'],
                    $payload['representanteLegal']
                );
                echo json_encode(['success'=>$this->personaJuridicaRepo->create($personaJuridica)]);
                return;
            }
        }
    }   