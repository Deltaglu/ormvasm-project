<?php
// Simple check using direct database access for account types

$file = __DIR__ . '/../.env';
if (!file_exists($file)) {
    echo "No .env file found\n";
    exit(1);
}

// Parse .env
$env = parse_ini_file($file);

// Connect to database
$host = $env['DB_HOST'] ?? '127.0.0.1';
$user = $env['DB_USERNAME'] ?? 'root';
$pass = $env['DB_PASSWORD'] ?? '';
$db = $env['DB_DATABASE'] ?? 'ormsa';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n";
echo "════════════════════════════════════════════════════════════════════════════════\n";
echo "ACCOUNT TYPE SYSTEM - DATABASE CHECK\n";
echo "════════════════════════════════════════════════════════════════════════════════\n\n";

// Check if account_type column exists
$stmt = $pdo->query("SHOW COLUMNS FROM companies LIKE 'account_type'");
$column = $stmt->fetch(PDO::FETCH_ASSOC);

if ($column) {
    echo "✓ Column 'account_type' exists in companies table\n";
    echo "  Type: " . $column['Type'] . "\n";
    echo "  Default: " . ($column['Default'] ?? 'none') . "\n\n";
} else {
    echo "✗ Column 'account_type' NOT found in companies table\n";
    exit(1);
}

// Get all companies with their account types
$stmt = $pdo->query("
    SELECT 
        c.id, 
        c.name, 
        c.code, 
        c.account_type,
        COUNT(a.id) as agriculteur_count
    FROM companies c
    LEFT JOIN agriculteurs a ON c.id = a.company_id AND a.deleted_at IS NULL
    GROUP BY c.id, c.name, c.code, c.account_type
    ORDER BY c.name
");

$companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "COMPANIES (" . count($companies) . " total):\n";
echo str_repeat("─", 80) . "\n";

$summary = [
    'INDIVIDUEL' => 0,
    'SOCIETE' => 0,
    'issues' => [],
];

foreach ($companies as $company) {
    $summary[$company['account_type']]++;
    $type = $company['account_type'];
    $count = (int)$company['agriculteur_count'];
    
    echo "📦 {$company['name']}\n";
    echo "   Type: {$type} | Code: {$company['code']} | Agriculteurs: {$count}\n";
    
    // Check for violations
    if ($type === 'INDIVIDUEL' && $count > 1) {
        echo "   ⚠️  VIOLATION: INDIVIDUEL with {$count} agriculteurs (max: 1)\n";
        $summary['issues'][] = [
            'id' => $company['id'],
            'name' => $company['name'],
            'count' => $count,
        ];
    }
    
    if ($type === 'SOCIETE' && $count === 0) {
        echo "   ℹ️  Note: SOCIETE account has no agriculteurs yet\n";
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
    foreach ($summary['issues'] as $issue) {
        echo "  - {$issue['name']} has {$issue['count']} agriculteurs but is marked INDIVIDUEL\n";
    }
} else {
    echo "\n✓ All companies comply with account type rules!\n";
}

echo "\n" . str_repeat("═", 80) . "\n\n";

// List agriculteurs by company
echo "AGRICULTEURS BY COMPANY:\n";
echo str_repeat("─", 80) . "\n";

$stmt = $pdo->query("
    SELECT 
        c.name as company_name,
        c.account_type,
        COUNT(a.id) as count
    FROM companies c
    LEFT JOIN agriculteurs a ON c.id = a.company_id AND a.deleted_at IS NULL
    GROUP BY c.id, c.name, c.account_type
    ORDER BY c.name, a.nom, a.prenom
");

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($results as $row) {
    echo "{$row['company_name']} ({$row['account_type']}): {$row['count']} agriculteur(s)\n";
}

echo "\n";
