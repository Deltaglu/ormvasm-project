<?php

namespace App\Exports;

use App\Models\Quittance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class QuittancesExport implements FromCollection, WithHeadings
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
                $quittance->paiement?->titreRecette?->agriculteur?->prenom . ' ' . $quittance->paiement?->titreRecette?->agriculteur?->nom,
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
            'Agriculteur',
            'Montant (DH)',
            'Date de création',
        ];
    }
}
