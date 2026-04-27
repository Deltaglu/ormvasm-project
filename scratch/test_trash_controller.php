<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Paiement;
use App\Models\Quittance;

echo "Testing TrashController restore functionality...\n";

// Find a payment with a quittance
$payment = Paiement::with('quittance')->first();
if (!$payment || !$payment->quittance) {
    echo "No payment with quittance found to test\n";
    exit;
}

echo "Found payment ID: {$payment->id} with quittance: {$payment->quittance->numero}\n";

// Soft delete both
echo "Soft deleting payment and quittance...\n";
$payment->delete();
$payment->quittance->delete();

// Simulate TrashController restore method
echo "Simulating TrashController restore...\n";

// This simulates: $model = $this->getModelByType('paiement', $payment->id, true);
$model = Paiement::onlyTrashed()->findOrFail($payment->id);
echo "Found trashed payment: {$model->id}\n";

// This simulates: $model->restore();
$model->restore();
echo "Payment restored\n";

// This simulates the cascading restore logic
if ($model->quittance()->withTrashed()->first()) {
    $quittance = $model->quittance()->withTrashed()->first();
    echo "Found trashed quittance via relationship: {$quittance->numero}\n";
    $quittance->restore();
    echo "Quittance restored via relationship\n";
} else {
    echo "No quittance found via relationship\n";
}

// Verify final state
$finalPayment = Paiement::find($payment->id);
$finalQuittance = Quittance::find($payment->quittance->id);

echo "Final state:\n";
echo "Payment exists: " . ($finalPayment ? 'YES' : 'NO') . "\n";
echo "Quittance exists: " . ($finalQuittance ? 'YES' : 'NO') . "\n";

echo "Test completed!\n";