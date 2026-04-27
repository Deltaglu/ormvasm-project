<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\AccountTypeService;
use Illuminate\Console\Command;

class UpdateAccountType extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'account:update-type
                            {--company= : Company code}
                            {--type= : Account type (INDIVIDUEL or SOCIETE)}
                            {--list : List all companies and their account types}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage company account types (INDIVIDUEL or SOCIETE)';

    private AccountTypeService $service;

    public function __construct(AccountTypeService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Show list of companies
        if ($this->option('list')) {
            return $this->listCompanies();
        }

        // Update account type
        if ($this->option('company') && $this->option('type')) {
            return $this->updateAccountType();
        }

        // Interactive mode
        return $this->interactiveMode();
    }

    private function listCompanies(): int
    {
        $this->info('Companies and Account Types:');
        $this->line(str_repeat('─', 100));

        $companies = Company::all();

        $rows = [];
        foreach ($companies as $company) {
            $stats = $this->service->getAccountStats($company);
            $rows[] = [
                $company->id,
                $company->code,
                $company->name,
                $stats['account_type'],
                $stats['agriculteur_count'],
                $stats['can_add_agriculteur'] ? 'Yes' : 'No',
            ];
        }

        $this->table(
            ['ID', 'Code', 'Name', 'Type', 'Agriculteurs', 'Can Add'],
            $rows
        );

        // Show validation results
        $issues = $this->service->validateAllCompanies();
        if (!empty($issues)) {
            $this->warn("\nValidation Issues Found:");
            foreach ($issues as $issue) {
                $this->warn(
                    "  - {$issue['company_name']}: {$issue['issue']}"
                );
            }
            return 1;
        }

        $this->info("\n✓ All companies comply with account type rules!");
        return 0;
    }

    private function updateAccountType(): int
    {
        $code = $this->option('company');
        $type = strtoupper($this->option('type'));

        // Validate type
        if (!in_array($type, ['INDIVIDUEL', 'SOCIETE'])) {
            $this->error("Invalid account type: {$type}. Must be INDIVIDUEL or SOCIETE");
            return 1;
        }

        // Find company
        $company = Company::where('code', $code)->first();
        if (!$company) {
            $this->error("Company not found with code: {$code}");
            return 1;
        }

        if ($company->account_type === $type) {
            $this->info("Company '{$company->name}' is already {$type}");
            return 0;
        }

        try {
            if ($type === 'SOCIETE') {
                $this->service->upgradeToSociete($company);
                $this->info("✓ Company upgraded to SOCIETE");
            } else {
                $this->service->downgradeToIndividuel($company);
                $this->info("✓ Company downgraded to INDIVIDUEL");
            }

            $stats = $this->service->getAccountStats($company);
            $this->line("  Type: {$stats['account_type']} | Agriculteurs: {$stats['agriculteur_count']}");

            return 0;
        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            return 1;
        }
    }

    private function interactiveMode(): int
    {
        $code = $this->ask('Enter company code');
        $company = Company::where('code', $code)->first();

        if (!$company) {
            $this->error("Company not found");
            return 1;
        }

        $stats = $this->service->getAccountStats($company);

        $this->info("Company: {$company->name}");
        $this->line("  Current Type: {$stats['account_type']}");
        $this->line("  Agriculteurs: {$stats['agriculteur_count']}");
        $this->line("");

        $newType = $this->choice(
            'Select new account type',
            ['INDIVIDUEL', 'SOCIETE'],
            array_search($company->account_type, ['INDIVIDUEL', 'SOCIETE'])
        );

        if ($newType === $company->account_type) {
            $this->info("No changes made");
            return 0;
        }

        if (!$this->confirm("Change account type to {$newType}?")) {
            return 0;
        }

        try {
            if ($newType === 'SOCIETE') {
                $this->service->upgradeToSociete($company);
                $this->info("✓ Company upgraded to SOCIETE");
            } else {
                $this->service->downgradeToIndividuel($company);
                $this->info("✓ Company downgraded to INDIVIDUEL");
            }

            return 0;
        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            return 1;
        }
    }
}
