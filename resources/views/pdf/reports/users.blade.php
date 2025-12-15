<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Personas</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #fff;
            color: #1F2937;
            font-size: 10px;
            line-height: 1.3;
        }

        @page {
            size: A4 landscape;
            margin: 15mm 10mm 10mm 10mm;
        }

        .header {
            border-bottom: 2px solid #2563EB;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }

        .header h1 {
            font-size: 18px;
            color: #1F2937;
            margin: 0;
        }

        .header p {
            font-size: 9px;
            color: #6B7280;
            margin-top: 2px;
        }

        .filters {
            background: #EBF4FF;
            padding: 6px 8px;
            border-radius: 4px;
            margin-bottom: 10px;
            font-size: 9px;
            color: #1E40AF;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #2563EB;
            color: #fff;
            padding: 6px 5px;
            font-weight: 600;
            font-size: 9px;
            text-align: left;
        }

        td {
            padding: 5px;
            font-size: 9px;
            border-bottom: 0.5px solid #E5E7EB;
        }

        tr:nth-child(even) {
            background: #F9FAFB;
        }

        .status-activo {
            color: #10B981;
            font-weight: 600;
        }

        .status-inactivo {
            color: #EF4444;
            font-weight: 600;
        }

        .footer {
            margin-top: 12px;
            text-align: center;
            font-size: 8px;
            color: #9CA3AF;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Personas</h1>
        <p>Generado el: {{ $generated_at->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="filters">
        <strong>Filtros:</strong>
        {{ $filters['date_range'] === 'between'
            ? 'Registros desde ' . $filters['start_date'] . ' hasta ' . $filters['end_date']
            : 'Todos los registros' }}
    </div>

    <table>
        <thead>
            <tr>
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
                <td>{{ $user->nombre }}</td>
                <td>{{ $user->apellido }}</td>
                <td>{{ $user->codigo }}</td>
                <td>
                    <span class="{{ $user->is_active ? 'status-activo' : 'status-inactivo' }}">
                        {{ $user->is_active ? 'ACTIVO' : 'INACTIVO' }}
                    </span>
                </td>
                <td>{{ $user->branch->nombre ?? 'N/A' }}</td>
                <td>{{ $user->group->nombre ?? 'N/A' }}</td>
                <td>{{ $user->section->nombre ?? 'N/A' }}</td>
                <td>{{ $user->rol->nombre ?? 'SIN ROL' }}</td>
                <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>© {{ date('Y') }} - Sistema de Gestión de Recursos Humanos</p>
        <p>Página generada el {{ $generated_at->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>