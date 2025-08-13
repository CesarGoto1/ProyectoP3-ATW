<?php
declare(strict_types=1);
namespace App\Entities;

use App\Entities\Venta;

class Factura
{
    private ?int $id;
    private Venta $venta;
    private string $numero;
    private string $claveAcceso;
    private \DateTime $fechaEmision;
    private string $estado;

    public function __construct(
        ?int $id,
        Venta $venta,
        string $numero,
        string $claveAcceso,
        \DateTime $fechaEmision,
        string $estado = 'pendiente'
    ){
        $this->id = $id;
        $this->venta = $venta;
        $this->numero = $numero;
        $this->claveAcceso = $claveAcceso;
        $this->fechaEmision = $fechaEmision;
        $this->estado = $estado;
    }

    public function getId(): ?int { return $this->id; }
    public function getVenta(): Venta { return $this->venta; }
    public function getNumero(): string { return $this->numero; }
    public function getClaveAcceso(): string { return $this->claveAcceso; }
    public function getFechaEmision(): \DateTime { return $this->fechaEmision; }
    public function getEstado(): string { return $this->estado; }

    public function setId(?int $id): void { $this->id = $id; }
    public function setVenta(Venta $venta): void { $this->venta = $venta; }
    public function setNumero(string $numero): void { $this->numero = $numero; }
    public function setClaveAcceso(string $claveAcceso): void { $this->claveAcceso = $claveAcceso; }
    public function setFechaEmision(\DateTime $fechaEmision): void { $this->fechaEmision = $fechaEmision; }
    public function setEstado(string $estado): void { $this->estado = $estado; }
}