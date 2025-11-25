<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $perms = [
            ['id'=>Str::uuid(),'nombre'=>'usuarios.vista',  'descripcion'=>'Ver listado'],
            ['id'=>Str::uuid(),'nombre'=>'usuarios.creacion', 'descripcion'=>'Crear usuario'],
            ['id'=>Str::uuid(),'nombre'=>'usuarios.modificacion', 'descripcion'=>'Editar usuario'],
        ];
        foreach ($perms as $p) Permission::create($p);
    }
}