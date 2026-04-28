<?php

namespace App\Exports;

use App\Models\Paiement;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PaiementsExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Paiement::with(['titreRecette.agriculteur', 'quittance'])->get()->map(function ($paiement) {
            return [
                $paiement->id,
                $paiement->reference,
                $paiement->date_paiement?->format('d/m/Y'),
                $paiement->titreRecette?->numero,
                $this->getClientName($paiement->titreRecette?->agriculteur),
                number_format((float) $paiement->montant, 2, ',', ' '),
                $paiement->type_paiement,
                $paiement->statut,
                $paiement->numero_cheque,
                $paiement->quittance?->numero,
                $paiement->created_at?->format('d/m/Y H:i'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Référence',
            'Date paiement',
            'Numéro titre',
            'Client',
            'Montant (DH)',
            'Type paiement',
            'Statut',
            'N° chèque',
            'N° quittance',
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
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1a3c6e']],
        ]);
        
        // Find all rows and apply conditional formatting
        $highestRow = $sheet->getHighestRow();
        for ($row = 2; $row <= $highestRow; $row++) {
            // Style montant column (F) with right alignment
            $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal('right');
            
            // Color status column (H)
            $status = $sheet->getCell('H' . $row)->getValue();
            if ($status === 'VALIDE') {
                $sheet->getStyle('H' . $row)->applyFromArray([
                    'font' => ['color' => ['rgb' => '16a34a'], 'bold' => true],
                ]);
            } elseif ($status === 'EN_ATTENTE') {
                $sheet->getStyle('H' . $row)->applyFromArray([
                    'font' => ['color' => ['rgb' => 'd97706'], 'bold' => true],
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
            'F' => 14,
            'G' => 14,
            'H' => 12,
            'I' => 14,
            'J' => 14,
            'K' => 18,
        ];
    }
}
