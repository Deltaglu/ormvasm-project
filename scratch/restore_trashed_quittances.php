<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Quittance;

$trashedQuittances = Quittance::onlyTrashed()->get();

echo "Found " . $trashedQuittances->count() . " trashed quittances\n";

foreach ($trashedQuittances as $quittance) {
    echo "Restoring quittance: {$quittance->numero} for payment {$quittance->paiement_id}\n";
    $quittance->restore();
}

echo "Done! All trashed quittances have been restored.\n";