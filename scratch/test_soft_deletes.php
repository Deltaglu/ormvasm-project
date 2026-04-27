<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Quittance;

echo "Testing soft deletes on Quittance model...\n";

$q = Quittance::first();
if ($q) {
    echo "Found quittance: {$q->numero}\n";

    // Test soft delete
    $q->delete();
    echo "Soft deleted\n";

    // Test withTrashed
    $trashed = Quittance::withTrashed()->find($q->id);
    if ($trashed) {
        echo "Can find with withTrashed()\n";

        // Test restore
        $trashed->restore();
        echo "Restored successfully\n";

        // Verify it's back
        $restored = Quittance::find($q->id);
        if ($restored) {
            echo "Quittance is back in normal query\n";
        } else {
            echo "ERROR: Quittance not found after restore\n";
        }
    } else {
        echo "ERROR: Cannot find with withTrashed()\n";
    }
} else {
    echo "No quittances found to test\n";
}

echo "Test completed!\n";