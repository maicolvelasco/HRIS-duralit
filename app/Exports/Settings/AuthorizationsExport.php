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

class AuthorizationsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
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
            ['REPORTE DE PERMISOS DE TRABAJO'],
            ['Generado el: ' . now()->format('d/m/Y H:i:s')],
            [],
            ['ID', 'NOMBRE', 'DESCRIPCIÓN', 'CREADO EL']
        ];
    }

    public function map($authorization): array
    {
        return [
            $authorization->id,
            $authorization->nombre,
            $authorization->descripcion ?? 'N/A',
            $authorization->created_at->format('d/m/Y H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            2 => ['font' => ['italic' => true, 'size' => 11]],
            4 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 
                  'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '6B7280']]],
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 10, 'B' => 30, 'C' => 45, 'D' => 20];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getDelegate()->mergeCells('A1:D1');
                $event->sheet->getDelegate()->mergeCells('A2:D2');
            },
        ];
    }
}