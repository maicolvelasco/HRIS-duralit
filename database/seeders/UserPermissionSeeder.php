<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class UserPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('codigo', 'admin')->first();
        if (! $user) return;

        // Todos los permisos excepto "Control de Asistencia Propio"
        $permisos = Permission::where('nombre', '!=', 'Control de Asistencia Propio')
                              ->pluck('id')
                              ->mapWithKeys(fn($id) => [$id => ['granted' => 1]]);

        $user->permissions()->sync($permisos);
    }
}