<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Paiement;
use App\Models\Quittance;

echo 'Checking payments and quittances...' . PHP_EOL;
$payments = Paiement::count();
$quittances = Quittance::count();
echo "Payments: $payments" . PHP_EOL;
echo "Quittances: $quittances" . PHP_EOL;

if ($payments > 0) {
    $payment = Paiement::with('quittance')->first();
    echo 'First payment has quittance: ' . ($payment->quittance ? 'YES' : 'NO') . PHP_EOL;
    if ($payment->quittance) {
        echo 'Quittance numero: ' . $payment->quittance->numero . PHP_EOL;
        echo 'Quittance PDF path: ' . $payment->quittance->chemin_pdf . PHP_EOL;
    }
}

if ($quittances > 0) {
    $quittance = Quittance::first();
    echo 'First quittance belongs to payment: ' . ($quittance->paiement ? 'YES' : 'NO') . PHP_EOL;
}