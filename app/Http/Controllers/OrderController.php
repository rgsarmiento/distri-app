<?php

namespace App\Http\Controllers;

use App\Models\CustomerDetail;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderProduct;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Order::with(['customer', 'user']);

        // ── Filtrado por rol ──────────────────────────────────
        if ($user->role_id === 1) {
            // Admin: ve todo
        } elseif ($user->role_id === 3) {
            // Supervisor: ve órdenes de los clientes de su empresa
            $customerIds = CustomerDetail::where('company_id', $user->company_id)->pluck('id');
            $query->whereIn('customer_id', $customerIds);
        } else {
            // Distribuidor: solo las suyas
            $query->where('user_id', $user->id);
        }

        // ── Filtros adicionales ───────────────────────────────
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('customer_search')) {
            $search = $request->customer_search;
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('full_name', 'LIKE', "%{$search}%")
                  ->orWhere('identification', 'LIKE', "%{$search}%");
            });
        }

        $orders   = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        $filters  = $request->only(['status', 'date_from', 'date_to', 'customer_search']);

        return view('orders.index', compact('orders', 'filters'));
    }
    // Método para mostrar una orden específica
    public function show(Order $order)
    {
        $user = Auth::user();

        // Distribuidor: solo sus propias órdenes
        if ($user->role_id === 2 && $user->id !== $order->user_id) {
            abort(403, 'No tienes permiso para ver esta orden.');
        }

        // Supervisor: solo órdenes de clientes de su empresa
        if ($user->role_id === 3) {
            $companyCustomerIds = CustomerDetail::where('company_id', $user->company_id)->pluck('id');
            if (!$companyCustomerIds->contains($order->customer_id)) {
                abort(403, 'No tienes permiso para ver esta orden.');
            }
        }

        $order->load(['customer', 'user', 'products']);
        return view('orders.show', compact('order'));
    }

    public function create()
    {
        $products = Product::all();
        $customers = CustomerDetail::all();

        return view('orders.create', compact('products', 'customers'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customer_details,id',
            'status'      => 'nullable|in:pendiente,facturado',
            'observations' => 'nullable|string',
            'products'    => 'required|array',
            'products.*'  => 'exists:products,id',
            'quantities'  => 'required|array',
            'quantities.*' => 'numeric|min:1',
            'base_prices' => 'required|array',
            'base_prices.*' => 'numeric|min:0',
        ]);

        $order = new Order();
        $order->user_id     = Auth::id();
        $order->customer_id = $request->customer_id;
        $order->status      = $request->status ?? 'pendiente';
        $order->observations = $request->observations;
        $order->subtotal    = 0;
        $order->total_tax   = 0;
        $order->total       = 0;
        $order->save();

        $subtotal = 0;
        $totalTax = 0;

        foreach ($request->products as $index => $productId) {
            $product  = Product::findOrFail($productId);
            $quantity = $request->quantities[$index];
            $basePrice = $request->base_prices[$index]; // Usar el precio seleccionado
            
            $taxRate  = $product->tax_rate;
            $lineSubtotal = $basePrice * $quantity;
            $lineTotalTax = ($basePrice * ($taxRate / 100)) * $quantity;
            $lineTotal    = $lineSubtotal + $lineTotalTax;

            $order->products()->attach($product->id, [
                'quantity'  => $quantity,
                'subtotal'  => $lineSubtotal,
                'total_tax' => $lineTotalTax,
                'total'     => $lineTotal,
            ]);

            if ($order->status === 'facturado') {
                $product->decrement('stock', $quantity);
            }

            $subtotal += $lineSubtotal;
            $totalTax += $lineTotalTax;
        }

        $order->update([
            'subtotal'  => $subtotal,
            'total_tax' => $totalTax,
            'total'     => $subtotal + $totalTax,
        ]);

        $this->flashNotification('success', 'Orden Creada', 'La orden ha sido creada exitosamente.');
        return redirect()->route('orders.index');
    }

    public function edit(Order $order)
    {
        if ($order->status === 'facturado') {
            $this->flashNotification('error', 'Acceso Denegado', 'No se pueden editar órdenes ya facturadas.');
            return redirect()->route('orders.index');
        }

        $order->load('products');
        $products  = Product::all();
        $customers = CustomerDetail::all();

        return view('orders.edit', compact('order', 'products', 'customers'));
    }

    public function update(Request $request, Order $order)
    {
        if ($order->status === 'facturado') {
            abort(403, 'No se puede editar una orden facturada.');
        }

        $request->validate([
            'customer_id' => 'required|exists:customer_details,id',
            'status'      => 'required|in:pendiente,facturado',
            'observations' => 'nullable|string',
            'products'    => 'required|array',
            'products.*'  => 'exists:products,id',
            'quantities'  => 'required|array',
            'quantities.*' => 'numeric|min:1',
            'base_prices' => 'required|array',
            'base_prices.*' => 'numeric|min:0',
        ]);

        // Revertir stock si era facturada
        if ($order->status === 'facturado') {
            foreach ($order->products as $product) {
                $product->increment('stock', $product->pivot->quantity);
            }
        }

        $order->customer_id  = $request->customer_id;
        $order->status       = $request->status;
        $order->observations = $request->observations;
        $order->save();

        $order->products()->detach();

        $subtotal = 0;
        $totalTax = 0;

        foreach ($request->products as $index => $productId) {
            $product  = Product::findOrFail($productId);
            $quantity = $request->quantities[$index];
            $basePrice = $request->base_prices[$index];

            $taxRate  = $product->tax_rate;
            $lineSubtotal = $basePrice * $quantity;
            $lineTotalTax = ($basePrice * ($taxRate / 100)) * $quantity;
            $lineTotal    = $lineSubtotal + $lineTotalTax;

            $order->products()->attach($product->id, [
                'quantity'  => $quantity,
                'subtotal'  => $lineSubtotal,
                'total_tax' => $lineTotalTax,
                'total'     => $lineTotal,
            ]);

            if ($order->status === 'facturado') {
                $product->decrement('stock', $quantity);
            }

            $subtotal += $lineSubtotal;
            $totalTax += $lineTotalTax;
        }

        $order->update([
            'subtotal'  => $subtotal,
            'total_tax' => $totalTax,
            'total'     => $subtotal + $totalTax,
        ]);

        $this->flashNotification('success', 'Orden Actualizada', 'Cambios guardados correctamente.');
        return redirect()->route('orders.index');
    }

    public function destroy($id)
    {
        $order = Order::with('products')->findOrFail($id);

        if ($order->status === 'facturado') {
            $this->flashNotification('error', 'Error', 'No se pueden eliminar órdenes facturadas.');
            return redirect()->route('orders.index');
        }

        $order->products()->detach();
        $order->delete();

        $this->flashNotification('success', 'Orden Eliminada', 'El pedido ha sido removido del sistema.');
        return redirect()->route('orders.index');
    }

    public function invoice(Order $order)
    {
        $user = Auth::user();
        if ($user->role_id === 2 && $user->id !== $order->user_id) abort(403);
        
        $order->load(['customer', 'user.company', 'products']);
        return view('orders.invoice', compact('order'));
    }

    private function flashNotification($type, $title, $message)
    {
        session()->flash('notification', [
            'type' => $type,
            'title' => $title,
            'message' => $message
        ]);
    }
}
