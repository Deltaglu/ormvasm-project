<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$companies = DB::table('companies')->get();

foreach ($companies as $company) {
    $dbName = $company->tenant_database;
    echo "Migrating $dbName...\n";

    try {
        config(['database.connections.tenant.database' => $dbName]);
        DB::purge('tenant');
        DB::reconnect('tenant');

        if (!Schema::connection('tenant')->hasTable('activity_logs')) {
            Schema::connection('tenant')->create('activity_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('action');
                $table->string('subject_type');
                $table->unsignedBigInteger('subject_id');
                $table->text('description');
                $table->json('properties')->nullable();
                $table->string('ip_address')->nullable();
                $table->timestamps();
                $table->index(['subject_type', 'subject_id']);
            });
            echo "Success: Table created in $dbName\n";
        } else {
            echo "Skipped: Table already exists in $dbName\n";
        }
    } catch (\Exception $e) {
        echo "Error in $dbName: " . $e->getMessage() . "\n";
    }
}
