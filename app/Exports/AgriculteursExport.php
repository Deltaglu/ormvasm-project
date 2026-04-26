<?php

namespace App\Exports;

use App\Models\Agriculteur;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AgriculteursExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Agriculteur::all()->map(function ($agriculteur) {
            return [
                $agriculteur->id,
                $agriculteur->nom,
                $agriculteur->prenom,
                $agriculteur->cin,
                $agriculteur->telephone,
                $agriculteur->email,
                $agriculteur->adresse,
                $agriculteur->created_at?->format('d/m/Y H:i'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nom',
            'Prénom',
            'CIN',
            'Téléphone',
            'Email',
            'Adresse',
            'Date de création',
        ];
    }
}
