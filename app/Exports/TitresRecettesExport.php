<?php

namespace App\Exports;

use App\Models\TitreRecette;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TitresRecettesExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
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
    
    private function getClientName($agriculteur): string
    {
        if (!$agriculteur) return 'N/A';
        return $agriculteur->type === 'society' 
            ? $agriculteur->nom 
            : trim(($agriculteur->prenom ?? '') . ' ' . $agriculteur->nom);
    }
    
    public function styles(Worksheet $sheet)
    {
        // Header styling
        $sheet->getStyle('A1:M1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1a3c6e']],
        ]);
        
        // Find all rows and apply formatting
        $highestRow = $sheet->getHighestRow();
        for ($row = 2; $row <= $highestRow; $row++) {
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
