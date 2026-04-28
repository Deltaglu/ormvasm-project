<?php

namespace App\Exports;

use App\Models\Paiement;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class PaiementsExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithEvents
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

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->insertNewRowBefore(1, 4);
                
                $sheet->setCellValue('A1', 'ORMVASM');
                $sheet->mergeCells('A1:K1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '1a3c6e']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                
                $sheet->setCellValue('A2', 'LISTE DES PAIEMENTS');
                $sheet->mergeCells('A2:K2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '333333']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                
                $sheet->setCellValue('A3', 'Date d\'export: ' . now()->format('d/m/Y H:i'));
                $sheet->mergeCells('A3:K3');
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => ['size' => 10, 'color' => ['rgb' => '666666']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                
                $sheet->setCellValue('A4', 'Président: ___________________');
                $sheet->setCellValue('F4', 'Trésorier: ___________________');
                $sheet->getStyle('A4:F4')->applyFromArray([
                    'font' => ['size' => 10, 'color' => ['rgb' => '666666']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                
                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->getRowDimension(2)->setRowHeight(20);
                $sheet->getRowDimension(3)->setRowHeight(15);
                $sheet->getRowDimension(4)->setRowHeight(15);
                
                $sheet->getStyle('A5:K5')->applyFromArray([
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
