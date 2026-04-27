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

        return $next($request);
    }
}
