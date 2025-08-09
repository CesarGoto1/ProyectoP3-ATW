<?php   
    declare(strict_types=1);
    namespace App\Controllers;

    use App\Repositories\PersonaJuridicaRepository;

    use App\Entities\PersonaJuridica;
    
    class PersonaJuridicaController
    {
        private PersonaJuridicaRepository $personaJuridicaRepository;

        public function __construct(){
            $this->personaJuridicaRepository = new PersonaJuridicaRepository();
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
                    $personaJuridica = $this->personaJuridicaRepository->findById((int)$_GET['id']);
                    echo json_encode($personaJuridica?$this->personaJuridicaToArray($personaJuridica):null);
                }else{
                    $list = array_map([$this, 'personaJuridicaToArray'], $this->personaJuridicaRepository->findAll());
                    echo json_encode($list);
                }
                return;
            }

            $payload = json_decode(file_get_contents('php://input'),true);

            if($method==='POST'){
                $personaJuridica = new PersonaJuridica(
                    null,
                    $payload['direccion'],
                    $payload['email'],
                    $payload['telefono'],
                    $payload['razonSocial'],
                    $payload['ruc'],
                    $payload['representanteLegal']
                );
                echo json_encode(['success'=>$this->personaJuridicaRepository->create($personaJuridica)]);
                return;
            }

            if($method==='PUT'){
                $id = (int)($payload['id']??0);

                $existing = $this->personaJuridicaRepository->findById($id);
                if(!$existing){
                    http_response_code(404);
                    echo json_encode(['error'=>'Persona Juridica not found']);
                    return;
                }
                if(isset($payload['direccion'])) $existing->setDireccion($payload['direccion']);
                if(isset($payload['email'])) $existing->setEmail($payload['email']);
                if(isset($payload['telefono'])) $existing->setTelefono($payload['telefono']);
                if(isset($payload['razonSocial'])) $existing->setRazonSocial($payload['razonSocial']);
                if(isset($payload['ruc'])) $existing->setRuc($payload['ruc']);
                if(isset($payload['representanteLegal'])) $existing->setRepresentanteLegal($payload['representanteLegal']);

                echo json_encode(['success'=>$this->personaJuridicaRepository->update($existing)]);
                return;
            }

            if($method === 'DELETE'){
                echo json_encode(['success' => $this->personaJuridicaRepository->delete((int)($payload['id']??0))]);
                return;
            }
        }
    }   