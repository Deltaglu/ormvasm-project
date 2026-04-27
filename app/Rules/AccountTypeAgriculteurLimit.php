<?php
namespace App\Rules;

use App\Models\Company;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AccountTypeAgriculteurLimit implements ValidationRule
{
    protected Company $company;

    public function __construct(Company $company)
    {
        $this->company = $company;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Check if company can add another agriculteur
        if (!$this->company->canAddAgriculteur()) {
            $errorMessage = $this->company->getValidationErrorMessage() 
                ?? 'Cannot add more agriculteurs to this account.';
            
            $fail($errorMessage);
        }
    }
}
