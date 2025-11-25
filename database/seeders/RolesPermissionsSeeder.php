<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener los IDs de los permisos que nos interesan
        $permisos = Permission::whereIn('nombre', [
            'usuarios.vista',
            'usuarios.creacion',
            'usuarios.modificacion'
        ])->pluck('id');

        // Obtener los roles que deben recibir esos permisos
        $roles = Rol::whereIn('nombre', ['Super Administrador', 'Administrador'])->get();

        // Asignar los mismos permisos a ambos roles
        foreach ($roles as $rol) {
            $rol->permissions()->attach($permisos);
        }
    }
}