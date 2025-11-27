# Manual de instalación

Este documento describe los pasos necesarios para instalar y poner en funcionamiento la aplicación en un entorno de desarrollo o producción. Está orientado a administradores y personal de TI. Se incluyen instrucciones específicas para Windows (PowerShell) y opciones para Linux/WSL cuando procede.

## 1. Requisitos previos

Hardware y sistema
- Servidor o máquina con Windows 10/11, Windows Server o Linux (Ubuntu / CentOS).
- Recomendado: 4+ GB RAM, disco según volumen de datos.

Software
- PHP 8.0+ con extensiones: pdo, pdo_mysql (o pdo_pgsql), mbstring, openssl, tokenizer, xml, ctype, json, fileinfo, bcmath, curl, zip, gd (según funciones).
- Composer (gestor de dependencias PHP).
- Node.js >= 16 y npm o Yarn.
- Base de datos: MySQL 5.7+/8.0, MariaDB o PostgreSQL (según configuración).
- Servidor web para producción: Nginx o Apache (en Windows puede usarse IIS, XAMPP o WAMP).
- Git (opcional, para clonar repositorios).
- Opcional en Windows: WSL2 para entorno Linux, útil para despliegues.

Permisos y servicios
- Cuenta con permisos para crear bases de datos y modificar configuraciones en el servidor web.
- Acceso a credenciales SMTP si se usan notificaciones por correo.

## 2. Preparación del entorno (Windows — PowerShell)

1. Abrir PowerShell como administrador.
2. Ir a la carpeta del proyecto:
   ```
   cd "c:\Proyectos\controlt"
   ```
3. Confirmar versión de PHP y Composer:
   ```
   php -v
   composer -V
   node -v
   npm -v
   ```

Si se usan WSL, ejecutar los pasos equivalentes dentro de la distribución Linux.

## 3. Instalar dependencias PHP y Node

1. Instalar dependencias PHP:
   ```
   composer install
   ```
   - Para producción: `composer install --no-dev --optimize-autoloader`

2. Instalar dependencias Node:
   ```
   npm install
   ```
   - O con Yarn:
   ```
   yarn
   ```

3. Construir assets (desarrollo):
   ```
   npm run dev
   ```
   - Para producción/build optimizado:
   ```
   npm run build
   ```

## 4. Configuración de variables de entorno

1. Copiar el archivo de ejemplo:
   ```
   copy .env.example .env
   ```
   (En PowerShell: `Copy-Item .env.example .env`)

2. Editar `.env` y configurar:
   - APP_NAME, APP_ENV, APP_URL
   - DB_CONNECTION, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
   - MAIL_*, si se requiere envío de correos
   - Otros parámetros según integración (LDAP, terceros, etc.)

3. Generar la clave de la aplicación:
   ```
   php artisan key:generate
   ```

## 5. Base de datos y migraciones

1. Crear la base de datos en el servidor MySQL/MariaDB/Postgres.
2. Ejecutar migraciones y seeders (crear tablas y datos iniciales):
   ```
   php artisan migrate --seed
   ```
   - En desarrollo puede usarse `php artisan migrate:fresh --seed` (elimina datos previos).

Nota: Para entornos productivos ejecute migraciones después de respaldar la BD y en una ventana de mantenimiento si procede.

## 6. Archivos y permisos

1. Crear enlace público a almacenamiento:
   ```
   php artisan storage:link
   ```
2. Asegurar permisos de escritura en:
   - storage/
   - bootstrap/cache/
   En Windows, comprobar que la cuenta del IIS o del servidor web tenga permisos. En Linux:
   ```
   sudo chown -R www-data:www-data storage bootstrap/cache
   sudo chmod -R 755 storage bootstrap/cache
   ```

## 7. Configuración del servidor web

Desarrollo (servidor integrado)
- Ejecutar servidor de desarrollo:
  ```
  php artisan serve
  ```
  Acceder en: http://localhost:8000

## 8. Tareas programadas y workers

- Scheduler (Laravel): en Linux agregar cron:
  ```
  * * * * * cd /ruta/al/proyecto && php artisan schedule:run >> /dev/null 2>&1
  ```
  En Windows use el Programador de tareas para ejecutar `php artisan schedule:run` cada minuto.

- Workers de cola: en producción ejecutar supervisord o un servicio que mantenga `php artisan queue:work` en ejecución. En Windows, se puede usar NSSM o crear un servicio.

## 9. Desarrollo y pruebas locales

- Crear usuario administrador (según seeders o comando específico del proyecto).
- Acceder a la URL, iniciar sesión y verificar módulos (Personas, Roles, Asistencias, Reportes).
- Probar generación de PDFs/Excel y funcionalidades de subida/descarga.

## 10. Checklist previo a producción

- .env configurado (APP_ENV=production, APP_DEBUG=false).
- APP_KEY generado.
- Backups de la base de datos antes de migraciones.
- Certificado TLS configurado (HTTPS).
- Permisos y propiedad de archivos correctos.
- Jobs y scheduler configurados.
- Monitoreo y logging activados (logs rotados).
- Revisión de dependencias y actualizaciones de seguridad.

## 11. Resolución de problemas comunes

- Error de extensiones PHP: activar extensiones requeridas en php.ini y reiniciar el servicio PHP-FPM/IIS.
- Permisos de almacenamiento: comprobar que storage/ y bootstrap/cache/ sean escribibles por el usuario del servidor web.
- Errores en assets: ejecutar `npm run build` y limpiar caché del navegador.
- Fallo en migraciones: revisar mensajes en consola, verificar conexión y credenciales de BD.

## 12. Contacto y referencias

Proporcione al equipo de soporte:
- Ruta del repositorio y commit referenciado.
- Contenido relevante de `.env` (sin claves privadas completas).
- Registros de error (storage/logs/laravel.log).
- Pasos realizados y salida de comandos fallidos.

Para despliegues automatizados, se recomienda documentar scripts CI/CD que ejecuten: composer install, npm ci, npm run build, php artisan migrate --force, y reinicio de workers.

---

Fin del manual. Guarde este archivo como MANUAL_INSTALACION.md en la raíz del proyecto.