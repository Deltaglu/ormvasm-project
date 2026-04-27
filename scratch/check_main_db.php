<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

if (Schema::hasTable('quittances')) {
    $hasDeletedAt = Schema::hasColumn('quittances', 'deleted_at');
    echo 'quittances table exists, deleted_at column: ' . ($hasDeletedAt ? 'YES' : 'NO') . PHP_EOL;
} else {
    echo 'quittances table does not exist' . PHP_EOL;
}