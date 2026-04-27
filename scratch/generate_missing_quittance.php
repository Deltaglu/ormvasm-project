<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Paiement;
use App\Models\Quittance;
use App\Services\PaiementService;

$paymentWithoutQuittance = Paiement::whereDoesntHave('quittance')->first();

if ($paymentWithoutQuittance) {
    echo "Found payment ID {$paymentWithoutQuittance->id} without quittance\n";
    echo "Generating quittance...\n";

    // Generate next quittance number
    $prefix = 'QUIT-'.now()->format('Y').'-';
    $lastNumero = Quittance::withTrashed()
        ->where('numero', 'like', $prefix.'%')
        ->orderByDesc('numero')
        ->value('numero');

    $next = $lastNumero ? ((int) substr($lastNumero, strlen($prefix))) + 1 : 1;
    $numero = $prefix.str_pad((string) $next, 5, '0', STR_PAD_LEFT);

    // Create quittance
    $quittance = Quittance::create([
        'numero' => $numero,
        'date_generation' => now(),
        'chemin_pdf' => '',
        'montant_paye' => $paymentWithoutQuittance->montant,
        'paiement_id' => $paymentWithoutQuittance->id,
    ]);

    // Generate PDF
    $service = new PaiementService();
    $service->regenerateQuittance($paymentWithoutQuittance->load('titreRecette.agriculteur'), $quittance);

    echo "Created quittance: {$quittance->numero} for payment {$paymentWithoutQuittance->id}\n";
} else {
    echo "All payments have quittances\n";
}