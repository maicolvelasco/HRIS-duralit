<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'id'          => Str::uuid(),
                'nombre'      => 'Super Administrador',
                'descripcion' => 'Aquel que controla todo en cualquier momento',
            ],
            [
                'id'          => Str::uuid(),
                'nombre'      => 'Administrador',
                'descripcion' => 'Aquel que controla usuarios',
            ],
            [
                'id'          => Str::uuid(),
                'nombre'      => 'Administrativo',
                'descripcion' => 'Aquel que trabaja en oficina',
            ],
            [
                'id'          => Str::uuid(),
                'nombre'      => 'Operativo',
                'descripcion' => 'Aquel que trabaja en talleres',
            ],
            [
                'id'          => Str::uuid(),
                'nombre'      => 'Externos',
                'descripcion' => 'Aquel que trabaja desde afuera',
            ],
        ];

        foreach ($roles as $rol) {
            Rol::create($rol);
        }
    }
}