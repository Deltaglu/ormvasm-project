<?php

namespace App\Exports;

use App\Models\Prestation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PrestationsExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Prestation::all()->map(function ($prestation) {
            return [
                $prestation->id,
                $prestation->code,
                $prestation->libelle,
                number_format((float) $prestation->tarif, 2, ',', ' '),
                $prestation->unite,
                $prestation->description,
                $prestation->created_at?->format('d/m/Y H:i'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Code',
            'Libellé',
            'Tarif (DH)',
            'Unité',
            'Description',
            'Date de création',
        ];
    }
}
