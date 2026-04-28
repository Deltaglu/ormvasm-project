<?php

namespace App\Exports;

use App\Models\Prestation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class PrestationsExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Prestation::all()->map(function ($prestation) {
            return [
                $prestation->id,
                $prestation->code,
                $prestation->libelle,
                number_format((float) $prestation->tarif, 2, ',', ' '),
                $prestation->unite,
                $prestation->description,
                $prestation->created_at?->format('d/m/Y H:i'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Code',
            'Libellé',
            'Tarif (DH)',
            'Unité',
            'Description',
            'Date de création',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->insertNewRowBefore(1, 4);
                
                $sheet->setCellValue('A1', 'ORMVASM');
                $sheet->mergeCells('A1:G1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '1a3c6e']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                
                $sheet->setCellValue('A2', 'LISTE DES PRESTATIONS');
                $sheet->mergeCells('A2:G2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '333333']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                
                $sheet->setCellValue('A3', 'Date d\'export: ' . now()->format('d/m/Y H:i'));
                $sheet->mergeCells('A3:G3');
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => ['size' => 10, 'color' => ['rgb' => '666666']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                
                $sheet->setCellValue('A4', 'Président: ___________________');
                $sheet->setCellValue('E4', 'Trésorier: ___________________');
                $sheet->getStyle('A4:E4')->applyFromArray([
                    'font' => ['size' => 10, 'color' => ['rgb' => '666666']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                
                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->getRowDimension(2)->setRowHeight(20);
                $sheet->getRowDimension(3)->setRowHeight(15);
                $sheet->getRowDimension(4)->setRowHeight(15);
                
                $sheet->getStyle('A5:G5')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1a3c6e']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
            },
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        for ($row = 6; $row <= $highestRow; $row++) {
            // Style tarif column (D) with right alignment and bold
            $sheet->getStyle('D' . $row)->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => 'right'],
            ]);
            
            // Alternate row colors
            if ($row % 2 === 0) {
                $sheet->getStyle('A' . $row . ':G' . $row)->applyFromArray([
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F8FAFC']],
                ]);
            }
        }
        
        return [];
    }
    
    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 12,
            'C' => 35,
            'D' => 14,
            'E' => 12,
            'F' => 40,
            'G' => 18,
        ];
    }
}
