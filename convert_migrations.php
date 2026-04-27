<?php
$files = glob('database/migrations/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // 1. Remove Schema::connection('tenant')
    $content = str_replace("Schema::connection('tenant')->", "Schema::", $content);
    
    // 2. Add company_id to core tables
    $tablesToUpdate = ['agriculteurs', 'prestations', 'titres_recettes', 'paiements', 'quittances', 'users', 'settings'];
    foreach ($tablesToUpdate as $table) {
        if (strpos($content, "Schema::create('$table'") !== false) {
            // Add company_id after id()
            $content = str_replace("\$table->id();", "\$table->id();\n            \$table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade');", $content);
        }
    }
    
    file_put_contents($file, $content);
    echo "Updated $file\n";
}
