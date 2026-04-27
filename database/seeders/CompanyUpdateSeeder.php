<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;

class CompanyUpdateSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        if ($company) {
            $company->tenant_database = 'ormsa';
            $company->save();
            $this->command->info('Updated company tenant_database to: ormsa');
        } else {
            $this->command->warn('No company found');
        }
    }
}
