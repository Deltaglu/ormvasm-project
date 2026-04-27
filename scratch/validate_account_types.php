<?php
require_once __DIR__ . '/../bootstrap/app.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = \Illuminate\Http\Request::capture());

use App\Models\Company;
use App\Models\Agriculteur;
use App\Services\AccountTypeService;

$service = new AccountTypeService();

echo "\n";
echo str_repeat("=", 80) . "\n";
echo "ACCOUNT TYPE SYSTEM VALIDATION\n";
echo str_repeat("=", 80) . "\n\n";

// 1. Check all companies
echo "1. CHECKING ALL COMPANIES\n";
echo str_repeat("-", 80) . "\n";

$companies = Company::all();
echo "Total companies: " . count($companies) . "\n\n";

foreach ($companies as $company) {
    echo "Company: {$company->name} (ID: {$company->id})\n";
    echo "  Account Type: {$company->account_type}\n";
    echo "  Code: {$company->code}\n";
    
    $stats = $service->getAccountStats($company);
    
    echo "  Agriculteur Count: {$stats['agriculteur_count']}\n";
    echo "  Can Add Agriculteur: " . ($stats['can_add_agriculteur'] ? 'YES' : 'NO') . "\n";
    echo "  Can Downgrade to INDIVIDUEL: " . ($stats['can_downgrade_to_individuel'] ? 'YES' : 'NO') . "\n";
    
    if ($company->isIndividuel() && $stats['agriculteur_count'] > 1) {
        echo "  ⚠️  WARNING: INDIVIDUEL account has multiple agriculteurs!\n";
    }
    
    echo "\n";
}

// 2. Validate all companies comply with rules
echo "2. VALIDATING DATA CONSISTENCY\n";
echo str_repeat("-", 80) . "\n";

$issues = $service->validateAllCompanies();

if (empty($issues)) {
    echo "✓ All companies comply with account type rules!\n";
} else {
    echo "✗ Found " . count($issues) . " issue(s):\n";
    foreach ($issues as $issue) {
        echo "  - Company: {$issue['company_name']} (ID: {$issue['company_id']})\n";
        echo "    Issue: {$issue['issue']}\n";
    }
}

echo "\n";

// 3. Test validation rules
echo "3. TESTING VALIDATION LOGIC\n";
echo str_repeat("-", 80) . "\n";

// Find or create a test company
$testCompany = Company::where('code', 'TEST_INDIVIDUEL')->first();
if (!$testCompany) {
    $testCompany = Company::create([
        'name' => 'Test Individual',
        'code' => 'TEST_INDIVIDUEL',
        'account_type' => Company::ACCOUNT_TYPE_INDIVIDUEL,
    ]);
    echo "Created test company: {$testCompany->name}\n";
} else {
    echo "Using existing test company: {$testCompany->name}\n";
}

echo "\nTest Company: {$testCompany->name} (Type: {$testCompany->account_type})\n";

// Clean up previous test agriculteurs
Agriculteur::where('company_id', $testCompany->id)->delete();

echo "\nAdding first agriculteur to INDIVIDUEL account...\n";
$validation = $service->validateAgriculteurAddition($testCompany);
if ($validation['valid']) {
    $agr1 = $service->createAgriculteur($testCompany, [
        'nom' => 'Test',
        'prenom' => 'Agriculteur',
        'cin' => 'TEST123456789',
        'email' => 'test@example.com',
    ]);
    echo "✓ Successfully added first agriculteur\n";
    echo "  ID: {$agr1->id}, Name: {$agr1->nom} {$agr1->prenom}\n";
} else {
    echo "✗ Failed: {$validation['message']}\n";
}

// Refresh company stats
$testCompany->refresh();

echo "\nAttempting to add second agriculteur to INDIVIDUEL account...\n";
$validation = $service->validateAgriculteurAddition($testCompany);
if (!$validation['valid']) {
    echo "✓ Correctly rejected: {$validation['message']}\n";
} else {
    echo "✗ ERROR: Should have rejected second agriculteur!\n";
}

// Test upgrade to SOCIETE
echo "\nUpgrading to SOCIETE account type...\n";
$service->upgradeToSociete($testCompany);
echo "✓ Upgraded to SOCIETE\n";

echo "\nAttempting to add second agriculteur to SOCIETE account...\n";
$validation = $service->validateAgriculteurAddition($testCompany);
if ($validation['valid']) {
    $agr2 = $service->createAgriculteur($testCompany, [
        'nom' => 'Test2',
        'prenom' => 'Agriculteur',
        'cin' => 'TEST223456789',
        'email' => 'test2@example.com',
    ]);
    echo "✓ Successfully added second agriculteur to SOCIETE account\n";
    echo "  ID: {$agr2->id}, Name: {$agr2->nom} {$agr2->prenom}\n";
} else {
    echo "✗ Failed: {$validation['message']}\n";
}

// Test downgrade validation
echo "\nChecking if SOCIETE account can downgrade to INDIVIDUEL...\n";
$testCompany->refresh();
$downgradeCheck = $service->canDowngradeToIndividuel($testCompany);
if (!$downgradeCheck['canDowngrade']) {
    echo "✓ Correctly blocked downgrade: {$downgradeCheck['message']}\n";
} else {
    echo "✗ ERROR: Should block downgrade with 2 agriculteurs!\n";
}

echo "\n";
echo str_repeat("=", 80) . "\n";
echo "VALIDATION COMPLETE\n";
echo str_repeat("=", 80) . "\n\n";
