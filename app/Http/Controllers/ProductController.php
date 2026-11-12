<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * Muestra el inventario de productos con filtros.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Product::with('company');

        // Filtrado por rol
        if ($user->role_id === 3) {
            $query->where('company_id', $user->company_id);
        }

        // Búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%");
            });
        }

        // Filtro de stock bajo
        if ($request->boolean('low_stock')) {
            $query->lowStock();
        }

        // Filtro por estado activo / inactivo
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where(function($q) {
                    $q->where('is_active', false)
                      ->orWhere('base_price_1', '<=', 0);
                });
            }
        }

        $products = $query->orderBy('name')->paginate(20)->withQueryString();
        
        return view('products.index', compact('products'));
    }

    public function createProducts($products)
    {
        foreach ($products as $product) {
            $code = trim((string)($product['code'] ?? ''));
            if ($code === '') {
                continue;
            }

            // Busca el producto por código
            $existingProduct = Product::where('code', $code)->first();

            // Determinar base_price_1 (o base_price si base_price_1 no viene especificado)
            $rawPrice1 = $product['base_price_1'] ?? $product['base_price'] ?? null;
            $basePrice1 = is_numeric($rawPrice1) ? (float) $rawPrice1 : 0.0;

            // Requerimiento: si base_price_1 <= 0
            if ($basePrice1 <= 0) {
                // Si NO existe en BD, NO se inserta (se ignora)
                if (!$existingProduct) {
                    continue;
                }

                // Si ya existe, se deshabilita para impedir nuevos pedidos
                $updateData = [
                    'is_active'    => false,
                    'base_price'   => $basePrice1,
                    'base_price_1' => $basePrice1,
                    'updated_at'   => now(),
                ];

                if (isset($product['base_price_2'])) {
                    $updateData['base_price_2'] = (float) $product['base_price_2'];
                }
                if (isset($product['base_price_3'])) {
                    $updateData['base_price_3'] = (float) $product['base_price_3'];
                }
                if (!empty($product['name'])) {
                    $updateData['name'] = $product['name'];
                }
                if (isset($product['tax_rate'])) {
                    $updateData['tax_rate'] = (float) $product['tax_rate'];
                }
                if (isset($product['company_id'])) {
                    $updateData['company_id'] = $product['company_id'];
                }
                if (isset($product['stock'])) {
                    $updateData['stock'] = (float) $product['stock'];
                }
                if (isset($product['min_stock'])) {
                    $updateData['min_stock'] = (float) $product['min_stock'];
                }

                $existingProduct->update($updateData);
                continue;
            }

            // Si base_price_1 > 0: se inserta o modifica y se habilita (is_active = true)
            $data = [
                'name'         => $product['name'],
                'base_price'   => $basePrice1,
                'base_price_1' => $basePrice1,
                'base_price_2' => isset($product['base_price_2']) ? (float) $product['base_price_2'] : 0,
                'base_price_3' => isset($product['base_price_3']) ? (float) $product['base_price_3'] : 0,
                'tax_rate'     => (float) $product['tax_rate'],
                'company_id'   => $product['company_id'],
                'is_active'    => true,
                'updated_at'   => now(),
            ];

            // Si se envía stock desde Nodo POS, lo actualizamos
            if (isset($product['stock'])) {
                $data['stock'] = (float) $product['stock'];
            }
            if (isset($product['min_stock'])) {
                $data['min_stock'] = (float) $product['min_stock'];
            }

            if ($existingProduct) {
                $existingProduct->update($data);
            } else {
                $data['code'] = $code;
                $data['created_at'] = now();
                Product::create($data);
            }
        }
    }

    public function getProducts()
    {
        $products = Product::active()->get();
        return response()->json($products);
    }

    public function store(Request $request)
    {
        try {
            // Valida la entrada
            $request->validate([
                'products' => 'required|array',
                'products.*.name' => 'required|string|max:255',
                'products.*.code' => 'required|string',
                'products.*.base_price' => 'nullable|numeric',
                'products.*.base_price_1' => 'nullable|numeric',
                'products.*.base_price_2' => 'nullable|numeric',
                'products.*.base_price_3' => 'nullable|numeric',
                'products.*.tax_rate' => 'required|numeric',
                'products.*.company_id' => 'required|integer|exists:companies,id',
            ]);

            $products = $request->input('products');
            $this->createProducts($products);

            return response()->json(['status' => true, 'message' => 'Productos creados o actualizados exitosamente.']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function searchProducts(Request $request)
    {
        $user = Auth::user();
        $queryValue = $request->input('query');
        
        $query = Product::active();

        if ($user && $user->role_id !== 1) {
            $query->where('company_id', $user->company_id);
        }

        $products = $query->where(function($q) use ($queryValue) {
                $q->where('name', 'LIKE', "%{$queryValue}%")
                  ->orWhere('code', 'LIKE', "%{$queryValue}%");
            })
            ->get();

        return response()->json($products);
    }
}
