<?php
/**
 * Quick Account Type Validation Script
 * Validates the account type system without full Laravel bootstrapping
 */

// Load Laravel
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

use App\Models\Company;
use App\Models\Agriculteur;
use App\Services\AccountTypeService;

$service = new AccountTypeService();

echo "\n";
echo "════════════════════════════════════════════════════════════════════════════════\n";
echo "ACCOUNT TYPE SYSTEM - QUICK VALIDATION\n";
echo "════════════════════════════════════════════════════════════════════════════════\n\n";

// Check all companies
$companies = Company::all();
echo "COMPANIES (" . count($companies) . " total):\n";
echo str_repeat("─", 80) . "\n";

$summary = [
    'INDIVIDUEL' => 0,
    'SOCIETE' => 0,
    'issues' => [],
];

foreach ($companies as $company) {
    $count = $company->getAgriculteurCount();
    $type = $company->account_type;
    
    $summary[$type]++;
    
    echo "📦 {$company->name}\n";
    echo "   Type: {$type} | Code: {$company->code} | Agriculteurs: {$count}\n";
    
    // Check for violations
    if ($type === 'INDIVIDUEL' && $count > 1) {
        echo "   ⚠️  VIOLATION: INDIVIDUEL with {$count} agriculteurs (max: 1)\n";
        $summary['issues'][] = $company->id;
    }
    
    if ($type === 'SOCIETE' && $count === 0) {
        echo "   ℹ️  SOCIETE with no agriculteurs yet\n";
    }
    
    echo "\n";
}

echo "─" . str_repeat("─", 79) . "\n";
echo "SUMMARY:\n";
echo "  INDIVIDUEL accounts: {$summary['INDIVIDUEL']}\n";
echo "  SOCIETE accounts: {$summary['SOCIETE']}\n";
echo "  Issues found: " . count($summary['issues']) . "\n";

if (count($summary['issues']) > 0) {
    echo "\n⚠️  ISSUES TO FIX:\n";
    foreach ($summary['issues'] as $companyId) {
        $company = Company::find($companyId);
        echo "  - {$company->name} has {$company->getAgriculteurCount()} agriculteurs but is marked INDIVIDUEL\n";
    }
} else {
    echo "\n✓ All companies comply with account type rules!\n";
}

echo "\n" . str_repeat("═", 80) . "\n\n";
