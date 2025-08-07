<?php
    declare(strict_types=1);
    namespace App\Entities;
    abstract class Cliente{
        protected ?int $id;
        protected string $email;
        protected string $telefono;
        protected string $direccion;

        public function __construct(?int $id, string $email, string $telefono, string $direccion){
            $this->id = $id;
            $this->direccion = $direccion;
            $this->email = $email;
            $this->telefono = $telefono;
            
        }

        public function getId():?int{return $this->id;}
        public function getEmail():string{return $this->email;}
        public function getTelefono():string{return $this->telefono;}
        public function getDireccion():string{return $this->direccion;}

        public function setId(?int $id):void{$this->id=$id;}
        public function setEmail(string $email):void{$this->email=$email;}
        public function setTelefono(string $telefono):void{$this->telefono=$telefono;}
        public function setDireccion(string $direccion):void{$this->direccion=$direccion;}
    }