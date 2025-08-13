<?php
declare(strict_types=1);
namespace App\Entities;

use App\Entities\Rol;
use App\Entities\Permiso;

class RolPermiso
{
    private Rol $rol;
    private Permiso $permiso;

    public function __construct(Rol $rol, Permiso $permiso)
    {
        $this->rol = $rol;
        $this->permiso = $permiso;
    }

    public function getRol(): Rol
    {
        return $this->rol;
    }

    public function getPermiso(): Permiso
    {
        return $this->permiso;
    }

    public function setRol(Rol $rol): void
    {
        $this->rol = $rol;
    }

    public function setPermiso(Permiso $permiso): void
    {
        $this->permiso = $permiso;
    }
}