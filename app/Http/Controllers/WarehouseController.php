<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Services\OrderSubstitutionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WarehouseController extends Controller
{
    /**
     * Bandeja de pedidos en bodega filtrable por estado
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Order::with([
            'customer:id,full_name,identification,address,phone,route_id',
            'user:id,name',
            'products:id,name,code,stock'
        ]);

        // Multi-tenant por empresa
        if ($user->role_id !== 1 && $user->company_id) {
            $query->where('company_id', $user->company_id);
        }

        // Filtro por defecto en bandeja de bodega: CRUDO, SEPARADO, REVISADO
        $statusFilter = $request->input('status');
        if ($statusFilter) {
            $query->where('status', strtoupper($statusFilter));
        } else {
            $query->whereIn('status', [Order::STATUS_CRUDO, Order::STATUS_SEPARADO, Order::STATUS_REVISADO]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('full_name', 'like', "%{$search}%")
                         ->orWhere('identification', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->orderBy('created_at', 'asc')->paginate(15)->withQueryString();

        return view('warehouse.index', compact('orders', 'statusFilter'));
    }

    /**
     * Pantalla de revisión física y auditoría de un pedido
     */
    public function review($id)
    {
        $user = Auth::user();

        $order = Order::with([
            'customer',
            'user',
            'products',
            'audits.originalProduct',
            'audits.newProduct',
            'audits.user'
        ])->findOrFail($id);

        if ($user->role_id !== 1 && $order->company_id && $order->company_id != $user->company_id) {
            abort(403, 'No tienes permiso para auditar órdenes de otra empresa.');
        }

        // Catálogo de productos disponibles de la empresa para sustitución
        $availableProducts = Product::when($order->company_id, fn($q) => $q->where('company_id', $order->company_id))
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'base_price', 'base_price_1', 'tax_rate', 'stock']);

        return view('warehouse.review', compact('order', 'availableProducts'));
    }

    /**
     * Actualiza el estado del pedido en el flujo de bodega (SEPARADO, REVISADO, DEPURADO)
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:SEPARADO,REVISADO,DEPURADO,FACTURADO,ANULADO'
        ]);

        $order = Order::findOrFail($id);
        $newStatus = strtoupper($request->status);

        $order->update([
            'status'     => $newStatus,
            'updated_at' => now(),
        ]);

        $messages = [
            'SEPARADO'  => 'El pedido ha sido marcado como SEPARADO (en alistamiento).',
            'REVISADO'  => 'El pedido ha sido marcado como REVISADO (inventario validado).',
            'DEPURADO'  => 'El pedido ha sido DEPURADO y está listo para descarga en Nodo ERP.',
            'FACTURADO' => 'El pedido ha sido marcado como FACTURADO.',
            'ANULADO'   => 'El pedido ha sido ANULADO.',
        ];

        session()->flash('notification', [
            'type'    => 'success',
            'title'   => 'Estado Actualizado',
            'message' => $messages[$newStatus] ?? 'Estado actualizado correctamente.'
        ]);

        return redirect()->back();
    }
}
