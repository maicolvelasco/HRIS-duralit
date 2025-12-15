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
    ShouldAutoSize,
    WithMultipleSheets
};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class UsersExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents, WithTitle, ShouldAutoSize
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

    public function title(): string
    {
        return 'Reporte de Personas';
    }

    public function headings(): array
    {
        return [
            ['REPORTE DE PERSONAS'],
            ['Generado el: ' . now()->format('d/m/Y H:i')],
            [],
            [],
            [],
            ['NOMBRE', 'APELLIDO', 'CÓDIGO', 'ESTADO', 'SUCURSAL', 'GRUPO', 'SECCIÓN', 'ROL', 'FECHA CREACIÓN']
        ];
    }

    public function map($user): array
    {
        return [
            $user->nombre,
            $user->apellido,
            $user->codigo,
            $user->is_active ? 'ACTIVO' : 'INACTIVO',
            $user->branch->nombre ?? 'N/A',
            $user->group->nombre ?? 'N/A',
            $user->section->nombre ?? 'N/A',
            $user->rol->nombre ?? 'SIN ROL',
            $user->created_at->format('d/m/Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $this->data->count() + 6;

        // Estilos generales
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F2937']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->mergeCells('A1:I1');
        $sheet->mergeCells('A2:I2');
        $sheet->mergeCells('A3:I3');
        $sheet->mergeCells('A4:I4');

        // Encabezado de tabla
        $sheet->getStyle('A6:I6')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '374151']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Cuerpo de tabla
        $sheet->getStyle("A7:I{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // Filtros activos
        $sheet->setAutoFilter('A6:I6');

        // Estado con color
        foreach ($this->data as $index => $user) {
            $row = $index + 7;
            $status = $user->is_active ? 'ACTIVO' : 'INACTIVO';
            $color = $user->is_active ? 'D1FAE5' : 'FEE2E2';
            $sheet->getStyle("D{$row}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $color]],
                'font' => ['bold' => true],
            ]);
        }

        // Altura de fila
        $sheet->getRowDimension(6)->setRowHeight(25);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 20,
            'C' => 15,
            'D' => 12,
            'E' => 20,
            'F' => 20,
            'G' => 20,
            'H' => 20,
            'I' => 20,
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

    private function getFilterDescription(): string
    {
        if (empty($this->filters)) return 'Ninguno';

        $parts = [];

        if (!empty($this->filters['start_date']) && !empty($this->filters['end_date'])) {
            $parts[] = "Desde {$this->filters['start_date']} hasta {$this->filters['end_date']}";
        }

        if (!empty($this->filters['branch'])) {
            $parts[] = "Sucursal: {$this->filters['branch']}";
        }

        if (!empty($this->filters['role'])) {
            $parts[] = "Rol: {$this->filters['role']}";
        }

        return implode(' | ', $parts) ?: 'Ninguno';
    }
}