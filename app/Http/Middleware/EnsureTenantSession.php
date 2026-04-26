<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenantDatabase = (string) $request->session()->get('tenant_db', '');

        if ($tenantDatabase === '') {
            return redirect()->route('login')->withErrors([
                'company_code' => 'Contexte entreprise introuvable. Veuillez vous reconnecter.',
            ]);
        }

        return $next($request);
    }
}

