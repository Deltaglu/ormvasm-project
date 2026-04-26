<?php
namespace Database\Seeders;

use App\Models\Company;
use App\Services\TenantConnectionManager;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CompanySeeder::class);

        $databaseManager = app(DatabaseManager::class);
        $tenantConnectionManager = app(TenantConnectionManager::class);

        Company::query()->each(function (Company $company) use ($databaseManager, $tenantConnectionManager) {
            $databaseManager->connection('central')
                ->statement('CREATE DATABASE IF NOT EXISTS `'.$company->tenant_database.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

            $tenantConnectionManager->connect($company->tenant_database, 'pre-fix', 'H1');

            // Tenant DB is not dropped by `migrate:fresh` on the default connection. Wipe it so
            // tenant schema always matches current migrations (avoids stale tables like users without `role`).
            Artisan::call('db:wipe', [
                '--database' => 'tenant',
                '--force' => true,
            ]);

            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--path' => 'database/migrations/tenant',
                '--force' => true,
            ]);

            $this->call(TenantAdminSeeder::class);
        });
    }
}
