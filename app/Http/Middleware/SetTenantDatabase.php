<?php
namespace App\Http\Middleware;

use App\Services\TenantConnectionManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTenantDatabase
{
    public function __construct(private readonly TenantConnectionManager $tenantConnectionManager)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $tenantDatabase = (string) $request->session()->get('tenant_db', '');

        if ($tenantDatabase !== '') {
            $this->tenantConnectionManager->connect($tenantDatabase, 'pre-fix', 'H2');

            // #region agent log
            file_put_contents(
                base_path('debug-2538fb.log'),
                json_encode([
                    'sessionId' => '2538fb',
                    'runId' => 'pre-fix',
                    'hypothesisId' => 'H2',
                    'location' => 'app/Http/Middleware/SetTenantDatabase.php:24',
                    'message' => 'Tenant DB applied from session',
                    'data' => ['tenant_db' => $tenantDatabase, 'path' => $request->path()],
                    'timestamp' => round(microtime(true) * 1000),
                ], JSON_UNESCAPED_SLASHES).PHP_EOL,
                FILE_APPEND
            );
            // #endregion
        }

        return $next($request);
    }
}

