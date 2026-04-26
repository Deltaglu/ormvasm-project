<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Gemini\Laravel\Facades\Gemini;

try {
    // Set the API key at runtime for the script
    config(['gemini.api_key' => env('GEMINI_API_KEY')]);
    
    $models = Gemini::models()->list();
    foreach ($models->models as $model) {
        echo $model->name . "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
