<?php

namespace App\Exports;

use App\Models\Quittance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class QuittancesExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
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
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1a3c6e']],
        ]);
        
        // Find all rows and apply formatting
        $highestRow = $sheet->getHighestRow();
        for ($row = 2; $row <= $highestRow; $row++) {
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
