<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Paiement;
use App\Models\Quittance;
use App\Services\PaiementService;

echo "=== COMPREHENSIVE PAYMENT RESTORE TEST ===\n";

// 1. Start with a clean payment
$payment = Paiement::with('quittance')->whereHas('quittance')->first();
if (!$payment) {
    echo "No payment with quittance found. Creating one...\n";
    $service = new PaiementService();
    $titre = \App\Models\TitreRecette::first();
    if ($titre) {
        $testPayment = $service->create([
            'titre_recette_id' => $titre->id,
            'montant' => 50.00,
            'date_paiement' => now()->format('Y-m-d'),
            'type_paiement' => 'ESPECES',
            'statut' => 'PAYE'
        ]);
        $payment = Paiement::with('quittance')->find($testPayment->id);
    } else {
        echo "No titre_recette found to create test payment\n";
        exit;
    }
}

echo "Using payment ID: {$payment->id} with quittance: {$payment->quittance->numero}\n";

// 2. Delete the payment (this should soft delete both)
echo "\n--- DELETING PAYMENT ---\n";
$service = new PaiementService();
$service->delete($payment);

$trashedPayment = Paiement::withTrashed()->find($payment->id);
$trashedQuittance = Quittance::withTrashed()->find($payment->quittance->id);

echo "Payment trashed: " . ($trashedPayment && $trashedPayment->trashed() ? 'YES' : 'NO') . "\n";
echo "Quittance trashed: " . ($trashedQuittance && $trashedQuittance->trashed() ? 'YES' : 'NO') . "\n";

// 3. Check trash (simulate TrashController index)
echo "\n--- CHECKING TRASH ---\n";
$trashedPaiements = Paiement::onlyTrashed()->with('titreRecette')->get();
echo "Payments in trash: {$trashedPaiements->count()}\n";

// 4. Restore the payment (simulate TrashController restore)
echo "\n--- RESTORING PAYMENT ---\n";
$trashedPayment->restore();

// Check cascading restore
$quittanceAfterRestore = $trashedPayment->quittance()->withTrashed()->first();
if ($quittanceAfterRestore && $quittanceAfterRestore->trashed()) {
    echo "Quittance still trashed, restoring it...\n";
    $quittanceAfterRestore->restore();
} elseif ($quittanceAfterRestore && !$quittanceAfterRestore->trashed()) {
    echo "Quittance was automatically restored!\n";
} else {
    echo "ERROR: No quittance found after payment restore!\n";
}

// 5. Verify final state
echo "\n--- FINAL VERIFICATION ---\n";
$finalPayment = Paiement::find($payment->id);
$finalQuittance = Quittance::find($payment->quittance->id);

echo "Payment restored: " . ($finalPayment ? 'YES' : 'NO') . "\n";
echo "Quittance restored: " . ($finalQuittance ? 'YES' : 'NO') . "\n";

// 6. Check payments index (simulate PaiementController index)
echo "\n--- CHECKING PAYMENTS INDEX ---\n";
$paiements = Paiement::withTrashed()
    ->with(['titreRecette.agriculteur', 'quittance' => function($q) { $q->withTrashed(); }])
    ->orderByRaw('deleted_at IS NOT NULL')
    ->latest('date_paiement')
    ->get();

$activePaiements = $paiements->where('deleted_at', null);
echo "Total payments (including trashed): {$paiements->count()}\n";
echo "Active payments: {$activePaiements->count()}\n";

$testPayment = $activePaiements->first();
if ($testPayment && $testPayment->quittance) {
    echo "Sample active payment has quittance: YES ({$testPayment->quittance->numero})\n";
} elseif ($testPayment) {
    echo "Sample active payment has quittance: NO\n";
}

echo "\n=== TEST COMPLETED ===\n";