<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte de Turnos</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; margin: 0; padding: 0; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 40px; }
        .header { border-bottom: 3px solid #8B5CF6; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { color: #1F2937; margin: 0; font-size: 28px; }
        .header p { color: #6B7280; margin: 5px 0 0; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #8B5CF6; color: white; padding: 12px; text-align: left; font-weight: 600; }
        td { padding: 10px 12px; border-bottom: 1px solid #E5E7EB; vertical-align: top; }
        tr:nth-child(even) { background: #F9FAFB; }
        .footer { margin-top: 40px; text-align: center; color: #9CA3AF; font-size: 12px; }
        .schedules, .assigned { font-size: 11px; color: #6B7280; line-height: 1.4; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🕒 Reporte de Turnos</h1>
            <p>Generado el: {{ $generated_at->format('d/m/Y H:i:s') }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
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
                    <td>{{ $shift->id }}</td>
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
        </div>
    </div>
</body>
</html>