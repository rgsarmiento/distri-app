<?php

namespace App\Http\Controllers;

use App\Models\CustomerDetail;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $inactiveDays = (int) request()->input('inactive_days', $user->company->alert_days ?? 30);

        // Alertas de stock bajo
        $lowStockProducts = [];
        if ($user->role_id === 1 || $user->role_id === 3) {
            $lowStockQuery = Product::lowStock();
            if ($user->role_id === 3) {
                $lowStockQuery->where('company_id', $user->company_id);
            }
            $lowStockProducts = $lowStockQuery->take(5)->get();
        }

        $stats = [];
        $role = '';
        if ($user->role_id === 1) {
            $stats = $this->getAdminStats($inactiveDays);
            $role = 'admin';
        } elseif ($user->role_id === 3) {
            $stats = $this->getSupervisorStats($user, $inactiveDays);
            $role = 'supervisor';
        } else {
            $stats = $this->getDistributorStats($user);
            $role = 'distributor';
        }

        return view('dashboard', array_merge([
            'user' => $user, 
            'role' => $role, 
            'lowStockProducts' => $lowStockProducts,
            'inactiveDays' => $inactiveDays
        ], $stats));
    }

    // ─────────────────────────────────────────────────────────
    // ADMIN STATS
    // ─────────────────────────────────────────────────────────
    private function getAdminStats(int $inactiveDays): array
    {
        $totalOrders      = Order::count();
        $pendingOrders    = Order::where('status', 'pendiente')->count();
        $billedOrders     = Order::where('status', 'facturado')->count();
        $totalCustomers   = CustomerDetail::count();
        $totalProducts    = Product::count();
        $totalUsers       = User::count();

        $totalRevenue = Order::where('status', 'facturado')->sum('total');
        $pendingRevenue = Order::where('status', 'pendiente')->sum('total');

        $salesByMonth = $this->getSalesByMonth(null, null);
        $topProducts = $this->getTopProducts(null, null);
        $topCustomers = $this->getTopCustomers(null, null);

        $recentOrders = Order::with(['customer', 'user'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $inactiveCustomers = $this->getInactiveCustomers(null, $inactiveDays);

        return compact(
            'totalOrders', 'pendingOrders', 'billedOrders',
            'totalCustomers', 'totalProducts', 'totalUsers',
            'totalRevenue', 'pendingRevenue',
            'salesByMonth', 'topProducts', 'topCustomers', 'recentOrders',
            'inactiveCustomers'
        );
    }

    private function getSupervisorStats($user, int $inactiveDays): array
    {
        $companyId = $user->company_id;
        $customerIds = CustomerDetail::where('company_id', $companyId)->pluck('id')->toArray();

        $totalOrders   = Order::whereIn('customer_id', $customerIds)->count();
        $pendingOrders = Order::whereIn('customer_id', $customerIds)->where('status', 'pendiente')->count();
        $billedOrders  = Order::whereIn('customer_id', $customerIds)->where('status', 'facturado')->count();
        $totalCustomers = count($customerIds);

        $distributorIds = User::where('company_id', $companyId)->where('role_id', 2)->pluck('id');
        $totalDistributors = $distributorIds->count();

        $totalRevenue   = Order::whereIn('customer_id', $customerIds)->where('status', 'facturado')->sum('total');
        $pendingRevenue = Order::whereIn('customer_id', $customerIds)->where('status', 'pendiente')->sum('total');

        $salesByMonth = $this->getSalesByMonth('customer_id', $customerIds);
        $topProducts = $this->getTopProducts('customer_id', $customerIds);
        $topCustomers = $this->getTopCustomers('company_id', $companyId);

        $recentOrders = Order::with(['customer', 'user'])
            ->whereIn('customer_id', $customerIds)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $inactiveCustomers = $this->getInactiveCustomers($companyId, $inactiveDays);

        return compact(
            'totalOrders', 'pendingOrders', 'billedOrders',
            'totalCustomers', 'totalDistributors',
            'totalRevenue', 'pendingRevenue',
            'salesByMonth', 'topProducts', 'topCustomers', 'recentOrders',
            'inactiveCustomers'
        );
    }
    private function getDistributorStats($user): array
    {
        $totalOrders   = Order::where('user_id', $user->id)->count();
        $pendingOrders = Order::where('user_id', $user->id)->where('status', 'pendiente')->count();
        $billedOrders  = Order::where('user_id', $user->id)->where('status', 'facturado')->count();

        $totalRevenue   = Order::where('user_id', $user->id)->where('status', 'facturado')->sum('total');
        $pendingRevenue = Order::where('user_id', $user->id)->where('status', 'pendiente')->sum('total');

        $salesByMonth = $this->getSalesByMonth('user_id', [$user->id]);
        $topProducts  = $this->getTopProducts('user_id', [$user->id]);
        $topCustomers = $this->getTopCustomers('user_id', $user->id);
        $totalCustomers = Order::where('user_id', $user->id)->distinct('customer_id')->count('customer_id');

        $recentOrders = Order::with(['customer'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return compact(
            'totalOrders', 'pendingOrders', 'billedOrders',
            'totalRevenue', 'pendingRevenue', 'totalCustomers',
            'salesByMonth', 'topProducts', 'topCustomers', 'recentOrders'
        );
    }

    private function getSalesByMonth(?string $filterColumn, ?array $filterValues): array
    {
        $months = [];
        $totals = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->locale('es')->isoFormat('MMM YY');
            $query = Order::where('status', 'facturado')->whereYear('created_at', $date->year)->whereMonth('created_at', $date->month);
            if ($filterColumn && $filterValues) $query->whereIn($filterColumn, $filterValues);
            $totals[] = (float) $query->sum('total');
        }
        return ['labels' => $months, 'data' => $totals];
    }

    private function getTopProducts(?string $filterColumn, ?array $filterValues): array
    {
        $query = \DB::table('order_products')
            ->join('products', 'order_products.product_id', '=', 'products.id')
            ->join('orders', 'order_products.order_id', '=', 'orders.id')
            ->where('orders.status', 'facturado')
            ->select('products.name', \DB::raw('SUM(order_products.quantity) as total_qty'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_qty')->limit(5);
        if ($filterColumn && $filterValues) $query->whereIn("orders.{$filterColumn}", $filterValues);
        $results = $query->get();
        return ['labels' => $results->pluck('name')->toArray(), 'data' => $results->pluck('total_qty')->map(fn($v) => (int)$v)->toArray()];
    }

    private function getTopCustomers(?string $filterColumn, $filterValue): array
    {
        $query = Order::with('customer')->where('status', 'facturado')
            ->select('customer_id', \DB::raw('SUM(total) as total_comprado'))
            ->groupBy('customer_id')->orderByDesc('total_comprado')->limit(5);
        if ($filterColumn === 'company_id') {
            $customerIds = CustomerDetail::where('company_id', $filterValue)->pluck('id');
            $query->whereIn('customer_id', $customerIds);
        } elseif ($filterColumn === 'user_id') {
            $query->where('user_id', $filterValue);
        }
        $results = $query->get();
        return ['labels' => $results->map(fn($o) => $o->customer?->full_name ?? 'N/A')->toArray(), 'data' => $results->pluck('total_comprado')->map(fn($v) => (float)$v)->toArray()];
    }

    /**
     * Clientes inactivos: sin órdenes facturadas en los últimos X días
     */
    private function getInactiveCustomers(?int $companyId, int $inactiveDays)
    {
        $cutoffDate = Carbon::now()->subDays($inactiveDays);

        // Optimización de la consulta para obtener la última orden facturada
        $subQuery = \DB::table('orders')
            ->select('customer_id', \DB::raw('MAX(created_at) as last_order_at'))
            ->where('status', 'facturado')
            ->groupBy('customer_id');

        $query = CustomerDetail::query()
            ->leftJoinSub($subQuery, 'latest_orders', function ($join) {
                $join->on('customer_details.id', '=', 'latest_orders.customer_id');
            })
            ->select('customer_details.*', 'latest_orders.last_order_at');

        if ($companyId) {
            $query->where('customer_details.company_id', $companyId);
        }

        // Calcular el conteo real solo para la alerta (que tengan pedidos Y SLIP los días)
        $inactiveCount = (clone $query)
            ->whereNotNull('latest_orders.last_order_at')
            ->where('latest_orders.last_order_at', '<', $cutoffDate)
            ->count();

        $query->orderByRaw('latest_orders.last_order_at IS NULL ASC')
              ->orderBy('latest_orders.last_order_at', 'ASC');

        $paginated = $query->paginate(20, ['*'], 'inactive_page')
            ->withQueryString()
            ->fragment('inactive-section');

        // Adjuntar el conteo real al objeto paginado para usarlo en la vista
        $paginated->inactiveCount = $inactiveCount;

        $paginated->getCollection()->transform(function ($customer) {
            if ($customer->last_order_at) {
                $customer->days_inactive = (int) Carbon::parse($customer->last_order_at)->diffInDays(Carbon::now());
            } else {
                $customer->days_inactive = null;
            }
            return $customer;
        });

        return $paginated;
    }
}
