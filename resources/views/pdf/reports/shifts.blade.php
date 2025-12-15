<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Turnos</title>
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
            font-size: 9px;
            line-height: 1.3;
        }

        @page {
            size: A4 landscape;
            margin: 12mm 8mm 8mm 8mm;
        }

        .header {
            border-bottom: 2px solid #8B5CF6;
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
            background: #8B5CF6;
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

        .schedules, .assigned {
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
        <h1>Reporte de Turnos</h1>
        <p>Generado el: {{ $generated_at->format('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>NOMBRE</th>
                <th>JORNADA</th>
                <th>SEMANAL</th>
                <th>DESDE</th>
                <th>HASTA</th>
                <th>HORARIOS</th>
                <th>ASIGNADO A</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $shift)
            <tr>
                <td>{{ $shift->nombre }}</td>
                <td>{{ $shift->jornada }} hrs</td>
                <td>{{ $shift->semanal }} hrs</td>
                <td>{{ $shift->desde }}</td>
                <td>{{ $shift->hasta }}</td>
                <td>
                    @if($shift->schedules->isNotEmpty())
                        <div class="schedules">
                            @foreach($shift->schedules as $schedule)
                                {{ $schedule->hora_inicio }} - {{ $schedule->hora_fin }} 
                                ({{ implode(', ', $schedule->dias) }})
                                @if($schedule->incluye_feriados) • Incluye feriados @endif
                                <br>
                            @endforeach
                        </div>
                    @else
                        <em>Sin horarios</em>
                    @endif
                </td>
                <td>
                    @php
                        $assigned = collect();
                        if ($shift->users->isNotEmpty()) $assigned->push('Usuarios');
                        if ($shift->branches->isNotEmpty()) $assigned->push('Sucursales');
                        if ($shift->groups->isNotEmpty()) $assigned->push('Grupos');
                        if ($shift->sections->isNotEmpty()) $assigned->push('Secciones');
                        if ($shift->roles->isNotEmpty()) $assigned->push('Roles');
                    @endphp
                    <div class="assigned">
                        {{ $assigned->implode(', ') ?: 'NADIE' }}
                    </div>
                </td>
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