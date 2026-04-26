<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

// Use the tenant connection
$connection = DB::connection('tenant');

try {
    echo "Starting Database Optimization...\n";

    $connection->getSchemaBuilder()->table('agriculteurs', function (Blueprint $table) {
        $table->index(['nom', 'prenom'], 'idx_agri_names');
        $table->index('cin', 'idx_agri_cin');
        echo " - Indexed Agriculteurs\n";
    });

    $connection->getSchemaBuilder()->table('paiements', function (Blueprint $table) {
        $table->index('reference', 'idx_pay_ref');
        $table->index('date_paiement', 'idx_pay_date');
        echo " - Indexed Paiements\n";
    });

    $connection->getSchemaBuilder()->table('titres_recettes', function (Blueprint $table) {
        $table->index('numero', 'idx_tr_num');
        $table->index('statut', 'idx_tr_statut');
        echo " - Indexed Titres\n";
    });

    echo "Optimization Successful!\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
