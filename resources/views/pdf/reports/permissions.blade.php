<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Permisos</title>
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
            margin: 12mm 8mm 8mm 8mm;
        }

        .header {
            border-bottom: 2px solid #DC2626;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }

        .header h1 {
            font-size: 16px;
            color: #1F2937;
            margin: 0;
        }

        .header p {
            font-size: 8px;
            color: #6B7280;
            margin-top: 2px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #DC2626;
            color: #fff;
            padding: 5px 4px;
            font-weight: 600;
            font-size: 8px;
            text-align: left;
        }

        td {
            padding: 4px;
            font-size: 8px;
            border-bottom: 0.5px solid #E5E7EB;
            vertical-align: top;
        }

        tr:nth-child(even) {
            background: #F9FAFB;
        }

        .roles {
            font-size: 7px;
            color: #6B7280;
            line-height: 1.2;
        }

        .footer {
            margin-top: 10px;
            text-align: center;
            font-size: 7px;
            color: #9CA3AF;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Permisos</h1>
        <p>Generado el: {{ $generated_at->format('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>NOMBRE</th>
                <th>DESCRIPCIÓN</th>
                <th>ROLES ASIGNADOS</th>
                <th>CREADO EL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $permission)
            <tr>
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
                <td>{{ $permission->created_at->format('d/m/Y H:i') }}</td>
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