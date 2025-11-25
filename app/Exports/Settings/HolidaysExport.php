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

class HolidaysExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
{
    protected $data;
    protected $filters;

    public function __construct(Collection $data, array $filters = [])
    {
        $this->data = $data;
        $this->filters = $filters;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            ['REPORTE DE FERIADOS'],
            ['Generado el: ' . now()->format('d/m/Y H:i:s')],
            ['Filtros: ' . ($this->filters['date_range'] === 'between' ? 'Desde ' . $this->filters['start_date'] . ' hasta ' . $this->filters['end_date'] : 'Todos los registros')],
            [],
            ['ID', 'FECHA', 'NOMBRE', 'TIPO', 'SUCURSALES AFECTADAS', 'CREADO EL']
        ];
    }

    public function map($holiday): array
    {
        return [
            $holiday->id,
            $holiday->date->format('d/m/Y'),
            $holiday->name,
            $holiday->type,
            $holiday->branches->pluck('nombre')->implode(', ') ?: 'TODAS',
            $holiday->created_at->format('d/m/Y H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            2 => ['font' => ['italic' => true, 'size' => 11]],
            3 => ['font' => ['italic' => true, 'size' => 11]],
            5 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 
                  'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'DC2626']]],
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 10, 'B' => 15, 'C' => 30, 'D' => 15, 'E' => 50, 'F' => 20];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getDelegate()->mergeCells('A1:F1');
                $event->sheet->getDelegate()->mergeCells('A2:F2');
                $event->sheet->getDelegate()->mergeCells('A3:F3');
                $event->sheet->getDelegate()->getStyle('E')->getAlignment()->setWrapText(true);
            },
        ];
    }
}