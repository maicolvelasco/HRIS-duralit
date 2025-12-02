<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permisos = [
            ['nombre' => 'Ver Roles', 'descripcion' => 'Ver opcion de Rolesss'],
            ['nombre' => 'Modificar Roles', 'descripcion' => 'Opcion de Editar algun Rol'],
            ['nombre' => 'Crear Roles', 'descripcion' => 'Crear los Roles disponibles'],
            ['nombre' => 'Ver Permisos', 'descripcion' => 'Visualizar permisos'],
            ['nombre' => 'Crear Permisos', 'descripcion' => 'Creacion de Permisos'],
            ['nombre' => 'Modificar Permisos', 'descripcion' => 'Modificar los permisos de la aplicacion'],
            ['nombre' => 'Ver Permisos Personales', 'descripcion' => 'Ver Permisos de cada persona'],
            ['nombre' => 'Activar Permisos Personales', 'descripcion' => 'Activar los permisos para los usuarios'],
            ['nombre' => 'Desactivar Permisos Personales', 'descripcion' => 'Desactivar los permisos de las personas'],
            ['nombre' => 'Ver Sucursales', 'descripcion' => 'Ver Sucursales de la aplicacion'],
            ['nombre' => 'Crear Sucursales', 'descripcion' => 'Crear las Sucursales'],
            ['nombre' => 'Modificar Sucursales', 'descripcion' => 'Modificar las Sucursales'],
            ['nombre' => 'Ver Grupos', 'descripcion' => 'Ver los Grupos'],
            ['nombre' => 'Ver Secciones', 'descripcion' => 'Ver las Secciones'],
            ['nombre' => 'Crear Grupos', 'descripcion' => 'Crear los Grupos'],
            ['nombre' => 'Crear Secciones', 'descripcion' => 'Crear las  Secciones'],
            ['nombre' => 'Modificar Secciones', 'descripcion' => 'Modificar las Secciones'],
            ['nombre' => 'Modificar Grupos', 'descripcion' => 'Modificar los Grupos'],
            ['nombre' => 'Ver Turnos', 'descripcion' => 'Ver los Turnos'],
            ['nombre' => 'Crear Turnos', 'descripcion' => 'Crear los Turnos'],
            ['nombre' => 'Modificar Turnos', 'descripcion' => 'Modificar los Turnos'],
            ['nombre' => 'Ver Feriados', 'descripcion' => 'Ver los Feriados'],
            ['nombre' => 'Crear Feriados', 'descripcion' => 'Crear los Feriados'],
            ['nombre' => 'Modificar Feriados', 'descripcion' => 'Modificar los Feriados'],
            ['nombre' => 'Eliminar Feriados', 'descripcion' => 'Eliminar los Feriados'],
            ['nombre' => 'Ver Permisos de Trabajo', 'descripcion' => 'Ver Permisos de Trabajo que hay'],
            ['nombre' => 'Crear Permisos de Trabajo', 'descripcion' => 'Crear Permisos de Trabajo que hay'],
            ['nombre' => 'Modificar Permisos de Trabajo', 'descripcion' => 'Modificar Permisos de Trabajo que hay'],
            ['nombre' => 'Ver Reportes', 'descripcion' => 'Permite ver la sección de reportes'],
            ['nombre' => 'Descargar Reportes Excel', 'descripcion' => 'Permite descargar reportes en formato Excel'],
            ['nombre' => 'Descargar Reportes PDF', 'descripcion' => 'Permite descargar reportes en formato PDF'],
            ['nombre' => 'Control de Jerarquia', 'descripcion' => 'Control de Jerarquia de permisos'],
            ['nombre' => 'Control de Asistencia', 'descripcion' => 'Control Completo de Asistencia'],
            ['nombre' => 'Registro de Entrada', 'descripcion' => 'Registro de Entrada manual'],
            ['nombre' => 'Registro de Salida', 'descripcion' => 'Registro de Salida manual'],
            ['nombre' => 'Control de Asistencia Propio', 'descripcion' => 'Control de Asistencia Propio'],
            ['nombre' => 'Ver Autorizaciones', 'descripcion' => 'Ver Autorizaciones de trabajo'],
            ['nombre' => 'Ver mis Autorizaciones', 'descripcion' => 'Ver Mis Autorizaciones hechas'],
            ['nombre' => 'Ver Nóminas', 'descripcion' => 'Ver listado de nóminas'],
            ['nombre' => 'Crear Nóminas', 'descripcion' => 'Crear nuevas nóminas'],
            ['nombre' => 'Editar Nóminas', 'descripcion' => 'Editar nóminas en borrador'],
            ['nombre' => 'Eliminar Nóminas', 'descripcion' => 'Eliminar nóminas'],
            ['nombre' => 'Aprobar Nóminas', 'descripcion' => 'Aprobar nóminas calculadas'],
            ['nombre' => 'Registrar Pagos', 'descripcion' => 'Registrar pagos de nóminas'],
            ['nombre' => 'Calcular Nóminas', 'descripcion' => 'Calcular descuentos automáticamente'],
            ['nombre' => 'Generar Recibos', 'descripcion' => 'Generar PDF de recibos de pago'],
            ['nombre' => 'Ver Usuarios', 'descripcion' => 'Ver listado'],
            ['nombre' => 'Crear Usuarios', 'descripcion' => 'Crear usuario'],
            ['nombre' => 'Modificar Usuarios', 'descripcion' => 'Editar usuario'],
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(
                ['nombre' => $permiso['nombre']],
                [
                    'id'          => Str::uuid(),
                    'descripcion' => $permiso['descripcion'],
                ]
            );
        }
    }
}