<?php
namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        Company::query()->updateOrCreate(
            ['code' => 'soc1'],
            [
                'name' => 'Société 1',
                'tenant_database' => 'company1_db',
            ]
        );
    }
}

