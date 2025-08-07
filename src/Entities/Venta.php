<?php   
    declare(strict_types=1);
    namespace App\Entities;
    use App\Entities\Cliente;
    class Venta{
        private ?int $id;
        private \DateTime $fecha;
        private Cliente $cliente;
        private float $total;
        private string $estado;

        public function __construct(
            ?int $id,
            \DateTime $fecha,
            Cliente $cliente,
            float $total,
            string $estado = 'borrador'
        ){
            $this->id = $id;
            $this->fecha = $fecha;
            $this->cliente = $cliente;
            $this->total = $total;
            $this->estado = $estado;
        }

        public function getId():?int{return $this->id;}
        public function getFecha():\DateTime{return $this->fecha;}
        public function getCliente():Cliente{return $this->cliente;}
        public function getTotal():float{return $this->total;}
        public function getEstado():string{return $this->estado;}

        public function setId(?int $id):void{$this->id=$id;}
        public function setFecha(\DateTime $fecha):void{$this->fecha=$fecha;}
        public function setCliente(Cliente $cliente):void{$this->cliente=$cliente;}
        public function setTotal(float $total):void{$this->total=$total;}
        public function setEstado(string $estado):void{$this->estado=$estado;}
    }