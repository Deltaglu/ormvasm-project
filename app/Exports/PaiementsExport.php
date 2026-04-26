<?php

namespace App\Exports;

use App\Models\Paiement;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PaiementsExport implements FromCollection, WithHeadings
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
                $paiement->titreRecette?->agriculteur?->prenom . ' ' . $paiement->titreRecette?->agriculteur?->nom,
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
            'Agriculteur',
            'Montant (DH)',
            'Type paiement',
            'Statut',
            'N° chèque',
            'N° quittance',
            'Date de création',
        ];
    }
}
