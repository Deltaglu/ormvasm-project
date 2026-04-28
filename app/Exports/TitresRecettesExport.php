<?php

namespace App\Exports;

use App\Models\TitreRecette;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class TitresRecettesExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return TitreRecette::with('agriculteur')->get()->map(function ($titre) {
            return [
                $titre->id,
                $titre->numero,
                $titre->date_emission?->format('d/m/Y'),
                $titre->date_echeance?->format('d/m/Y'),
                $this->getClientName($titre->agriculteur),
                number_format((float) $titre->montant_total, 2, ',', ' '),
                number_format((float) $titre->montant_penalite, 2, ',', ' '),
                number_format((float) $titre->montant_total_avec_penalite, 2, ',', ' '),
                number_format((float) $titre->montant_paye, 2, ',', ' '),
                number_format((float) $titre->solde_restant, 2, ',', ' '),
                $titre->statut,
                $titre->objet,
                $titre->created_at?->format('d/m/Y H:i'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Numéro',
            'Date émission',
            'Date échéance',
            'Client',
            'Montant total (DH)',
            'Pénalité (DH)',
            'Total avec pénalité (DH)',
            'Montant payé (DH)',
            'Solde restant (DH)',
            'Statut',
            'Objet',
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
                $sheet->mergeCells('A1:M1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '1a3c6e']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                
                $sheet->setCellValue('A2', 'LISTE DES TITRES DE RECETTE');
                $sheet->mergeCells('A2:M2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '333333']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                
                $sheet->setCellValue('A3', 'Date d\'export: ' . now()->format('d/m/Y H:i'));
                $sheet->mergeCells('A3:M3');
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => ['size' => 10, 'color' => ['rgb' => '666666']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                
                $sheet->setCellValue('A4', 'Président: ___________________');
                $sheet->setCellValue('H4', 'Trésorier: ___________________');
                $sheet->getStyle('A4:H4')->applyFromArray([
                    'font' => ['size' => 10, 'color' => ['rgb' => '666666']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                
                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->getRowDimension(2)->setRowHeight(20);
                $sheet->getRowDimension(3)->setRowHeight(15);
                $sheet->getRowDimension(4)->setRowHeight(15);
                
                $sheet->getStyle('A5:M5')->applyFromArray([
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
            // Right align all amount columns (F, G, H, I, J)
            foreach (['F', 'G', 'H', 'I', 'J'] as $col) {
                $sheet->getStyle($col . $row)->getAlignment()->setHorizontal('right');
            }
            
            // Bold for montant total
            $sheet->getStyle('F' . $row)->getFont()->setBold(true);
            
            // Red color for penalties if > 0
            $penalite = $sheet->getCell('G' . $row)->getValue();
            if ($penalite && floatval(str_replace([' ', ','], ['', '.'], $penalite)) > 0) {
                $sheet->getStyle('G' . $row)->applyFromArray([
                    'font' => ['color' => ['rgb' => 'dc2626'], 'bold' => true],
                ]);
            }
            
            // Color status column (K)
            $status = $sheet->getCell('K' . $row)->getValue();
            if ($status === 'SOLDE') {
                $sheet->getStyle('K' . $row)->applyFromArray([
                    'font' => ['color' => ['rgb' => '16a34a'], 'bold' => true],
                ]);
            } elseif ($status === 'PARTIEL') {
                $sheet->getStyle('K' . $row)->applyFromArray([
                    'font' => ['color' => ['rgb' => 'd97706'], 'bold' => true],
                ]);
            } elseif ($status === 'NON_SOLDE') {
                $sheet->getStyle('K' . $row)->applyFromArray([
                    'font' => ['color' => ['rgb' => 'dc2626'], 'bold' => true],
                ]);
            }
            
            // Green for paid montant
            $montantPaye = $sheet->getCell('I' . $row)->getValue();
            if ($montantPaye && floatval(str_replace([' ', ','], ['', '.'], $montantPaye)) > 0) {
                $sheet->getStyle('I' . $row)->applyFromArray([
                    'font' => ['color' => ['rgb' => '16a34a']],
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
            'C' => 14,
            'D' => 14,
            'E' => 25,
            'F' => 18,
            'G' => 14,
            'H' => 20,
            'I' => 16,
            'J' => 16,
            'K' => 12,
            'L' => 35,
            'M' => 18,
        ];
    }
}
