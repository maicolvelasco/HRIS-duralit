<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte de Personas</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; margin: 0; padding: 0; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 40px; }
        .header { border-bottom: 3px solid #2563EB; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { color: #1F2937; margin: 0; font-size: 28px; }
        .header p { color: #6B7280; margin: 5px 0 0; }
        .filters { background: #EBF4FF; padding: 15px; border-radius: 8px; margin-bottom: 30px; }
        .filters strong { color: #1E40AF; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #2563EB; color: white; padding: 12px; text-align: left; font-weight: 600; }
        td { padding: 10px 12px; border-bottom: 1px solid #E5E7EB; }
        tr:nth-child(even) { background: #F9FAFB; }
        .footer { margin-top: 40px; text-align: center; color: #9CA3AF; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 Reporte de Personas</h1>
            <p>Generado el: {{ $generated_at->format('d/m/Y H:i:s') }}</p>
        </div>

        <div class="filters">
            <strong>Filtros aplicados:</strong> 
            {{ $filters['date_range'] === 'between' ? 'Registros desde ' . $filters['start_date'] . ' hasta ' . $filters['end_date'] : 'Todos los registros' }}
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>NOMBRE</th>
                    <th>APELLIDO</th>
                    <th>CÓDIGO</th>
                    <th>ESTADO</th>
                    <th>SUCURSAL</th>
                    <th>GRUPO</th>
                    <th>SECCIÓN</th>
                    <th>ROL</th>
                    <th>CREADO</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->nombre }}</td>
                    <td>{{ $user->apellido }}</td>
                    <td>{{ $user->codigo }}</td>
                    <td>
                        <span style="color: {{ $user->is_active ? '#10B981' : '#EF4444' }}; font-weight: 600;">
                            {{ $user->is_active ? 'ACTIVO' : 'INACTIVO' }}
                        </span>
                    </td>
                    <td>{{ $user->branch->nombre ?? 'N/A' }}</td>
                    <td>{{ $user->group->nombre ?? 'N/A' }}</td>
                    <td>{{ $user->section->nombre ?? 'N/A' }}</td>
                    <td>{{ $user->rol->nombre ?? 'SIN ROL' }}</td>
                    <td>{{ $user->created_at->format('d/m/Y H:i:s') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            <p>© {{ date('Y') }} - Sistema de Gestión de Recursos Humanos</p>
            <p>Página generada el {{ $generated_at->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>