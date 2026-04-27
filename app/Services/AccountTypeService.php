<?php
namespace App\Services;

use App\Models\Company;
use App\Models\Agriculteur;
use Illuminate\Support\Facades\DB;

class AccountTypeService
{
    /**
     * Validate if an agriculteur can be added to a company.
     *
     * @param Company $company
     * @return array{valid: bool, message: string|null}
     */
    public function validateAgriculteurAddition(Company $company): array
    {
        if (!$company->canAddAgriculteur()) {
            return [
                'valid' => false,
                'message' => $company->getValidationErrorMessage(),
            ];
        }

        return ['valid' => true, 'message' => null];
    }

    /**
     * Create an agriculteur with account type validation.
     *
     * @param Company $company
     * @param array $data
     * @return Agriculteur
     * @throws \Exception
     */
    public function createAgriculteur(Company $company, array $data): Agriculteur
    {
        $validation = $this->validateAgriculteurAddition($company);

        if (!$validation['valid']) {
            throw new \Exception($validation['message']);
        }

        $data['company_id'] = $company->id;

        return Agriculteur::create($data);
    }

    /**
     * Convert company from INDIVIDUEL to SOCIETE.
     *
     * @param Company $company
     * @return bool
     */
    public function upgradeToSociete(Company $company): bool
    {
        if ($company->isSociete()) {
            return true;
        }

        $company->update(['account_type' => Company::ACCOUNT_TYPE_SOCIETE]);

        return true;
    }

    /**
     * Check if a company can be downgraded from SOCIETE to INDIVIDUEL.
     *
     * @param Company $company
     * @return array{canDowngrade: bool, message: string|null}
     */
    public function canDowngradeToIndividuel(Company $company): array
    {
        if ($company->isIndividuel()) {
            return ['canDowngrade' => true, 'message' => null];
        }

        // Can only downgrade if company has 0 or 1 agriculteur
        $count = $company->getAgriculteurCount();
        if ($count > 1) {
            return [
                'canDowngrade' => false,
                'message' => "Cannot downgrade to INDIVIDUEL: company has {$count} agriculteurs. Maximum is 1.",
            ];
        }

        return ['canDowngrade' => true, 'message' => null];
    }

    /**
     * Downgrade company from SOCIETE to INDIVIDUEL (with validation).
     *
     * @param Company $company
     * @return bool
     * @throws \Exception
     */
    public function downgradeToIndividuel(Company $company): bool
    {
        $check = $this->canDowngradeToIndividuel($company);

        if (!$check['canDowngrade']) {
            throw new \Exception($check['message']);
        }

        $company->update(['account_type' => Company::ACCOUNT_TYPE_INDIVIDUEL]);

        return true;
    }

    /**
     * Get account type statistics for a company.
     *
     * @param Company $company
     * @return array
     */
    public function getAccountStats(Company $company): array
    {
        return [
            'account_type' => $company->account_type,
            'agriculteur_count' => $company->getAgriculteurCount(),
            'user_count' => $company->users()->count(),
            'can_add_agriculteur' => $company->canAddAgriculteur(),
            'can_downgrade_to_individuel' => $this->canDowngradeToIndividuel($company)['canDowngrade'],
        ];
    }

    /**
     * Validate all companies have proper account type constraints.
     *
     * @return array
     */
    public function validateAllCompanies(): array
    {
        $issues = [];

        foreach (Company::all() as $company) {
            if ($company->isIndividuel() && $company->getAgriculteurCount() > 1) {
                $issues[] = [
                    'company_id' => $company->id,
                    'company_name' => $company->name,
                    'issue' => "INDIVIDUEL account has {$company->getAgriculteurCount()} agriculteurs (max: 1)",
                ];
            }
        }

        return $issues;
    }
}
