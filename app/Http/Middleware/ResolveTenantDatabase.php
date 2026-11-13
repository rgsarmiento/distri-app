<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Models\CustomerDetail;
use App\Services\TenantDatabaseManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantDatabase
{
    public function __construct(
        protected TenantDatabaseManager $tenantManager
    ) {}

    /**
     * Resuelve y conmuta la conexión si la empresa solicitada tiene base de datos dedicada.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $companyId = $this->resolveCompanyId($request);

        if ($companyId) {
            // Buscar la empresa en la base de datos central/compartida
            $defaultConn = Config::get('database.default', 'mysql');
            $company = Company::on($defaultConn)->find($companyId);

            if ($company && $company->hasDedicatedDatabase()) {
                $this->tenantManager->switchToTenant($company);
            }
        }

        return $next($request);
    }

    /**
     * Limpia la conexión al finalizar la respuesta para evitar contaminación entre peticiones
     */
    public function terminate(Request $request, Response $response): void
    {
        $this->tenantManager->resetConnection();
    }

    /**
     * Identifica el company_id desde el usuario autenticado, sesión B2B, headers o parámetros
     */
    protected function resolveCompanyId(Request $request): ?int
    {
        // 1. Usuario autenticado en sesión web (si no es Superadmin global role 1 con acceso general)
        $user = Auth::user();
        if ($user && $user->company_id) {
            return (int) $user->company_id;
        }

        // 2. Sesión activa del portal B2B
        if ($request->hasSession() && $request->session()->has('b2b_customer_id')) {
            $defaultConn = Config::get('database.default', 'mysql');
            $customerId = $request->session()->get('b2b_customer_id');
            $customer = CustomerDetail::on($defaultConn)->find($customerId);
            if ($customer && $customer->company_id) {
                return (int) $customer->company_id;
            }
        }

        // 3. Parámetro de ruta en APIs (ej: /api/orders/search/{companyId})
        $routeCompanyId = $request->route('companyId') ?? $request->route('company_id');
        if ($routeCompanyId && is_numeric($routeCompanyId) && $routeCompanyId > 0) {
            return (int) $routeCompanyId;
        }

        // 4. Header HTTP explícito (útil para integraciones ERP Nodo)
        if ($request->hasHeader('X-Company-ID')) {
            $headerVal = $request->header('X-Company-ID');
            if (is_numeric($headerVal) && $headerVal > 0) {
                return (int) $headerVal;
            }
        }

        // 5. Query parameter o body input (?company_id=)
        $queryCompanyId = $request->query('company_id') ?: $request->input('company_id');
        if ($queryCompanyId && is_numeric($queryCompanyId) && $queryCompanyId > 0) {
            return (int) $queryCompanyId;
        }

        return null;
    }
}
