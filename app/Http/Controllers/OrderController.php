<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CustomerDetail;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderSubstitutionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Eager Loading estricto para evitar el problema N+1
        $query = Order::with([
            'customer:id,full_name,identification,address,phone,route_id,discount_rate,company_id',
            'user:id,name,email,company_id',
            'products:id,name,code,base_price,tax_rate,company_id'
        ]);

        // ── Filtrado por rol y multi-tenancy ──────────────────
        if ($user->role_id === 1) {
            // Admin: ve todo
        } elseif ($user->role_id === 3) {
            // Supervisor: órdenes de su empresa
            $query->where('company_id', $user->company_id);
        } else {
            // Distribuidor / Preventista: sus propias órdenes
            $query->where('user_id', $user->id);
        }

        // ── Filtros adicionales con índices compuestos ─────────
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('route_id')) {
            $query->where('route_id', $request->route_id);
        }
        if ($request->filled('customer_search')) {
            $search = $request->customer_search;
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('full_name', 'LIKE', "%{$search}%")
                  ->orWhere('identification', 'LIKE', "%{$search}%");
            });
        }

        $orders  = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        $filters = $request->only(['status', 'date_from', 'date_to', 'route_id', 'customer_search']);

        return view('orders.index', compact('orders', 'filters'));
    }

    public function show(Order $order)
    {
        $user = Auth::user();

        // Distribuidor: solo sus propias órdenes
        if ($user->role_id === 2 && $user->id !== $order->user_id) {
            abort(403, 'No tienes permiso para ver esta orden.');
        }

        // Supervisor: órdenes de su empresa
        if ($user->role_id === 3 && $order->company_id != $user->company_id) {
            abort(403, 'No tienes permiso para ver esta orden.');
        }

        $order->load([
            'customer',
            'user',
            'separator',
            'reviewer',
            'debugger',
            'products',
            'audits.originalProduct',
            'audits.newProduct',
            'audits.user'
        ]);

        return view('orders.show', compact('order'));
    }

    public function create()
    {
        $user = Auth::user();
        $companyId = $user->company_id;

        $products = Product::active()->when($companyId && $user->role_id !== 1, function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })->get();

        $customers = CustomerDetail::when($companyId && $user->role_id !== 1, function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })->get();

        return view('orders.create', compact('products', 'customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id'   => 'required|exists:customer_details,id',
            'status'        => 'nullable|string',
            'payment_term'  => 'nullable|in:CONTADO,CREDITO',
            'route_id'      => 'nullable|integer',
            'observations'  => 'nullable|string',
            'products'      => 'required|array',
            'products.*'    => 'exists:products,id',
            'quantities'    => 'required|array',
            'quantities.*'  => 'numeric|min:0.01',
            'base_prices'   => 'required|array',
            'base_prices.*' => 'numeric|min:0',
        ]);

        $customer = CustomerDetail::findOrFail($request->customer_id);
        $user = Auth::user();
        $companyId = $user->company_id ?: $customer->company_id;

        // Normalizar estado inicial
        $status = $request->status ?: Order::STATUS_CRUDO;
        if (strtoupper($status) === 'PENDIENTE') {
            $status = Order::STATUS_CRUDO;
        }

        $order = DB::transaction(function () use ($request, $customer, $user, $companyId, $status) {
            $order = new Order();
            $order->company_id   = $companyId;
            $order->user_id      = $user->id;
            $order->customer_id  = $customer->id;
            $order->route_id     = $request->route_id ?: $customer->route_id;
            $order->payment_term = $request->payment_term ?: Order::PAYMENT_CONTADO;
            $order->status       = strtoupper($status);
            $order->observations = $request->observations;
            $order->subtotal     = 0;
            $order->total_tax    = 0;
            $order->total        = 0;
            $order->save();

            $subtotal = 0;
            $totalTax = 0;

            foreach ($request->products as $index => $productId) {
                $product   = Product::active()->findOrFail($productId);
                $quantity  = (float) $request->quantities[$index];
                $basePrice = (float) $request->base_prices[$index];

                $taxRate      = (float) $product->tax_rate;
                $lineSubtotal = round($basePrice * $quantity, 2);
                $lineTotalTax = round($lineSubtotal * ($taxRate / 100), 2);
                $lineTotal    = $lineSubtotal + $lineTotalTax;

                $order->products()->attach($product->id, [
                    'quantity'    => $quantity,
                    'price_final' => $basePrice,
                    'subtotal'    => $lineSubtotal,
                    'total_tax'   => $lineTotalTax,
                    'total'       => $lineTotal,
                ]);

                if ($order->status === Order::STATUS_FACTURADO) {
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

            return $order;
        });

        $this->flashNotification('success', 'Orden Creada', "La orden #{$order->id} ha sido creada exitosamente.");
        session()->flash('clear_order_draft', true);
        return redirect()->route('orders.index');
    }

    public function edit(Order $order)
    {
        if ($order->status === Order::STATUS_FACTURADO || $order->status === 'facturado') {
            $this->flashNotification('error', 'Acceso Denegado', 'No se pueden editar órdenes ya facturadas.');
            return redirect()->route('orders.index');
        }

        $order->load(['products', 'customer']);
        $companyId = $order->company_id;

        $products  = Product::when($companyId, fn($q) => $q->where('company_id', $companyId))->get();
        $customers = CustomerDetail::when($companyId, fn($q) => $q->where('company_id', $companyId))->get();

        return view('orders.edit', compact('order', 'products', 'customers'));
    }

    public function update(Request $request, Order $order)
    {
        if ($order->status === Order::STATUS_FACTURADO || $order->status === 'facturado') {
            abort(403, 'No se puede editar una orden facturada.');
        }

        $request->validate([
            'customer_id'   => 'required|exists:customer_details,id',
            'status'        => 'required|string',
            'payment_term'  => 'nullable|in:CONTADO,CREDITO',
            'route_id'      => 'nullable|integer',
            'observations'  => 'nullable|string',
            'products'      => 'required|array',
            'products.*'    => 'exists:products,id',
            'quantities'    => 'required|array',
            'quantities.*'  => 'numeric|min:0.01',
            'base_prices'   => 'required|array',
            'base_prices.*' => 'numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $order) {
            $customer = CustomerDetail::findOrFail($request->customer_id);

            // Revertir stock previo si ya estaba facturada
            if ($order->status === Order::STATUS_FACTURADO || $order->status === 'facturado') {
                foreach ($order->products as $product) {
                    $product->increment('stock', $product->pivot->quantity);
                }
            }

            $order->customer_id  = $customer->id;
            $order->route_id     = $request->route_id ?: $customer->route_id;
            $order->payment_term = $request->payment_term ?: $order->payment_term;
            $order->status       = strtoupper($request->status);
            $order->observations = $request->observations;
            $order->save();

            $order->products()->detach();

            $subtotal = 0;
            $totalTax = 0;

            foreach ($request->products as $index => $productId) {
                $product   = Product::findOrFail($productId);
                $quantity  = (float) $request->quantities[$index];
                $basePrice = (float) $request->base_prices[$index];

                $taxRate      = (float) $product->tax_rate;
                $lineSubtotal = round($basePrice * $quantity, 2);
                $lineTotalTax = round($lineSubtotal * ($taxRate / 100), 2);
                $lineTotal    = $lineSubtotal + $lineTotalTax;

                $order->products()->attach($product->id, [
                    'quantity'    => $quantity,
                    'price_final' => $basePrice,
                    'subtotal'    => $lineSubtotal,
                    'total_tax'   => $lineTotalTax,
                    'total'       => $lineTotal,
                ]);

                if ($order->status === Order::STATUS_FACTURADO) {
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
        });

        $this->flashNotification('success', 'Orden Actualizada', 'Cambios guardados correctamente.');
        return redirect()->route('orders.index');
    }

    public function destroy($id)
    {
        $order = Order::with('products')->findOrFail($id);

        if ($order->status === Order::STATUS_FACTURADO || $order->status === 'facturado') {
            $this->flashNotification('error', 'Error', 'No se pueden eliminar órdenes facturadas.');
            return redirect()->route('orders.index');
        }

        $order->products()->detach();
        $order->delete();

        $this->flashNotification('success', 'Orden Eliminada', 'El pedido ha sido removido del sistema.');
        return redirect()->route('orders.index');
    }

    /**
     * API: Búsqueda y descarga de pedidos optimizada para sincronización con Nodo ERP
     * GET /api/orders/search/{company_id}/{status}?from=YYYY-MM-DD&to=YYYY-MM-DD&status=XXX
     */
    public function downloadOrders(Request $request, $companyId = null, $status = null)
    {
        // ── 0. Obtener y validar company_id (desde ruta o query param ?company_id= / ?company=)
        $effectiveCompanyId = $companyId ?: $request->query('company_id') ?: $request->query('company');

        // Si se llama sin company_id o con '0' / 'TODOS'
        if (!$effectiveCompanyId || $effectiveCompanyId == '0' || strtoupper($effectiveCompanyId) === 'TODOS' || strtoupper($effectiveCompanyId) === 'ALL') {
            // Solo permitir ver todas las empresas si se especifica explícitamente allow_all=1 (ej. reportes globales del superadmin)
            if (!$request->boolean('allow_all')) {
                return response()->json([
                    'error'   => 'Debe especificar un company_id válido para consultar los pedidos de su empresa.',
                    'hint'    => 'Uso: GET /api/orders/search/{company_id}/{status?} o ?company_id={id}',
                ], 400);
            }
            $effectiveCompanyId = null;
        }

        $query = Order::query();

        // Filtrado estricto por empresa
        if ($effectiveCompanyId) {
            $query->where('orders.company_id', $effectiveCompanyId);
        }

        // ── 1. Filtrado por Estado (query param o parámetro de ruta) ────────
        $statusParam = $request->query('status');
        $effectiveStatus = ($statusParam !== null && $statusParam !== '') ? $statusParam : $status;

        if ($effectiveStatus !== null && $effectiveStatus !== '') {
            $upperStatus = strtoupper(trim($effectiveStatus));
            if (!in_array($upperStatus, ['TODOS', 'ALL', '0'])) {
                if ($upperStatus === 'PENDIENTE' || $upperStatus === 'CRUDO') {
                    $query->whereIn('orders.status', [Order::STATUS_CRUDO, 'pendiente', 'crudo', 'CRUDO']);
                } elseif ($upperStatus === 'FACTURADO' || $upperStatus === '1') {
                    $query->whereIn('orders.status', [Order::STATUS_FACTURADO, 'facturado', 'FACTURADO']);
                } else {
                    $query->where(function($sq) use ($upperStatus) {
                        $sq->where('orders.status', $upperStatus)
                           ->orWhere('orders.status', strtolower($upperStatus));
                    });
                }
            }
        }

        // ── 2. Filtrado por Rango de Fechas (from / to o date_from / date_to) ─
        $from = $request->query('from') ?: $request->query('date_from');
        $to   = $request->query('to') ?: $request->query('date_to');

        if ($from) {
            $query->where('orders.created_at', '>=', trim($from) . ' 00:00:00');
        }
        if ($to) {
            $query->where('orders.created_at', '<=', trim($to) . ' 23:59:59');
        }

        // ── 3. Parámetros opcionales adicionales (user_id, route_id) ─────────
        if ($request->filled('user_id')) {
            $query->where('orders.user_id', $request->query('user_id'));
        }
        if ($request->filled('route_id')) {
            $query->where('orders.route_id', $request->query('route_id'));
        }

        // ── 4. Eager Loading estricto sin problema N+1 ──────────────────────
        $orders = $query->with([
            'customer:id,full_name,identification,address,phone,route_id,discount_rate',
            'user:id,name,email',
            'separator:id,name',
            'reviewer:id,name',
            'debugger:id,name',
            'products:id,name,code,tax_rate,company_id,base_price,base_price_1,stock',
            'audits.originalProduct:id,name,code',
            'audits.newProduct:id,name,code',
            'audits.user:id,name',
        ])
        ->orderBy('orders.created_at', 'asc')
        ->get();

        // ── 5. Formato estructurado para Nodo ERP con trazabilidad ──────────
        $formattedOrders = $orders->map(function ($order) {
            $audits = $order->audits ?? collect();

            // Trazabilidad de operadores
            $separator = $order->separator ? [
                'id'   => $order->separator->id,
                'name' => $order->separator->name,
            ] : null;

            // Revisor: relación directa o auditor de sustituciones si existe
            $reviewerUser = $order->reviewer ?? ($audits->first()?->user ?? null);
            $reviewer = $reviewerUser ? [
                'id'   => $reviewerUser->id,
                'name' => $reviewerUser->name,
            ] : null;

            $debugger = $order->debugger ? [
                'id'   => $order->debugger->id,
                'name' => $order->debugger->name,
            ] : null;

            return [
                'document'      => $order->id,
                'creation_date' => $order->created_at ? $order->created_at->format('Y-m-d H:i:s') : null,
                'status'        => $order->status,
                'total'         => number_format((float) $order->total, 2, '.', ''),
                'subtotal'      => number_format((float) $order->subtotal, 2, '.', ''),
                'total_tax'     => number_format((float) $order->total_tax, 2, '.', ''),
                'observations'  => $order->observations ?: '',
                'company_id'    => $order->company_id,
                'route_id'      => $order->route_id,
                'payment_term'  => $order->payment_term ?: Order::PAYMENT_CONTADO,
                'customer'      => [
                    'id'             => $order->customer->id ?? null,
                    'identification' => $order->customer->identification ?? '',
                    'name'           => $order->customer->full_name ?? '',
                    'address'        => $order->customer->address ?? '',
                    'phone'          => $order->customer->phone ?? '',
                    'route_id'       => $order->customer->route_id ?? null,
                    'discount_rate'  => (float) ($order->customer->discount_rate ?? 0),
                ],
                'user'          => [
                    'id'    => $order->user->id ?? null,
                    'name'  => $order->user->name ?? '',
                    'email' => $order->user->email ?? '',
                ],
                'separator'     => $separator,
                'reviewer'      => $reviewer,
                'debugger'      => $debugger,
                'has_audits'    => $audits->isNotEmpty(),
                'products'      => $order->products->map(function ($product) use ($audits) {
                    $basePrice = $product->pivot->price_final > 0
                        ? (float) $product->pivot->price_final
                        : ($product->pivot->quantity > 0 ? (float) ($product->pivot->subtotal / $product->pivot->quantity) : 0);

                    $lineTotal = (float) $product->pivot->total > 0
                        ? (float) $product->pivot->total
                        : (float) ($product->pivot->subtotal + $product->pivot->total_tax);

                    if ($lineTotal <= 0 && $product->pivot->quantity > 0) {
                        $lineTotal = round($basePrice * (float) $product->pivot->quantity, 2);
                    }

                    // Trazabilidad de sustituciones para este producto
                    $productAudits = $audits->filter(function ($audit) use ($product) {
                        return $audit->original_product_id == $product->id || $audit->new_product_id == $product->id;
                    });

                    return [
                        'id'                  => $product->id,
                        'code'                => $product->code,
                        'name'                => $product->name,
                        'quantity'            => (float) $product->pivot->quantity,
                        'base_price_selected' => (float) number_format($basePrice, 2, '.', ''),
                        'tax_rate'            => (float) $product->tax_rate,
                        'total_price'         => (float) number_format($lineTotal, 2, '.', ''),
                        'subtotal'            => (float) number_format((float) $product->pivot->subtotal, 2, '.', ''),
                        'total_tax'           => (float) number_format((float) $product->pivot->total_tax, 2, '.', ''),
                        'company_id'          => $product->company_id,
                        'has_substitution'    => $productAudits->isNotEmpty(),
                        'substitution_audits' => $productAudits->map(fn($a) => [
                            'audit_id'          => $a->id,
                            'action_type'       => $a->action_type,
                            'original_product'  => $a->originalProduct ? $a->originalProduct->name : null,
                            'original_quantity' => (float) $a->original_quantity,
                            'new_product'       => $a->newProduct ? $a->newProduct->name : null,
                            'new_quantity'      => (float) $a->new_quantity,
                            'auditor_name'      => $a->user ? $a->user->name : null,
                            'notes'             => $a->notes,
                            'date'              => $a->created_at ? $a->created_at->toDateTimeString() : null,
                        ])->values(),
                    ];
                }),
                'created_at'    => $order->created_at ? $order->created_at->toIso8601String() : null,
            ];
        });

        return response()->json($formattedOrders, 200);
    }

    /**
     * API: Actualización del estado de un solo pedido (fallback individual)
     * GET /api/orders/update/{orderId}
     */
    public function updateOrderStatus($orderId)
    {
        $order = Order::with('products')->find($orderId);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => "La orden #{$orderId} no fue encontrada."
            ], 404);
        }

        DB::transaction(function () use ($order) {
            // Descontar inventario si no estaba facturada
            if (!in_array(strtoupper($order->status), [Order::STATUS_FACTURADO, 'facturado'])) {
                foreach ($order->products as $product) {
                    $product->decrement('stock', $product->pivot->quantity);
                }
            }

            $order->update([
                'status'      => Order::STATUS_FACTURADO,
                'invoiced_at' => now(),
                'updated_at'  => now(),
            ]);
        });

        return response()->json([
            'success'     => true,
            'message'     => 'Estado de la orden actualizado a FACTURADO exitosamente.',
            'order_id'    => (int) $order->id,
            'status'      => Order::STATUS_FACTURADO,
            'invoiced_at' => now()->toDateTimeString(),
        ], 200);
    }

    /**
     * API: Actualización masiva de estado en una sola transacción SQL
     * POST /api/orders/update-multiple
     * Body: { "order_ids": [1045, 1046, 1047] }
     */
    public function updateMultipleOrderStatus(Request $request)
    {
        $rawOrderIds = $request->input('order_ids', []);
        $targetStatus = strtoupper($request->input('status', Order::STATUS_FACTURADO));

        if (empty($rawOrderIds) || !is_array($rawOrderIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No se proporcionaron IDs de orden válidos.'
            ], 400);
        }

        $orderIds = array_values(array_filter(array_map('intval', $rawOrderIds)));

        if (empty($orderIds)) {
            return response()->json([
                'success' => false,
                'message' => 'El array order_ids está vacío o no contiene IDs numéricos válidos.'
            ], 400);
        }

        $updatedCount = DB::transaction(function () use ($orderIds, $targetStatus) {
            // Si el estado pasa a FACTURADO, actualizar stock de productos que aún no se hayan descargado
            if ($targetStatus === Order::STATUS_FACTURADO) {
                $orders = Order::whereIn('id', $orderIds)
                    ->whereNotIn('status', [Order::STATUS_FACTURADO, 'facturado'])
                    ->with('products')
                    ->get();

                foreach ($orders as $order) {
                    foreach ($order->products as $product) {
                        $product->decrement('stock', $product->pivot->quantity);
                    }
                }
            }

            $updateData = [
                'status'     => $targetStatus,
                'updated_at' => now(),
            ];

            if ($targetStatus === Order::STATUS_FACTURADO) {
                $updateData['invoiced_at'] = now();
            }

            return Order::whereIn('id', $orderIds)->update($updateData);
        });

        return response()->json([
            'success'       => true,
            'message'       => "Órdenes actualizadas a {$targetStatus} exitosamente.",
            'updated_count' => $updatedCount
        ], 200);
    }

    /**
     * API: Sustitución auditable de producto en bodega
     * POST /api/orders/{id}/substitute-item
     */
    public function substituteItem(Request $request, $id, OrderSubstitutionService $substitutionService)
    {
        $request->validate([
            'original_product_id' => 'required|exists:products,id',
            'new_product_id'      => 'nullable|exists:products,id',
            'new_quantity'        => 'nullable|numeric|min:0',
            'action_type'         => 'required|in:SUBSTITUTE,REMOVE,ADJUST_QTY',
            'notes'               => 'nullable|string|max:500',
        ]);

        $order = Order::findOrFail($id);

        try {
            $result = $substitutionService->execute(
                $order,
                (int) $request->original_product_id,
                $request->new_product_id ? (int) $request->new_product_id : null,
                (float) ($request->new_quantity ?? 0),
                $request->action_type,
                $request->notes,
                Auth::id()
            );

            return response()->json([
                'status'     => true,
                'message'    => 'Ítem de la orden actualizado y auditado con éxito.',
                'difference' => $result['difference'],
                'order'      => [
                    'id'       => $result['order']->id,
                    'status'   => $result['order']->status,
                    'subtotal' => (float) $result['order']->subtotal,
                    'total_tax'=> (float) $result['order']->total_tax,
                    'total'    => (float) $result['order']->total,
                ],
                'audit'      => $result['audit'],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function showInvoice($id)
    {
        $order = Order::with('products', 'user')->findOrFail($id);
        $company = Company::findOrFail($order->company_id ?: $order->user->company_id);
        $customer = CustomerDetail::findOrFail($order->customer_id);
        $order->company_details = $company;
        $order->customer_details = $customer;
        return view('orders.invoice', compact('order'));
    }

    private function flashNotification($type, $title, $message)
    {
        session()->flash('notification', [
            'type'    => $type,
            'title'   => $title,
            'message' => $message
        ]);
    }
}
