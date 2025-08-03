<?php
    declare(strict_types=1);
    namespace App\Entities;

    class PersonaNatural extends Cliente{
        private string $nombres;
        private string $apellido;
        private string $cedula;

        public function __construct(
            ?int $id, 
            string $email, 
            string $telefono, 
            string $direccion, 
            string $nombres, 
            string $apellidos, 
            string $cedula
        ){
            parent::__construct($id, $email, $telefono, $direccion);
            $this->nombres=$nombres;
            $this->apellidos=$apellidos;
            $this->cedula=$cedula;
        }

        public function getNombres():string{return $this->nombres;}
        public function getApellidos():string{return $this->apellidos;}
        public function getCedula():string{return $this->cedula;}

        public function setNombres(string $nombres):void{$this->nombres=$nombres;}
        public function setApellidos(string $apellidos):void{$this->apellidos=$apellidos;}
        public function setCedula(string $cedula):void{$this->cedula=$cedula;}
    }