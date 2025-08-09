<?php
    declare(strict_types=1);
    namespace App\Entities;
    use App\Entities\Venta;
    use App\Entities\Producto;

    class DetalleVenta{
        private int $idVenta;
        private int $lineNumber;
        private int $idProducto;
        private int $cantidad;
        private float $precioUnitario;
        private float $subtotal;

        public function __construct(
            int $idVenta,
            int $lineNumber,
            int $idProducto,
            int $cantidad,
            float $precioUnitario
        ) {
            $this->idVenta = $idVenta;
            $this->lineNumber = $lineNumber;
            $this->idProducto = $idProducto;
            $this->cantidad = $cantidad;
            $this->precioUnitario = $precioUnitario;
            $this->subtotal = $cantidad * $precioUnitario;
        }
        
        public function getIdVenta(): int { return $this->idVenta; }
        public function getLineNumber(): int { return $this->lineNumber; }
        public function getIdProducto(): int { return $this->idProducto; }
        public function getCantidad(): int { return $this->cantidad; }
        public function getPrecioUnitario(): float { return $this->precioUnitario; }
        public function getSubtotal(): float { return $this->subtotal; }

        public function setIdVenta(int $idVenta): void { $this->idVenta = $idVenta; }
        public function setLineNumber(int $lineNumber): void { $this->lineNumber = $lineNumber; }
        public function setIdProducto(int $idProducto): void { $this->idProducto = $idProducto; }
        
        public function setCantidad(int $cantidad): void { 
            $this->cantidad = $cantidad;
            $this->subtotal = $cantidad * $this->precioUnitario;
        }
        
        public function setPrecioUnitario(float $precioUnitario): void { 
            $this->precioUnitario = $precioUnitario;
            $this->subtotal = $this->cantidad * $precioUnitario;
        }
    }