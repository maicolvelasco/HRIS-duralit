# MANUAL DE USUARIO

## 1. Introducción
Bienvenido al sistema de gestión empresarial. Esta aplicación está diseñada para gestionar personas, roles, permisos, turnos, asistencias, sobretiempos, autorizaciones y generar reportes. Es una herramienta orientada a usuarios administrativos y de recursos humanos que requiere permisos adecuados para acceder a cada módulo. Este manual explica, en un lenguaje claro y no técnico, cómo utilizar las principales funcionalidades de la aplicación y resolver las dudas más frecuentes.

## 2. Requisitos previos
Antes de comenzar asegúrese de contar con:
- Acceso a la URL del sistema proporcionada por el área de TI.
- Credenciales de usuario (código de usuario y contraseña) activas.
- Permisos asignados por el administrador para las acciones necesarias (crear, editar, ver, exportar).
- Navegador web moderno (Chrome, Edge, Firefox) y conexión a Internet estable.
Si necesita apoyo para la instalación o despliegue, solicite asistencia del equipo de TI.

## 3. Instalación y configuración inicial
La instalación técnica se realiza por el área de TI. Para usuarios finales, los pasos para comenzar a trabajar son:

1. Solicite al equipo de TI la URL del sistema y sus credenciales iniciales.
2. Abra la URL en su navegador y ubique la pantalla de inicio de sesión. En la pantalla de login debe ingresar su "Código de Usuario" y su contraseña. Si su cuenta está desactivada, el sistema mostrará un mensaje de aviso y deberá contactar al administrador.
![alt text](image.png)
3. Tras autenticarse con éxito accederá al panel principal (Dashboard). Desde allí encontrará un menú lateral con las secciones habilitadas según sus permisos.

Nota: Si olvida la contraseña contacte al administrador para restablecerla.

## 4. Funcionalidades principales

A continuación se describen los módulos principales que encontrará en la aplicación y sus funciones básicas.

- Personas
  - Crear, editar y listar personas/usuarios. Permite asignar código, datos personales, estado (activo/inactivo) y rol.
  - Formularios de creación y edición disponibles desde la sección de Ajustes o Personas.
![alt text](image-1.png)
- Roles
  - Crear y editar roles del sistema. Asignar un conjunto de permisos a cada rol para controlar el acceso a módulos y acciones.
![alt text](image-2.png)
- Permisos del sistema
  - Definición y administración de permisos individuales que pueden asignarse a roles o a usuarios específicos.
![alt text](image-3.png)
- Permisos de Trabajo / Autorizaciones
  - Solicitudes de autorizaciones (por horas o días), gestión de estado (pendiente, aprobado, rechazado) y trazabilidad de firmas.
![alt text](image-6.png)
- Turnos / Horarios
  - Definir y editar horarios y turnos asignables a personas o secciones. Control de inicio y fin de jornadas.
![alt text](image-5.png)
- Asistencias
  - Registro de entradas y salidas, tanto automáticas como manuales. Visualización de históricos de marcaciones y ediciones administrativas.
![alt text](image-4.png)
- Sobretiempos
  - Registro de horas extra, justificaciones y cálculos asociados.
![alt text](image-7.png)
- Firmas de permisos
  - Recolección de firma (canvas) para validaciones de autorizaciones y comprobantes.
![alt text](image-8.png)
- Reportes y exportaciones
  - Generación de reportes en PDF o Excel para personas, roles, permisos, turnos, autorizaciones y feriados. Posibilidad de aplicar filtros por fechas y secciones.
![alt text](image-9.png)
Cada módulo ofrece botones y formularios claros para crear, editar, eliminar y exportar información. Los accesos y botones visibles dependen de los permisos asignados a su usuario.

## 5. Flujo de uso completo
A continuación se describe un flujo típico de uso para un usuario administrativo:

1. Inicio de sesión
   - Acceda con su código y contraseña en la pantalla de login.
![alt text](image-10.png)
2. Gestión de personas
   - Vaya a Ajustes → Personas, haga clic en "Nueva persona", complete los campos obligatorios y asigne un rol.
![alt text](image-11.png)
3. Asignación de roles y permisos
   - En Ajustes → Roles cree o edite un rol y marque los permisos que correspondan.
![alt text](image-12.png)
4. Registro de asistencias
   - Desde Asistencias puede revisar marcaciones, registrar entradas o salidas manuales y corregir incidencias.
