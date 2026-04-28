<?php

namespace App\Exports;

use App\Models\Agriculteur;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AgriculteursExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $rows = collect();
        
        // Get only top-level clients (societies and individuals without parents)
        $clients = Agriculteur::with('children')
            ->whereNull('parent_id')
            ->orderBy('type', 'desc') // Societies first
            ->orderBy('nom')
            ->get();
        
        foreach ($clients as $client) {
            if ($client->type === 'society') {
                // Add society as header row
                $rows->push([
                    'TYPE' => 'SOCIÉTÉ',
                    'NOM' => $client->nom,
                    'PRÉNOM' => '',
                    'CIN' => $client->cin ?? '—',
                    'TÉLÉPHONE' => $client->telephone ?? '—',
                    'EMAIL' => $client->email ?? '—',
                    'ADRESSE' => $client->adresse ?? '—',
                    'MEMBRES' => $client->children->count(),
                ]);
                
                // Add members indented
                foreach ($client->children as $member) {
                    $rows->push([
                        'TYPE' => '  → Membre',
                        'NOM' => $member->nom,
                        'PRÉNOM' => $member->prenom ?? '—',
                        'CIN' => $member->cin ?? '—',
                        'TÉLÉPHONE' => $member->telephone ?? '—',
                        'EMAIL' => $member->email ?? '—',
                        'ADRESSE' => $member->adresse ?? '—',
                        'MEMBRES' => '',
                    ]);
                }
                
                // Add empty row after society block
                $rows->push([
                    'TYPE' => '',
                    'NOM' => '',
                    'PRÉNOM' => '',
                    'CIN' => '',
                    'TÉLÉPHONE' => '',
                    'EMAIL' => '',
                    'ADRESSE' => '',
                    'MEMBRES' => '',
                ]);
            } else {
                // Individual client
                $rows->push([
                    'TYPE' => 'PARTICULIER',
                    'NOM' => $client->nom,
                    'PRÉNOM' => $client->prenom ?? '—',
                    'CIN' => $client->cin ?? '—',
                    'TÉLÉPHONE' => $client->telephone ?? '—',
                    'EMAIL' => $client->email ?? '—',
                    'ADRESSE' => $client->adresse ?? '—',
                    'MEMBRES' => '',
                ]);
            }
        }
        
        return $rows;
    }

    public function headings(): array
    {
        return [
            'TYPE',
            'NOM',
            'PRÉNOM',
            'CIN',
            'TÉLÉPHONE',
            'EMAIL',
            'ADRESSE',
            'NB MEMBRES',
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        // Header styling
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1a3c6e']],
        ]);
        
        // Find all society rows and style them
        $highestRow = $sheet->getHighestRow();
        for ($row = 2; $row <= $highestRow; $row++) {
            $typeCell = $sheet->getCell('A' . $row)->getValue();
            if ($typeCell === 'SOCIÉTÉ') {
                $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E8F4FD']],
                ]);
            } elseif ($typeCell === '  → Membre') {
                $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray([
                    'font' => ['italic' => true],
                ]);
            } elseif ($typeCell === 'PARTICULIER') {
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
            'A' => 15,
            'B' => 20,
            'C' => 15,
            'D' => 15,
            'E' => 15,
            'F' => 25,
            'G' => 30,
            'H' => 12,
        ];
    }
}
