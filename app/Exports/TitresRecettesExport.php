<?php

namespace App\Exports;

use App\Models\TitreRecette;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TitresRecettesExport implements FromCollection, WithHeadings
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
                $titre->agriculteur?->prenom . ' ' . $titre->agriculteur?->nom,
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
            'Agriculteur',
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
}
