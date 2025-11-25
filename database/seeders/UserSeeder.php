<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Rol;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminRol = Rol::where('nombre', 'Super Administrador')->first();

        User::create([
            'id' => Str::uuid(),
            'nombre' => 'Super',
            'apellido' => 'Administrador',
            'codigo' => 'admin',
            'password' => Hash::make('admin123'),
            'is_active' => 1,
            'rol_id' => $superAdminRol?->id,
        ]);
    }
}