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
            // If user is authenticated but no tenant_db, logout and redirect to login
            if (auth()->check()) {
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }
            return redirect()->route('login')->withErrors([
                'company_code' => 'Contexte entreprise introuvable. Veuillez vous reconnecter.',
            ]);
        }

        return $next($request);
    }
}

