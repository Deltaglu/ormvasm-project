<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Paiement;

$payments = Paiement::all();
echo "Existing payments:\n";
foreach ($payments as $payment) {
    echo 'Payment ID: ' . $payment->id . ', Date: ' . $payment->date_paiement . ', Amount: ' . $payment->montant . PHP_EOL;
}