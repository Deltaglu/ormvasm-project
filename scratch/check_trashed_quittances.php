<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Quittance;

$allQuittances = Quittance::withTrashed()->count();
$activeQuittances = Quittance::count();
$trashedQuittances = Quittance::onlyTrashed()->count();

echo "Total quittances (with trashed): $allQuittances\n";
echo "Active quittances: $activeQuittances\n";
echo "Trashed quittances: $trashedQuittances\n";

if ($trashedQuittances > 0) {
    $trashed = Quittance::onlyTrashed()->get();
    foreach ($trashed as $q) {
        echo "Trashed quittance: {$q->numero} for payment {$q->paiement_id}\n";
    }
}