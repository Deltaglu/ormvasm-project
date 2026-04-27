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
        }

        return $next($request);
    }
}
