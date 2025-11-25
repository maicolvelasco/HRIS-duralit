<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // único usuario creado
        $user = User::where('codigo', 'admin')->firstOrFail();

        // permisos que queremos asignarle
        $perms = Permission::whereIn('nombre', [
            'usuarios.vista',
            'usuarios.creacion',
            'usuarios.modificacion'
        ])->pluck('id');

        // insertar con granted = true
        $attach = $perms->map(fn($id) => [
            'user_id'      => $user->id,
            'permission_id'=> $id,
            'granted'      => true,
        ])->toArray();

        DB::table('users_permissions')->insert($attach);
    }
}