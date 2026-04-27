<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\PaiementService;
use App\Models\TitreRecette;

try {
    $service = new PaiementService();
    $titre = TitreRecette::first();
    if ($titre) {
        echo 'Found titre_recette: ' . $titre->numero . PHP_EOL;
        $data = [
            'titre_recette_id' => $titre->id,
            'montant' => 100.00,
            'date_paiement' => now()->format('Y-m-d'),
            'type_paiement' => 'ESPECES',
            'statut' => 'PAYE'
        ];
        echo 'Creating payment...' . PHP_EOL;
        $payment = $service->create($data);
        echo 'Payment created with ID: ' . $payment->id . PHP_EOL;
        echo 'Has quittance: ' . ($payment->quittance ? 'YES' : 'NO') . PHP_EOL;
        if ($payment->quittance) {
            echo 'Quittance numero: ' . $payment->quittance->numero . PHP_EOL;
        }
    } else {
        echo 'No titre_recette found' . PHP_EOL;
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
    echo 'File: ' . $e->getFile() . ':' . $e->getLine() . PHP_EOL;
}