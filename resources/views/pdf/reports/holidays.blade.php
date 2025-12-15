<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Feriados</title>
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

        .filters {
            background: #FEF2F2;
            padding: 4px 6px;
            border-radius: 3px;
            margin-bottom: 6px;
            border-left: 2px solid #DC2626;
            font-size: 7px;
            color: #991B1B;
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

        .branches {
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
        <h1>Reporte de Feriados</h1>
        <p>Generado el: {{ $generated_at->format('d/m/Y H:i:s') }}</p>
    </div>

    @if($filters['date_range'] === 'between')
        <div class="filters">
            <strong>Rango:</strong> Desde {{ $filters['start_date'] }} hasta {{ $filters['end_date'] }}
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>FECHA</th>
                <th>NOMBRE</th>
                <th>SUCURSALES AFECTADAS</th>
                <th>CREADO EL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $holiday)
                <tr>
                    <td>{{ $holiday->fecha->format('d/m/Y') }}</td>
                    <td>{{ $holiday->nombre }}</td>
                    <td>
                        @if($holiday->branch->isNotEmpty())
                            <div class="branches">
                                {{ $holiday->branch->pluck('nombre')->implode(', ') }}
                            </div>
                        @else
                            <em>Todas las sucursales</em>
                        @endif
                    </td>
                    <td>{{ $holiday->created_at->format('d/m/Y H:i') }}</td>
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