# Account Type System Documentation

## Overview

The **Account Type System** introduces explicit account classification to the unified database architecture:

- **INDIVIDUEL**: Individual account with a single agriculteur
- **SOCIETE**: Organization account managing multiple agriculteurs

This system ensures data consistency, simplifies business logic, and makes the system easier to maintain and extend.

---

## Architecture

### Database Schema

```sql
-- accounts table
CREATE TABLE companies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(255) UNIQUE NOT NULL,
    account_type ENUM('INDIVIDUEL', 'SOCIETE') DEFAULT 'INDIVIDUEL',
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Related tables (already existing)
CREATE TABLE agriculteurs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    company_id INT NOT NULL,
    nom VARCHAR(255) NOT NULL,
    prenom VARCHAR(255) NOT NULL,
    cin VARCHAR(32) UNIQUE NOT NULL,
    -- ... other fields
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);
```

---

## Validation Rules

### INDIVIDUEL Account
- **Maximum agriculteurs**: 1
- **Can add agriculteur**: Only if count < 1
- **Can downgrade**: Yes (if has ≤ 1 agriculteur)
- **Can upgrade to SOCIETE**: Yes

### SOCIETE Account
- **Maximum agriculteurs**: Unlimited
- **Can add agriculteur**: Always
- **Can downgrade**: Only if count ≤ 1
- **Can upgrade**: Already SOCIETE

---

## Implementation

### Files Added/Modified

#### 1. Database Migration
**File**: `database/migrations/2026_04_27_202001_add_account_type_to_companies_table.php`

```php
Schema::table('companies', function (Blueprint $table) {
    $table->enum('account_type', ['INDIVIDUEL', 'SOCIETE'])
          ->default('INDIVIDUEL')
          ->after('code');
});
```

#### 2. Company Model
**File**: `app/Models/Company.php`

```php
class Company extends Model
{
    public const ACCOUNT_TYPE_INDIVIDUEL = 'INDIVIDUEL';
    public const ACCOUNT_TYPE_SOCIETE = 'SOCIETE';

    protected $fillable = [
        'name',
        'code',
        'account_type',
    ];

    public function agriculteurs(): HasMany { /* ... */ }
    public function isIndividuel(): bool { /* ... */ }
    public function isSociete(): bool { /* ... */ }
    public function getAgriculteurCount(): int { /* ... */ }
    public function canAddAgriculteur(): bool { /* ... */ }
    public function getValidationErrorMessage(): ?string { /* ... */ }
}
```

#### 3. Account Type Service
**File**: `app/Services/AccountTypeService.php`

Core business logic service:

```php
class AccountTypeService
{
    // Validate if agriculteur can be added
    public function validateAgriculteurAddition(Company $company): array { /* ... */ }

    // Create agriculteur with validation
    public function createAgriculteur(Company $company, array $data): Agriculteur { /* ... */ }

    // Upgrade to SOCIETE
    public function upgradeToSociete(Company $company): bool { /* ... */ }

    // Check if can downgrade to INDIVIDUEL
    public function canDowngradeToIndividuel(Company $company): array { /* ... */ }

    // Downgrade to INDIVIDUEL (with validation)
    public function downgradeToIndividuel(Company $company): bool { /* ... */ }

    // Get account statistics
    public function getAccountStats(Company $company): array { /* ... */ }

    // Validate all companies comply with rules
    public function validateAllCompanies(): array { /* ... */ }
}
```

#### 4. Validation Rule
**File**: `app/Rules/AccountTypeAgriculteurLimit.php`

Laravel validation rule for use in controllers:

```php
class AccountTypeAgriculteurLimit implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->company->canAddAgriculteur()) {
            $fail($this->company->getValidationErrorMessage());
        }
    }
}
```

#### 5. Updated Controllers

**CompanyController** (`app/Http/Controllers/CompanyController.php`):
- Now requires `account_type` when creating companies
- Displays account type options in create view

**AgriculteurController** (`app/Http/Controllers/AgriculteurController.php`):
- Uses `AccountTypeService` for agriculteur creation
- Validates account type constraints
- Provides user-friendly error messages

#### 6. Artisan Command
**File**: `app/Console/Commands/UpdateAccountType.php`

Manage account types from command line:

```bash
# List all companies and their types
php artisan account:update-type --list

# Update account type for a company
php artisan account:update-type --company=SOC1 --type=SOCIETE

# Interactive mode
php artisan account:update-type
```

---

## Usage

### Creating a Company

#### Via Web Interface
1. Go to company creation form
2. Select account type:
   - **INDIVIDUEL**: For single farmer
   - **SOCIETE**: For organization with multiple farmers
3. Create company

#### Via API
```php
POST /companies
{
    "name": "Farm Name",
    "code": "FARM001",
    "account_type": "INDIVIDUEL"  // or "SOCIETE"
}
```

### Adding Agriculteurs

#### INDIVIDUEL Account
```php
// First agriculteur: Success ✓
Company::find(1)->account_type; // INDIVIDUEL

try {
    $agr1 = $service->createAgriculteur($company, $data);
    // Success
} catch (Exception $e) {
    // Error: "Cannot add agriculteur - account full"
}

// Second agriculteur: Error ✗
try {
    $agr2 = $service->createAgriculteur($company, $data);
    // Exception: Account full
}
```

#### SOCIETE Account
```php
// Can add unlimited agriculteurs
$agr1 = $service->createAgriculteur($company, $data1); // Success
$agr2 = $service->createAgriculteur($company, $data2); // Success
$agr3 = $service->createAgriculteur($company, $data3); // Success
// ... and more
```

