<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Paiement;
use App\Models\Quittance;
use App\Services\PaiementService;

$paymentsWithoutQuittances = Paiement::whereDoesntHave('quittance')->get();

echo "Found " . $paymentsWithoutQuittances->count() . " payments without quittances\n";

foreach ($paymentsWithoutQuittances as $payment) {
    try {
        echo "Generating quittance for payment ID: {$payment->id}...\n";

        // Generate next quittance number (duplicate logic from service)
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
            'montant_paye' => $payment->montant,
            'paiement_id' => $payment->id,
        ]);

        // Generate PDF using the service
        $service = new PaiementService();
        $service->regenerateQuittance($payment->load('titreRecette.agriculteur'), $quittance);

        echo "Created quittance: {$quittance->numero}\n";
    } catch (Exception $e) {
        echo "Error creating quittance for payment {$payment->id}: {$e->getMessage()}\n";
    }
}

echo "Done!\n";