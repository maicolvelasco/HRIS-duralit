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

class UsersExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
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
            ['REPORTE DE PERSONAS'],
            ['Generado el: ' . now()->format('d/m/Y H:i:s')],
            ['Filtros: ' . ($this->filters['date_range'] === 'between' ? 'Desde ' . $this->filters['start_date'] . ' hasta ' . $this->filters['end_date'] : 'Todos los registros')],
            [],
            ['ID', 'NOMBRE', 'APELLIDO', 'CÓDIGO', 'ESTADO', 'SUCURSAL', 'GRUPO', 'SECCIÓN', 'ROL', 'FECHA CREACIÓN']
        ];
    }

    public function map($user): array
    {
        return [
            $user->id,
            $user->nombre,
            $user->apellido,
            $user->codigo,
            $user->is_active ? 'ACTIVO' : 'INACTIVO',
            $user->branch->nombre ?? 'N/A',
            $user->group->nombre ?? 'N/A',
            $user->section->nombre ?? 'N/A',
            $user->rol->nombre ?? 'SIN ROL',
            $user->created_at->format('d/m/Y H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            2 => ['font' => ['italic' => true, 'size' => 11]],
            3 => ['font' => ['italic' => true, 'size' => 11]],
            5 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 
                  'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2563EB']]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10, 'B' => 20, 'C' => 20, 'D' => 15, 'E' => 12,
            'F' => 20, 'G' => 20, 'H' => 20, 'I' => 20, 'J' => 20
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getDelegate()->mergeCells('A1:J1');
                $event->sheet->getDelegate()->mergeCells('A2:J2');
                $event->sheet->getDelegate()->mergeCells('A3:J3');
            },
        ];
    }
}