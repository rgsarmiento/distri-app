<?php

namespace App\Http\Controllers;

use App\Models\CustomerDetail;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CustomerB2BController extends Controller
{
    /**
     * Muestra la pantalla de acceso para clientes B2B
     */
    public function loginForm()
    {
        if (session()->has('b2b_customer_id')) {
            return redirect()->route('b2b.portal');
        }
        return view('b2b.login');
    }

    /**
     * Autenticación de cliente con acceso web habilitado
     */
    public function login(Request $request)
    {
        $request->validate([
            'web_user'     => 'required|string',
            'web_password' => 'required|string',
        ]);

        $customer = CustomerDetail::where('web_user', $request->web_user)
            ->where('web_access', true)
            ->first();

        if (!$customer) {
            return back()->withErrors(['web_user' => 'Usuario no encontrado o sin acceso B2B habilitado.'])->withInput();
        }

        // Validar contraseña (soporta tanto bcrypt como texto plano sincronizado de Nodo)
        $passwordMatches = Hash::check($request->web_password, $customer->web_password) 
                        || ($customer->web_password === $request->web_password);

        if (!$passwordMatches) {
            return back()->withErrors(['web_password' => 'Contraseña incorrecta.'])->withInput();
        }

        session([
            'b2b_customer_id'   => $customer->id,
            'b2b_customer_name' => $customer->full_name,
            'b2b_company_id'    => $customer->company_id,
        ]);

        return redirect()->route('b2b.portal');
    }

    /**
     * Catálogo B2B de productos con descuento comercial aplicado
     */
    public function portal(Request $request)
    {
        $customerId = session('b2b_customer_id');
        if (!$customerId) {
            return redirect()->route('b2b.login');
        }

        $customer = CustomerDetail::with(['company', 'sellers'])->findOrFail($customerId);

        // Catálogo de productos de su empresa con stock
        $products = Product::where('company_id', $customer->company_id)
            ->orderBy('name')
            ->get();

        // Órdenes recientes del cliente con productos cargados para auditoría y repetir pedido
        $myOrders = Order::with('products')
            ->where('customer_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        return view('b2b.portal', compact('customer', 'products', 'myOrders'));
    }

    /**
     * Creación de orden directa desde el portal B2B en estado CRUDO
     */
    public function storeOrder(Request $request)
    {
        $customerId = session('b2b_customer_id');
        if (!$customerId) {
            return redirect()->route('b2b.login');
        }

        $request->validate([
            'products'     => 'required|array',
            'products.*'   => 'exists:products,id',
            'quantities'   => 'required|array',
            'quantities.*' => 'numeric|min:1',
            'observations' => 'nullable|string',
        ]);

        $customer = CustomerDetail::findOrFail($customerId);

        // Asignar al vendedor preferido del cliente o al primer usuario disponible de la empresa
        $assignedSeller = $customer->sellers()->first();
        if (!$assignedSeller) {
            $assignedSeller = User::where('company_id', $customer->company_id)->first();
        }
        $userId = $assignedSeller ? $assignedSeller->id : 1;

        $discountRate = (float) $customer->discount_rate;

        $order = DB::transaction(function () use ($request, $customer, $userId, $discountRate) {
            $order = new Order();
            $order->company_id   = $customer->company_id;
            $order->user_id      = $userId;
            $order->customer_id  = $customer->id;
            $order->route_id     = $customer->route_id;
            $order->payment_term = Order::PAYMENT_CONTADO;
            $order->status       = Order::STATUS_CRUDO;
            $order->observations = '[Pedido B2B Web] ' . ($request->observations ?? '');
            $order->subtotal     = 0;
            $order->total_tax    = 0;
            $order->total        = 0;
            $order->save();

            $subtotal = 0;
            $totalTax = 0;

            foreach ($request->products as $index => $productId) {
                $product  = Product::findOrFail($productId);
                $quantity = (float) $request->quantities[$index];

                // Aplicar el descuento comercial al precio base
                $rawBasePrice = (float) ($product->base_price_1 ?: $product->base_price);
                $discountedPrice = $discountRate > 0 ? round($rawBasePrice * (1 - ($discountRate / 100)), 2) : $rawBasePrice;

                $taxRate      = (float) $product->tax_rate;
                $lineSubtotal = round($discountedPrice * $quantity, 2);
                $lineTotalTax = round($lineSubtotal * ($taxRate / 100), 2);
                $lineTotal    = $lineSubtotal + $lineTotalTax;

                $order->products()->attach($product->id, [
                    'quantity'    => $quantity,
                    'price_final' => $discountedPrice,
                    'subtotal'    => $lineSubtotal,
                    'total_tax'   => $lineTotalTax,
                    'total'       => $lineTotal,
                ]);

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

        return redirect()->route('b2b.portal')->with('success_message', "¡Pedido #{$order->id} enviado exitosamente! Será preparado en bodega a la brevedad.");
    }

    /**
     * Cierre de sesión B2B
     */
    public function logout()
    {
        session()->forget(['b2b_customer_id', 'b2b_customer_name', 'b2b_company_id']);
        return redirect()->route('b2b.login');
    }
}
