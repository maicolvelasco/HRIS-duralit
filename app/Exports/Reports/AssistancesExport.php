<?php

namespace App\Exports\Reports;

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

class AssistancesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents, WithTitle, ShouldAutoSize
{
    protected Collection $data;
    protected array $filters;

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
        return 'Asistencias';
    }

    public function headings(): array
    {
        return [
            ['REPORTE DE ASISTENCIAS'],
            ['Generado el: ' . now()->format('d/m/Y H:i')],
            ['Empresa: ' . config('app.name')],
            [],
            ['USUARIO', 'FECHA ENTRADA', 'HORA ENTRADA', 'FECHA SALIDA', 'HORA SALIDA', 'ESTADO', 'RETRASO']
        ];
    }

    public function map($ass): array
    {
        return [
            $ass->user->nombre . ' ' . $ass->user->apellido,
            $ass->fecha_entrada,
            $ass->hora_entrada,
            $ass->fecha_salida,
            $ass->hora_salida,
            $ass->entrada && $ass->salida ? 'COMPLETO' : ($ass->entrada ? 'ENTRADA' : 'PENDIENTE'),
            $ass->affirmation?->retraso ? 'SÍ' : 'NO',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $this->data->count() + 6;

        $sheet->mergeCells('A1:G1');
        $sheet->mergeCells('A2:G2');
        $sheet->mergeCells('A3:G3');
        $sheet->getStyle('A1:G3')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F2937']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->getStyle('A5:G5')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '059669']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
        ]);

        $sheet->getStyle("A6:G{$lastRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);

        $sheet->getRowDimension(5)->setRowHeight(25);
        $sheet->setAutoFilter('A5:G5');
    }

    public function columnWidths(): array
    {
        return ['A' => 30, 'B' => 15, 'C' => 12, 'D' => 15, 'E' => 12, 'F' => 12, 'G' => 10];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Logo opcional
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