<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RolPermissionSeeder extends Seeder
{
    public function run(): void
    {
        /* ---------- mapeo NOMBRE-rol → nombres de permisos ---------- */
        $map = [
            'Super Administrador' => [
                'Ver Roles','Modificar Roles','Crear Roles',
                'Ver Permisos','Crear Permisos','Modificar Permisos',
                'Ver Permisos Personales','Activar Permisos Personales','Desactivar Permisos Personales',
                'Ver Sucursales','Crear Sucursales','Modificar Sucursales',
                'Ver Grupos','Ver Secciones','Crear Grupos','Crear Secciones','Modificar Secciones','Modificar Grupos',
                'Ver Turnos','Crear Turnos','Modificar Turnos',
                'Ver Feriados','Crear Feriados','Modificar Feriados','Eliminar Feriados',
                'Ver Permisos de Trabajo','Crear Permisos de Trabajo','Modificar Permisos de Trabajo',
                'Ver Reportes','Descargar Reportes Excel','Descargar Reportes PDF',
                'Control de Jerarquia',
                'Control de Asistencia','Registro de Entrada','Registro de Salida','Control de Asistencia Propio',
                'Ver Autorizaciones','Ver mis Autorizaciones',
                'Ver Nóminas','Crear Nóminas','Editar Nóminas','Eliminar Nóminas','Aprobar Nóminas','Registrar Pagos','Calcular Nóminas','Generar Recibos',
                'Ver Usuarios','Crear Usuarios','Modificar Usuarios'
            ],
            'Administrador' => [
                'Ver Permisos Personales','Activar Permisos Personales','Desactivar Permisos Personales',
                'Ver Sucursales','Crear Sucursales','Modificar Sucursales',
                'Ver Grupos','Ver Secciones','Crear Grupos','Crear Secciones','Modificar Secciones','Modificar Grupos',
                'Ver Turnos','Crear Turnos','Modificar Turnos',
                'Ver Feriados','Crear Feriados','Modificar Feriados','Eliminar Feriados',
                'Ver Permisos de Trabajo','Crear Permisos de Trabajo','Modificar Permisos de Trabajo',
                'Ver Reportes','Descargar Reportes Excel','Descargar Reportes PDF',
                'Control de Jerarquia',
                'Control de Asistencia','Registro de Entrada','Registro de Salida',
                'Ver Autorizaciones',
                'Ver Nóminas','Crear Nóminas','Editar Nóminas','Eliminar Nóminas','Aprobar Nóminas','Registrar Pagos','Calcular Nóminas','Generar Recibos',
                'Ver Usuarios','Crear Usuarios','Modificar Usuarios'
            ],
            'Administrativo' => [
                'Control de Asistencia Propio',
                'Ver Autorizaciones',
                'Ver mis Autorizaciones'
            ],
            'Operativo' => [
                'Control de Asistencia Propio',
                'Ver mis Autorizaciones'
            ],
            'Externos' => [
                'Control de Asistencia Propio',
                'Ver mis Autorizaciones'
            ],
        ];

        foreach ($map as $rolNombre => $permisosNombres) {
            $rol = Rol::where('nombre', $rolNombre)->first();
            if (! $rol) continue;

            $perms = Permission::whereIn('nombre', $permisosNombres)->pluck('id');
            $rol->permissions()->sync($perms);
        }
    }
}