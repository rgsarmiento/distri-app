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

        $products = $query->orderBy('name')->paginate(20)->withQueryString();
        
        return view('products.index', compact('products'));
    }

    public function createProducts($products)
    {
        foreach ($products as $product) {
            // Busca el producto por código
            $existingProduct = Product::where('code', $product['code'])->first();
    
            $data = [
                'name' => $product['name'],
                'base_price' => $product['base_price_1'] ?? $product['base_price'],
                'base_price_1' => $product['base_price_1'] ?? $product['base_price'],
                'base_price_2' => $product['base_price_2'] ?? 0,
                'base_price_3' => $product['base_price_3'] ?? 0,
                'tax_rate' => $product['tax_rate'],
                'company_id' => $product['company_id'],
                'updated_at' => now(),
            ];

            // Si se envía stock desde Nodo POS, lo actualizamos
            if (isset($product['stock'])) {
                $data['stock'] = $product['stock'];
            }
            if (isset($product['min_stock'])) {
                $data['min_stock'] = $product['min_stock'];
            }

            if ($existingProduct) {
                $existingProduct->update($data);
            } else {
                $data['code'] = $product['code'];
                $data['created_at'] = now();
                Product::create($data);
            }
        }
    }

    public function getProducts()
    {
        $products = Product::all();
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
        
        $query = Product::query();

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
