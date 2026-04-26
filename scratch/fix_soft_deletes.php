<?php

require realpath(__DIR__ . '/../vendor/autoload.php');
$app = require_once realpath(__DIR__ . '/../bootstrap/app.php');
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Company;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

$companies = Company::all();

foreach ($companies as $company) {
    echo "Processing database: {$company->tenant_database}\n";
    
    config(['database.connections.tenant.database' => $company->tenant_database]);
    DB::purge('tenant');
    DB::reconnect('tenant');

    $tables = ['agriculteurs', 'prestations', 'paiements', 'titres_recettes'];

    foreach ($tables as $tableName) {
        if (Schema::connection('tenant')->hasTable($tableName)) {
            Schema::connection('tenant')->table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::connection('tenant')->hasColumn($tableName, 'deleted_at')) {
                    $table->softDeletes();
                    echo "  - Added softDeletes to $tableName\n";
                } else {
                    echo "  - softDeletes already exists on $tableName\n";
                }
            });
        }
    }
}

echo "Done!\n";
