<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\TenantConnectionManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __construct(private readonly TenantConnectionManager $tenantConnectionManager)
    {
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'company_code' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $company = Company::query()
            ->where('code', $credentials['company_code'])
            ->first();

        // #region agent log
        file_put_contents(
            base_path('debug-2538fb.log'),
            json_encode([
                'sessionId' => '2538fb',
                'runId' => 'pre-fix',
                'hypothesisId' => 'H4',
                'location' => 'app/Http/Controllers/Auth/LoginController.php:34',
                'message' => 'Company lookup completed',
                'data' => [
                    'company_code' => $credentials['company_code'],
                    'company_found' => (bool) $company,
                    'tenant_database' => $company?->tenant_database,
                ],
                'timestamp' => round(microtime(true) * 1000),
            ], JSON_UNESCAPED_SLASHES).PHP_EOL,
            FILE_APPEND
        );
        // #endregion

        if (! $company || blank($company->tenant_database)) {
            throw ValidationException::withMessages([
                'company_code' => __('Code entreprise invalide ou base locataire indisponible.'),
            ]);
        }

        $this->tenantConnectionManager->connect($company->tenant_database, 'pre-fix', 'H1');
        $request->session()->put('tenant_db', $company->tenant_database);
        $request->session()->put('company_code', $company->code);

        // #region agent log
        file_put_contents(
            base_path('debug-2538fb.log'),
            json_encode([
                'sessionId' => '2538fb',
                'runId' => 'pre-fix',
                'hypothesisId' => 'H1',
                'location' => 'app/Http/Controllers/Auth/LoginController.php:58',
                'message' => 'Tenant DB stored in session before auth',
                'data' => [
                    'tenant_db' => $request->session()->get('tenant_db'),
                    'company_code' => $request->session()->get('company_code'),
                ],
                'timestamp' => round(microtime(true) * 1000),
            ], JSON_UNESCAPED_SLASHES).PHP_EOL,
            FILE_APPEND
        );
        // #endregion

        if (! Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ], $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('Identifiants incorrects.'),
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->forget(['tenant_db', 'company_code']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
