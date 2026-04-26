<?php
namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;

class UpdateTenantUserPassword extends Command
{
    protected $signature = 'tenant:update-password {database} {email} {password}';
    protected $description = 'Update user password in a specific tenant database';

    public function handle(): int
    {
        $databaseName = $this->argument('database');
        $email = $this->argument('email');
        $password = $this->argument('password');
        
        $this->info("Updating password for user {$email} in database: {$databaseName}");

        // Temporarily set the tenant database
        $originalDatabase = Config::get('database.connections.tenant.database');
        $originalDefault = Config::get('database.default');
        
        Config::set('database.connections.tenant.database', $databaseName);
        Config::set('database.default', 'tenant');

        // Find user
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User with email {$email} not found in database {$databaseName}");
            
            // Restore original settings
            Config::set('database.connections.tenant.database', $originalDatabase);
            Config::set('database.default', $originalDefault);
            
            return Command::FAILURE;
        }

        // Update password
        $user->update([
            'password' => Hash::make($password),
        ]);

        // Restore original settings
        Config::set('database.connections.tenant.database', $originalDatabase);
        Config::set('database.default', $originalDefault);

        $this->info("Password updated successfully for user: {$email}");

        return Command::SUCCESS;
    }
}