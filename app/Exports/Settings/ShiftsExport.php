<?php

namespace App\Exports\Settings;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ShiftsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
{
    protected $data;

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            ['REPORTE DE TURNOS'],
            ['Generado el: ' . now()->format('d/m/Y H:i:s')],
            [],
            ['ID', 'NOMBRE', 'JORNADA (h)', 'SEMANAL (h)', 'DESDE', 'HASTA', 'HORARIOS', 'ASIGNADO A']
        ];
    }

    public function map($shift): array
    {
        $schedules = $shift->schedules->map(function($s) {
            return "{$s->hora_inicio} - {$s->hora_fin} (" . implode(', ', $s->dias) . ")";
        })->implode(" | ");

        $assigned = collect();
        if ($shift->users->isNotEmpty()) $assigned->push('Usuarios');
        if ($shift->branches->isNotEmpty()) $assigned->push('Sucursales');
        if ($shift->groups->isNotEmpty()) $assigned->push('Grupos');
        if ($shift->sections->isNotEmpty()) $assigned->push('Secciones');
        if ($shift->roles->isNotEmpty()) $assigned->push('Roles');

        return [
            $shift->id,
            $shift->nombre,
            $shift->jornada,
            $shift->semanal,
            $shift->desde,
            $shift->hasta,
            $schedules ?: 'N/A',
            $assigned->implode(', ') ?: 'NADIE',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            2 => ['font' => ['italic' => true, 'size' => 11]],
            4 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 
                  'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '8B5CF6']]],
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 10, 'B' => 25, 'C' => 15, 'D' => 15, 'E' => 12, 'F' => 12, 'G' => 50, 'H' => 30];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getDelegate()->mergeCells('A1:H1');
                $event->sheet->getDelegate()->mergeCells('A2:H2');
                $event->sheet->getDelegate()->getStyle('G')->getAlignment()->setWrapText(true);
                $event->sheet->getDelegate()->getStyle('H')->getAlignment()->setWrapText(true);
            },
        ];
    }
}