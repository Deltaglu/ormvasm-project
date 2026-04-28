<?php

namespace App\Exports;

use App\Models\Agriculteur;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class AgriculteursExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithEvents
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

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Insert title row at the top
                $sheet->insertNewRowBefore(1, 4);
                
                // ORMVASM Title
                $sheet->setCellValue('A1', 'ORMVASM');
                $sheet->mergeCells('A1:H1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '1a3c6e']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                
                // Document Title
                $sheet->setCellValue('A2', 'LISTE DES AGRICULTEURS');
                $sheet->mergeCells('A2:H2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '333333']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                
                // Export Date
                $sheet->setCellValue('A3', 'Date d\'export: ' . now()->format('d/m/Y H:i'));
                $sheet->mergeCells('A3:H3');
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => ['size' => 10, 'color' => ['rgb' => '666666']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                
                // President & Treasurer Info
                $sheet->setCellValue('A4', 'Président: ___________________');
                $sheet->setCellValue('E4', 'Trésorier: ___________________');
                $sheet->getStyle('A4:E4')->applyFromArray([
                    'font' => ['size' => 10, 'color' => ['rgb' => '666666']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                
                // Adjust row heights
                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->getRowDimension(2)->setRowHeight(20);
                $sheet->getRowDimension(3)->setRowHeight(15);
                $sheet->getRowDimension(4)->setRowHeight(15);
                
                // Style header row (now at row 5)
                $sheet->getStyle('A5:H5')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1a3c6e']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
            },
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        // Find all society rows and style them (data starts at row 6 now)
        $highestRow = $sheet->getHighestRow();
        for ($row = 6; $row <= $highestRow; $row++) {
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
