<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

class MigrateTenantDatabase extends Command
{
    protected $signature = 'migrate:tenant {database}';
    protected $description = 'Run migrations on a specific tenant database';

    public function handle(): int
    {
        $databaseName = $this->argument('database');
        
        $this->info("Migrating tenant database: {$databaseName}");

        // Temporarily set the tenant database
        $originalDatabase = Config::get('database.connections.tenant.database');
        
        Config::set('database.connections.tenant.database', $databaseName);

        // Run tenant migrations
        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant',
            '--force' => true,
        ]);

        $this->info(Artisan::output());

        // Restore original database
        Config::set('database.connections.tenant.database', $originalDatabase);

        $this->info("Migrations completed for database: {$databaseName}");

        return Command::SUCCESS;
    }
}