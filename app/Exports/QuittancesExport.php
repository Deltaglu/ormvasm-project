<?php

namespace App\Exports;

use App\Models\Quittance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class QuittancesExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Quittance::with(['paiement.titreRecette.agriculteur'])->get()->map(function ($quittance) {
            return [
                $quittance->id,
                $quittance->numero,
                $quittance->paiement?->reference,
                $quittance->paiement?->date_paiement?->format('d/m/Y'),
                $quittance->paiement?->titreRecette?->numero,
                $this->getClientName($quittance->paiement?->titreRecette?->agriculteur),
                number_format((float) $quittance->paiement?->montant, 2, ',', ' '),
                $quittance->created_at?->format('d/m/Y H:i'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Numéro',
            'Référence paiement',
            'Date paiement',
            'Numéro titre',
            'Client',
            'Montant (DH)',
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
                $sheet->mergeCells('A1:H1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '1a3c6e']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                
                $sheet->setCellValue('A2', 'LISTE DES QUITTANCES');
                $sheet->mergeCells('A2:H2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '333333']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                
                $sheet->setCellValue('A3', 'Date d\'export: ' . now()->format('d/m/Y H:i'));
                $sheet->mergeCells('A3:H3');
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
                
                $sheet->getStyle('A5:H5')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1a3c6e']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
            },
        ];
    }
    
    private function getClientName($agriculteur): string
    {
        if (!$agriculteur) return 'N/A';
        return $agriculteur->type === 'society' 
            ? $agriculteur->nom 
            : trim(($agriculteur->prenom ?? '') . ' ' . $agriculteur->nom);
    }
    
    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        for ($row = 6; $row <= $highestRow; $row++) {
            // Style montant column (G) with right alignment and bold green
            $sheet->getStyle('G' . $row)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => '16a34a']],
                'alignment' => ['horizontal' => 'right'],
            ]);
            
            // Alternate row colors
            if ($row % 2 === 0) {
                $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray([
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F0FDF4']],
                ]);
            }
        }
        
        return [];
    }
    
    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 15,
            'C' => 18,
            'D' => 14,
            'E' => 14,
            'F' => 25,
            'G' => 14,
            'H' => 18,
        ];
    }
}
