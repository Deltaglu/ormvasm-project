<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

if (Schema::hasTable('quittances')) {
    if (!Schema::hasColumn('quittances', 'deleted_at')) {
        Schema::table('quittances', function (Blueprint $table) {
            $table->softDeletes();
        });
        echo "Added softDeletes to quittances table\n";
    } else {
        echo "deleted_at column already exists\n";
    }
} else {
    echo "quittances table does not exist\n";
}