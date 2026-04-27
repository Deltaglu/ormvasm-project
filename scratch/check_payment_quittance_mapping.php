<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Paiement;
use App\Models\Quittance;

$paiements = Paiement::all();
$quittances = Quittance::all();

echo "Payments and their quittances:\n";
foreach ($paiements as $paiement) {
    $quittance = $paiement->quittance;
    echo "Payment ID {$paiement->id}: " . ($quittance ? "Has quittance {$quittance->numero}" : "NO QUITTANCE") . "\n";
}

echo "\nQuittances and their payments:\n";
foreach ($quittances as $quittance) {
    $paiement = $quittance->paiement;
    echo "Quittance {$quittance->numero}: " . ($paiement ? "Belongs to payment {$paiement->id}" : "NO PAYMENT") . "\n";
}