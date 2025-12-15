<?php

namespace App\Exports\Settings;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithColumnWidths,
    WithEvents,
    WithTitle,
    ShouldAutoSize
};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\{
    Fill,
    Border,
    Alignment
};

class ShiftsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents, WithTitle, ShouldAutoSize
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

    public function title(): string
    {
        return 'Reporte de Turnos';
    }

    public function headings(): array
    {
        return [
            ['REPORTE DE TURNOS'],
            ['Generado el: ' . now()->format('d/m/Y H:i')],
            [],
            [],
            ['NOMBRE', 'JORNADA (h)', 'SEMANAL (h)', 'DESDE', 'HASTA', 'HORARIOS', 'ASIGNADO A']
        ];
    }

    public function map($shift): array
    {
        $schedules = $shift->schedules->map(function ($s) {
            return "{$s->hora_inicio} - {$s->hora_fin} (" . implode(', ', $s->dias) . ")";
        })->implode(" | ");

        $assigned = collect();
        if ($shift->users->isNotEmpty()) $assigned->push('Usuarios');
        if ($shift->branches->isNotEmpty()) $assigned->push('Sucursales');
        if ($shift->groups->isNotEmpty()) $assigned->push('Grupos');
        if ($shift->sections->isNotEmpty()) $assigned->push('Secciones');
        if ($shift->roles->isNotEmpty()) $assigned->push('Roles');

        return [
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
        $lastRow = $this->data->count() + 5;

        // Título principal
        $sheet->mergeCells('A1:G1');
        $sheet->mergeCells('A2:G2');
        $sheet->mergeCells('A3:G3');
        $sheet->getStyle('A1:G3')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F2937'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Encabezado de tabla
        $sheet->getStyle('A5:G5')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '374151'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Cuerpo de tabla
        $sheet->getStyle("A6:G{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        // Altura de filas
        $sheet->getRowDimension(5)->setRowHeight(25);

        // Filtros activos
        $sheet->setAutoFilter('A5:G5');
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 12,
            'C' => 12,
            'D' => 12,
            'E' => 12,
            'F' => 50,
            'G' => 30,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Logo (opcional)
                // $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                // $drawing->setName('Logo');
                // $drawing->setDescription('Logo');
                // $drawing->setPath(public_path('img/logo.png'));
                // $drawing->setCoordinates('A1');
                // $drawing->setHeight(40);
                // $drawing->setWorksheet($event->sheet->getDelegate());
            },
        ];
    }
}