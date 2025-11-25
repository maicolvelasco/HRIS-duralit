<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte de Permisos</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; margin: 0; padding: 0; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 40px; }
        .header { border-bottom: 3px solid #DC2626; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { color: #1F2937; margin: 0; font-size: 28px; }
        .header p { color: #6B7280; margin: 5px 0 0; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #DC2626; color: white; padding: 12px; text-align: left; font-weight: 600; }
        td { padding: 10px 12px; border-bottom: 1px solid #E5E7EB; }
        tr:nth-child(even) { background: #F9FAFB; }
        .footer { margin-top: 40px; text-align: center; color: #9CA3AF; font-size: 12px; }
        .roles { font-size: 11px; color: #6B7280; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔑 Reporte de Permisos</h1>
            <p>Generado el: {{ $generated_at->format('d/m/Y H:i:s') }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>NOMBRE</th>
                    <th>DESCRIPCIÓN</th>
                    <th>ROLES ASIGNADOS</th>
                    <th>CREADO EL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $permission)
                <tr>
                    <td>{{ $permission->id }}</td>
                    <td>{{ $permission->nombre }}</td>
                    <td>{{ $permission->descripcion ?? 'N/A' }}</td>
                    <td>
                        @if($permission->roles->isNotEmpty())
                            <div class="roles">
                                {{ $permission->roles->pluck('nombre')->implode(', ') }}
                            </div>
                        @else
                            <em>Sin roles</em>
                        @endif
                    </td>
                    <td>{{ $permission->created_at->format('d/m/Y H:i:s') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            <p>© {{ date('Y') }} - Sistema de Gestión de Recursos Humanos</p>
        </div>
    </div>
</body>
</html>