<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Company;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$companies = Company::all();
foreach ($companies as $company) {
    if ($company->tenant_database) {
        echo "Checking database: {$company->tenant_database}\n";
        config(['database.connections.tenant.database' => $company->tenant_database]);
        DB::purge('tenant');
        DB::reconnect('tenant');

        if (Schema::connection('tenant')->hasTable('quittances')) {
            $hasDeletedAt = Schema::connection('tenant')->hasColumn('quittances', 'deleted_at');
            echo "  - quittances table exists, deleted_at column: " . ($hasDeletedAt ? 'YES' : 'NO') . "\n";
        } else {
            echo "  - quittances table does not exist\n";
        }
    } else {
        echo "Company {$company->name} has no tenant database\n";
    }
}
echo "Done!\n";