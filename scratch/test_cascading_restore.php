<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Paiement;
use App\Models\Quittance;

echo "Testing cascading restore functionality...\n";

// Find a payment with a quittance
$payment = Paiement::with('quittance')->first();
if (!$payment || !$payment->quittance) {
    echo "No payment with quittance found to test\n";
    exit;
}

echo "Found payment ID: {$payment->id} with quittance: {$payment->quittance->numero}\n";

// Soft delete both
echo "Soft deleting payment and quittance...\n";
$payment->delete(); // This should soft delete the payment
$payment->quittance->delete(); // And the quittance

// Verify they're trashed
$trashedPayment = Paiement::withTrashed()->find($payment->id);
$trashedQuittance = Quittance::withTrashed()->find($payment->quittance->id);

echo "Payment trashed: " . ($trashedPayment && $trashedPayment->trashed() ? 'YES' : 'NO') . "\n";
echo "Quittance trashed: " . ($trashedQuittance && $trashedQuittance->trashed() ? 'YES' : 'NO') . "\n";

// Now restore the payment (simulating what TrashController does)
echo "Restoring payment...\n";
$trashedPayment->restore();

// Check if quittance relationship finds the trashed quittance
echo "Checking quittance relationship after payment restore...\n";
$quittanceViaRelationship = $trashedPayment->quittance()->withTrashed()->first();
echo "Quittance found via relationship: " . ($quittanceViaRelationship ? 'YES' : 'NO') . "\n";

if ($quittanceViaRelationship) {
    echo "Restoring quittance via relationship...\n";
    $quittanceViaRelationship->restore();
    echo "Quittance restored successfully!\n";
} else {
    echo "Quittance not found via relationship\n";
}

// Check if quittance was also restored
$restoredQuittance = Quittance::withTrashed()->find($payment->quittance->id);
echo "Quittance restored: " . ($restoredQuittance && !$restoredQuittance->trashed() ? 'YES' : 'NO') . "\n";

// Clean up - restore everything
if ($restoredQuittance && $restoredQuittance->trashed()) {
    $restoredQuittance->restore();
}

echo "Test completed!\n";