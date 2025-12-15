<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Asistencias</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap');
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',sans-serif;background:#fff;color:#1F2937;font-size:10px;line-height:1.3}
        @page{size:A4 landscape;margin:12mm 8mm 8mm 8mm}
        .header{border-bottom:2px solid #059669;padding-bottom:6px;margin-bottom:8px}
        .header h1{font-size:16px;color:#1F2937;margin:0}
        .header p{font-size:8px;color:#6B7280;margin-top:2px}
        table{width:100%;border-collapse:collapse}
        th{background:#059669;color:#fff;padding:5px 4px;font-weight:600;font-size:8px;text-align:left}
        td{padding:4px;font-size:8px;border-bottom:0.5px solid #E5E7EB;vertical-align:top}
        tr:nth-child(even){background:#F9FAFB}
        .footer{margin-top:10px;text-align:center;font-size:7px;color:#9CA3AF}
    </style>
</head>
<body>
    <div class="header">
        <h1>📋 Reporte de Asistencias</h1>
        <p>Generado el: {{ $generated_at->format('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>USUARIO</th>
                <th>FECHA ENTRADA</th>
                <th>HORA ENTRADA</th>
                <th>FECHA SALIDA</th>
                <th>HORA SALIDA</th>
                <th>ESTADO</th>
                <th>RETRASO</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $ass)
            <tr>
                <td>{{ $ass->user->nombre }} {{ $ass->user->apellido }}</td>
                <td>{{ $ass->fecha_entrada }}</td>
                <td>{{ $ass->hora_entrada }}</td>
                <td>{{ $ass->fecha_salida }}</td>
                <td>{{ $ass->hora_salida }}</td>
                <td>{{ $ass->entrada && $ass->salida ? 'COMPLETO' : ($ass->entrada ? 'ENTRADA' : 'PENDIENTE') }}</td>
                <td>{{ $ass->affirmation?->retraso ? 'SÍ' : 'NO' }}</td>
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