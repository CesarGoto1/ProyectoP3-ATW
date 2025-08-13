<?php
    declare(strict_types=1);
    namespace App\Entities;

    class PersonaNatural extends Cliente{
        private string $nombres;
        private string $apellidos;
        private string $cedula;

        public function __construct(
            ?int $id, 
            string $direccion,
            string $email, 
            string $telefono, 
            string $nombres, 
            string $apellidos, 
            string $cedula
        ){
            parent::__construct($id, $email, $telefono, $direccion);
            $this->nombres=$nombres;
            $this->apellidos=$apellidos;
            $this->cedula=$cedula;
        }
        private static function validarCedulaEcuatoriana($cedula) {
            if (!preg_match('/^\d{10}$/', $cedula)) return false;
            $provincia = intval(substr($cedula, 0, 2));
            if ($provincia < 1 || $provincia > 24) return false;
            $digitos = str_split($cedula);
            $suma = 0;
            for ($i = 0; $i < 9; $i++) {
                $valor = intval($digitos[$i]);
                if ($i % 2 == 0) {
                    $valor *= 2;
                    if ($valor > 9) $valor -= 9;
                }
                $suma += $valor;
            }
            $digitoVerificador = (10 - ($suma % 10)) % 10;
            return $digitoVerificador == intval($digitos[9]);
        }
        public function getNombres():string{return $this->nombres;}
        public function getApellidos():string{return $this->apellidos;}
        public function getCedula():string{return $this->cedula;}

        public function setNombres(string $nombres):void{$this->nombres=$nombres;}
        public function setApellidos(string $apellidos):void{$this->apellidos=$apellidos;}
        public function setCedula(string $cedula):void{
            if (!self::validarCedulaEcuatoriana($cedula)) {
                throw new \InvalidArgumentException('Cédula ecuatoriana inválida');
            }
            $this->cedula = $cedula;
        }
    }