<?php

namespace App\Exports;

use App\Models\Prestation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PrestationsExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
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
    
    public function styles(Worksheet $sheet)
    {
        // Header styling
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1a3c6e']],
        ]);
        
        // Find all rows and apply formatting
        $highestRow = $sheet->getHighestRow();
        for ($row = 2; $row <= $highestRow; $row++) {
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