![alt text](image-13.png)
5. Gestión de autorizaciones
   - Desde Permisos de Trabajo cree la solicitud indicando tipo, motivo y rango de fechas. La solicitud pasará a revisión según el flujo de aprobación.
![alt text](image-14.png)
6. Generación de reportes
   - Abra el generador de reportes, elija la sección, aplique filtros y seleccione formato (PDF/Excel). Descargue el archivo generado.
![alt text](image-15.png)
7. Cierre de sesión
   - Use la opción de cerrar sesión en el menú de usuario para terminar la sesión de forma segura.
![alt text](image-16.png)
Este flujo puede variar según las políticas internas de su organización y los permisos configurados para cada rol.

## 6. Ejemplos de uso con entradas y resultados esperados

- Crear persona
  - Entrada: Desde Ajustes → Personas haga clic en "Nueva persona", introduzca nombre, apellido, código, correo, asigne rol y guarde.
![alt text](image-17.png)
  - Resultado esperado: La nueva persona aparece en la lista con estado "Activo" y puede editarse o desactivarse.
![alt text](image-18.png)
- Registrar entrada manual
  - Entrada: En Asistencias seleccione "Registrar entrada manual", elija la persona, la fecha y la hora, y confirme.
![alt text](image-19.png)
  - Resultado esperado: La marcación se añade al registro diario y queda visible en el histórico.
![alt text](image-20.png)
- Solicitud de permiso de trabajo
  - Entrada: En Permisos cree una nueva solicitud indicando tipo (horas/días), motivo y fecha de inicio/finalización.
  ![alt text](image-21.png)
  - Resultado esperado: La solicitud queda en estado "Pendiente" y notifica a los aprobadores para su revisión. Tras aprobación, el estado cambia a "Aprobado" y se registra la autorización.
![alt text](image-22.png)
- Generar reporte de personas (PDF)
  - Entrada: Abrir el generador de reportes, seleccionar "Personas", ajustar filtro de fechas (si aplica) y elegir formato PDF.
  ![alt text](image-23.png)
  - Resultado esperado: Se descarga un archivo PDF con el listado y los datos solicitados según filtro.
![alt text](image-24.png)
## 7. Preguntas frecuentes (FAQ)

1. ¿Qué hago si no puedo iniciar sesión?
   - Verifique sus credenciales y que su cuenta esté activa. Si el problema persiste, contacte al administrador con su código de usuario.

2. ¿Cómo creo un nuevo usuario?
   - Acceda a Ajustes → Personas y utilice el botón "Nueva persona". Complete los campos obligatorios y asigne un rol.

3. ¿Cómo asigno permisos a un rol?
   - En Ajustes → Roles edite el rol deseado y marque los permisos disponibles, luego guarde los cambios.

4. ¿Puedo registrar asistencias manualmente?
   - Sí. En la sección de Asistencias encontrará opciones para registrar entradas y salidas manuales y corregir marcaciones.

5. ¿Cómo genero un reporte con filtros por fecha?
   - Use el generador de reportes, seleccione la categoría, aplique el rango de fechas deseado y el formato de exportación, luego descargue el archivo.

6. ¿Qué hago si un reporte no descarga correctamente?
   - Compruebe su conexión y vuelva a intentar. Si el error persiste, anote el nombre del reporte, filtros usados y hora del intento, y envíe esos datos al equipo de soporte.

## 8. Soporte y contacto
Si necesita asistencia, envíe al equipo de soporte interno la siguiente información:
- Nombre y código de usuario.
- Descripción detallada del problema y los pasos para reproducirlo.
- Hora aproximada en la que ocurrió el incidente.
- Capturas de pantalla (si es posible) para facilitar la identificación del problema.

Contactos recomendados:
- Soporte Técnico (TI): solicite la vía oficial interna (ticket, email o sistema de incidencias).
- Administrador de la aplicación: responsable de asignación de permisos y usuarios.

Notas técnicas para el equipo de TI/desarrollo (para referencia interna):
- Verificar rutas, controladores y vistas relacionadas con usuarios, permisos, asistencias y reportes.
- Revisar logs del servidor cuando se reporten errores en generación de PDF/Excel o fallos de autenticación.

---

Este manual está diseñado para usuarios finales y resume las funciones y flujos más utilizados. Para cambios en reglas de negocio, permisos o despliegues, contacte al equipo responsable de administración del sistema.