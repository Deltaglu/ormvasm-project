<?php
namespace App\Http\Middleware;

use App\Services\TenantConnectionManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiTenantContext
{
    public function __construct(private readonly TenantConnectionManager $tenantConnectionManager)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $tenantDatabase = (string) $request->header('X-Tenant-Database', '');

        if ($tenantDatabase === '') {
            return response()->json([
                'message' => 'Tenant context missing. Provide X-Tenant-Database header.',
            ], 422);
        }

        $this->tenantConnectionManager->connect($tenantDatabase, 'pre-fix', 'H3');

        // #region agent log
        file_put_contents(
            base_path('debug-2538fb.log'),
            json_encode([
                'sessionId' => '2538fb',
                'runId' => 'pre-fix',
                'hypothesisId' => 'H3',
                'location' => 'app/Http/Middleware/EnsureApiTenantContext.php:28',
                'message' => 'Tenant DB applied from API header',
                'data' => ['tenant_db' => $tenantDatabase, 'path' => $request->path()],
                'timestamp' => round(microtime(true) * 1000),
            ], JSON_UNESCAPED_SLASHES).PHP_EOL,
            FILE_APPEND
        );
        // #endregion

        return $next($request);
    }
}