### Upgrading Account Type

```bash
# Upgrade INDIVIDUEL to SOCIETE
php artisan account:update-type --company=SOC2 --type=SOCIETE
```

### Downgrading Account Type

```bash
# Only possible if ≤ 1 agriculteur
php artisan account:update-type --company=SOC2 --type=INDIVIDUEL
```

---

## Validation Checks

### Automatic Validation

The system automatically validates:

1. **Creation**: Enforce agriculteur count limits
2. **Upgrade**: Allow upgrading from INDIVIDUEL to SOCIETE (always possible)
3. **Downgrade**: Only if agriculteur count ≤ 1

### Manual Validation

```php
$service = app(AccountTypeService::class);

// Validate all companies
$issues = $service->validateAllCompanies();

if (empty($issues)) {
    echo "✓ All companies comply with rules";
} else {
    foreach ($issues as $issue) {
        echo "✗ {$issue['company_name']}: {$issue['issue']}";
    }
}
```

### Get Statistics

```php
$stats = $service->getAccountStats($company);

// Returns:
// [
//     'account_type' => 'INDIVIDUEL',
//     'agriculteur_count' => 1,
//     'user_count' => 3,
//     'can_add_agriculteur' => false,
//     'can_downgrade_to_individuel' => true,
// ]
```

---

## Diagnostic Scripts

### Quick Check
```bash
php scratch/quick_account_check.php
```

Displays:
- Column existence in database
- All companies and their types
- Agriculteur counts
- Compliance summary

### Full Validation
```bash
php scratch/validate_account_types.php
```

Tests:
- Company creation with proper types
- Agriculteur addition limits
- Account type upgrades/downgrades
- Edge cases and error handling

---

## Error Handling

### Validation Errors

```php
try {
    $service->createAgriculteur($company, $data);
} catch (Exception $e) {
    // Error messages:
    // - "An individual account can only have one agriculteur..."
    // - "Cannot downgrade to INDIVIDUEL: company has 2 agriculteurs..."
}
```

### Database Errors

Handled via Laravel transactions to ensure consistency:

```php
DB::transaction(function () {
    // All operations are atomic
    // Rolled back if any error occurs
});
```

---

## Database Consistency

### Guarantees

- ✓ INDIVIDUEL accounts cannot have > 1 agriculteur
- ✓ SOCIETE accounts can have unlimited agriculteurs
- ✓ Downgrades blocked if violating constraints
- ✓ All operations are atomic (transactions)
- ✓ Soft deletes don't affect counts

### Migration Safety

The migration:
1. Adds `account_type` column with default `INDIVIDUEL`
2. Existing companies default to INDIVIDUEL
3. No data loss
4. Reversible with `php artisan migrate:rollback`

---

## Business Logic Examples

### Scenario 1: Individual Farmer
```
Company: Ahmed's Farm
Type: INDIVIDUEL
Max Agriculteurs: 1

Current State:
  - Ahmed (1 agriculteur)
  
Result: ✗ Cannot add more
Action: Would need to upgrade to SOCIETE first
```

### Scenario 2: Agricultural Organization
```
Company: Cooperative du Sud
Type: SOCIETE
Max Agriculteurs: Unlimited

Current State:
  - Ahmed
  - Fatima
  - Mohamed
  - Zainab
  
Result: ✓ Can add more
Action: Can continue adding agriculteurs
```

### Scenario 3: Downgrade
```
Company: Former SOCIETE
Type: SOCIETE → INDIVIDUEL

Requirements:
  - Must have ≤ 1 agriculteur
  - All relationships intact
  - No data loss

Result: ✓ Possible (if count ≤ 1)
```

---

## Future Extensions

The system is designed for easy extensions:

### 1. Seat-Based Pricing
```php
protected $fillable = [
    'max_agriculteurs', // Custom seat limit
    'seat_price',
];
```

### 2. Feature Flags
```php
if ($company->isSociete()) {
    // Enable bulk operations
    // Enable team collaboration
    // Enable advanced reporting
}
```

### 3. Usage Tracking
```php
AccountTypeChange::create([
    'company_id' => $company->id,
    'from_type' => 'INDIVIDUEL',
    'to_type' => 'SOCIETE',
]);
```

---

## Testing

### Unit Tests
```php
test('individuel account can have one agriculteur', function () {
    $company = Company::factory()->create(['account_type' => 'INDIVIDUEL']);
    
    $service = app(AccountTypeService::class);
    $validation = $service->validateAgriculteurAddition($company);
    
    expect($validation['valid'])->toBeTrue();
});

test('individuel account cannot have two agriculteurs', function () {
    $company = Company::factory()->create(['account_type' => 'INDIVIDUEL']);
    Agriculteur::factory()->create(['company_id' => $company->id]);
    
    $service = app(AccountTypeService::class);
    $validation = $service->validateAgriculteurAddition($company);
    
    expect($validation['valid'])->toBeFalse();
});
```

---

## Summary

The Account Type System provides:

- ✅ **Explicit Account Classification**: Clear INDIVIDUEL vs SOCIETE distinction
- ✅ **Automatic Validation**: Enforce rules at service layer
- ✅ **User-Friendly Errors**: Clear messages about constraints
- ✅ **Flexible Management**: Easy upgrade/downgrade via Artisan
- ✅ **Data Consistency**: Atomic operations with transactions
- ✅ **Scalable Design**: Foundation for future extensions
- ✅ **Diagnostic Tools**: Scripts to validate compliance
- ✅ **Unified Database**: Single DB with logical separation

This architecture maintains simplicity while ensuring data integrity and business rule enforcement!
