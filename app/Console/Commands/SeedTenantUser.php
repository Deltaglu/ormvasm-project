<?php
namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;

class SeedTenantUser extends Command
{
    protected $signature = 'tenant:seed-user {database} {email} {password}';
    protected $description = 'Seed an admin user in a specific tenant database';

    public function handle(): int
    {
        $databaseName = $this->argument('database');
        $email = $this->argument('email');
        $password = $this->argument('password');
        
        $this->info("Seeding admin user in tenant database: {$databaseName}");

        // Temporarily set the tenant database
        $originalDatabase = Config::get('database.connections.tenant.database');
        $originalDefault = Config::get('database.default');
        
        Config::set('database.connections.tenant.database', $databaseName);
        Config::set('database.default', 'tenant');

        // Check if user already exists
        $existingUser = User::where('email', $email)->first();
        
        if ($existingUser) {
            $this->warn("User with email {$email} already exists in database {$databaseName}");
            
            // Restore original settings
            Config::set('database.connections.tenant.database', $originalDatabase);
            Config::set('database.default', $originalDefault);
            
            return Command::FAILURE;
        }

        // Create admin user
        $user = User::create([
            'name' => 'Admin',
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        // Restore original settings
        Config::set('database.connections.tenant.database', $originalDatabase);
        Config::set('database.default', $originalDefault);

        $this->info("Admin user created successfully in database: {$databaseName}");
        $this->info("Email: {$email}");

        return Command::SUCCESS;
    }
}