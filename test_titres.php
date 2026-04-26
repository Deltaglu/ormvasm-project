<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Manually connect the tenant DB!
(new \App\Services\TenantConnectionManager())->connect('company1_db');

$request = Illuminate\Http\Request::create('/titres-recettes', 'GET');

$user = \App\Models\User::first();
if ($user) {
    auth()->login($user);
}

$request->setLaravelSession($app->make('session')->driver());
$request->session()->put('company_code', 'soc1');

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request);

echo "Status: " . $response->getStatusCode() . "\n";
$content = $response->getContent();
echo "Length: " . strlen($content) . "\n";
file_put_contents('test_output.html', $content);
