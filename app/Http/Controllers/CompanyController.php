<?php
namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function create(): View
    {
        return view('companies.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'unique:companies,code'],
            'tenant_database' => ['required', 'unique:companies,tenant_database'],
        ]);

        // Create company in transaction
        $company = DB::connection('central')->transaction(function () use ($validated) {
            $company = Company::create($validated);

            // Create tenant database if it doesn't exist
            $this->createTenantDatabase($company->tenant_database);

            return $company;
        });

        // Run migrations outside transaction
        $this->runTenantMigrations($company->tenant_database);

        // Seed admin user automatically
        $this->seedAdminUser($company->tenant_database);

        return redirect()->route('dashboard')
            ->with('status', 'Société créée avec succès.');
    }

    private function createTenantDatabase(string $databaseName): void
    {
        DB::statement("CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }

    private function runTenantMigrations(string $databaseName): void
    {
        try {
            // Temporarily set the tenant database
            $originalDatabase = Config::get('database.connections.tenant.database');
            
            Config::set('database.connections.tenant.database', $databaseName);
            
            // Clear database manager cache to ensure new connection is used
            DB::purge('tenant');
            DB::reconnect('tenant');

            // Run tenant migrations with force
            $exitCode = Artisan::call('migrate', [
                '--database' => 'tenant',
                '--path' => 'database/migrations/tenant',
                '--force' => true,
            ]);

            if ($exitCode !== 0) {
                Log::error("Migration failed for database {$databaseName}. Exit code: {$exitCode}");
                Log::error(Artisan::output());
            }

            // Restore original database
            Config::set('database.connections.tenant.database', $originalDatabase);
            DB::purge('tenant');
            DB::reconnect('tenant');
        } catch (\Exception $e) {
            Log::error("Migration error for database {$databaseName}: " . $e->getMessage());
            throw $e;
        }
    }

    private function seedAdminUser(string $databaseName): void
    {
        try {
            // Temporarily set the tenant database
            $originalDatabase = Config::get('database.connections.tenant.database');
            $originalDefault = Config::get('database.default');
            
            Config::set('database.connections.tenant.database', $databaseName);
            Config::set('database.default', 'tenant');

            // Clear database manager cache
            DB::purge('tenant');
            DB::reconnect('tenant');

            // Check if admin user already exists
            $existingUser = User::where('email', 'admin@test.com')->first();
            
            if (!$existingUser) {
                // Create admin user
                User::create([
                    'name' => 'Admin',
                    'email' => 'admin@test.com',
                    'password' => Hash::make('admin123'),
                ]);
            }

            // Restore original settings
            Config::set('database.connections.tenant.database', $originalDatabase);
            Config::set('database.default', $originalDefault);
            DB::purge('tenant');
            DB::reconnect('tenant');
        } catch (\Exception $e) {
            Log::error("User seeding error for database {$databaseName}: " . $e->getMessage());
            throw $e;
        }
    }
}