<?php

use App\Models\Company;
use App\Models\CustomerDetail;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

uses(DatabaseTransactions::class);

function createSyncTestCompany(): Company {
    return Company::create([
        'nit'          => '900' . rand(100000, 999999),
        'name'         => 'Empresa Sync ' . Str::random(5),
        'phone'        => '3001112233',
        'address'      => 'Calle 123',
        'department'   => 'Valle',
        'municipality' => 'Cali',
        'alert_days'   => 3,
    ]);
}

function createSyncTestCustomer(Company $company): CustomerDetail {
    return CustomerDetail::create([
        'company_id'     => $company->id,
        'full_name'      => 'Cliente Test ' . Str::random(4),
        'identification' => '999' . rand(100000, 999999),
        'email'          => 'cust_' . Str::random(6) . '@example.com',
        'address'        => 'Calle 10 # 20-30',
        'phone'          => '3001234567',
        'route_id'       => 1,
    ]);
}

test('new product with base_price_1 = 0 is not inserted into database', function () {
    $company = createSyncTestCompany();
    $uniqueCode = 'TEST_ZERO_' . Str::random(6);

    $payload = [
        'products' => [
            [
                'name'         => 'Producto Invalido Cero',
                'code'         => $uniqueCode,
                'base_price'   => 0,
                'base_price_1' => 0,
                'base_price_2' => 0,
                'base_price_3' => 0,
                'tax_rate'     => 19,
                'company_id'   => $company->id,
            ]
        ]
    ];

    $response = $this->postJson('/api/products/store', $payload);
    $response->assertStatus(200);

    // No debe existir en la base de datos
    $exists = Product::where('code', $uniqueCode)->exists();
    expect($exists)->toBeFalse();
});

test('existing product with base_price_1 = 0 is disabled (is_active = false)', function () {
    $company = createSyncTestCompany();
    $uniqueCode = 'TEST_ACTIVE_' . Str::random(6);

    // Creamos el producto inicialmente activo con precio
    $product = Product::create([
        'name'         => 'Producto Inicial',
        'code'         => $uniqueCode,
        'base_price'   => 15000,
        'base_price_1' => 15000,
        'tax_rate'     => 19,
        'stock'        => 50,
        'company_id'   => $company->id,
        'is_active'    => true,
    ]);

    expect($product->is_active)->toBeTrue();

    // Enviamos sincronización con base_price_1 = 0
    $payload = [
        'products' => [
            [
                'name'         => 'Producto Inicial Actualizado',
                'code'         => $uniqueCode,
                'base_price_1' => 0,
                'tax_rate'     => 19,
                'company_id'   => $company->id,
            ]
        ]
    ];

    $response = $this->postJson('/api/products/store', $payload);
    $response->assertStatus(200);

    $product->refresh();
    expect($product->is_active)->toBeFalse();
    expect((float)$product->base_price_1)->toEqual(0.0);

    // No debe ser retornado por el scope active
    $activeProduct = Product::active()->where('code', $uniqueCode)->first();
    expect($activeProduct)->toBeNull();
});

test('disabled product sent again with base_price_1 > 0 is modified and re-enabled', function () {
    $company = createSyncTestCompany();
    $uniqueCode = 'TEST_RE_ENABLE_' . Str::random(6);

    // Inicia deshabilitado
    $product = Product::create([
        'name'         => 'Producto Deshabilitado',
        'code'         => $uniqueCode,
        'base_price'   => 0,
        'base_price_1' => 0,
        'tax_rate'     => 19,
        'stock'        => 10,
        'company_id'   => $company->id,
        'is_active'    => false,
    ]);

    expect($product->is_active)->toBeFalse();

    // Enviamos sincronización con nuevo precio > 0
    $payload = [
        'products' => [
            [
                'name'         => 'Producto Re-habilitado',
                'code'         => $uniqueCode,
                'base_price_1' => 28000,
                'tax_rate'     => 19,
                'company_id'   => $company->id,
            ]
        ]
    ];

    $response = $this->postJson('/api/products/store', $payload);
    $response->assertStatus(200);

    $product->refresh();
    expect($product->is_active)->toBeTrue();
    expect($product->name)->toBe('Producto Re-habilitado');
    expect((float)$product->base_price_1)->toEqual(28000.0);

    // Ahora sí debe ser retornado por el scope active
    $activeProduct = Product::active()->where('code', $uniqueCode)->first();
    expect($activeProduct)->not->toBeNull();
    expect($activeProduct->id)->toBe($product->id);
});

test('past orders retain disabled products without issues', function () {
    $company = createSyncTestCompany();
    $uniqueCode = 'TEST_ORDER_PROD_' . Str::random(6);

    $product = Product::create([
        'name'         => 'Producto Antiguo',
        'code'         => $uniqueCode,
        'base_price'   => 10000,
        'base_price_1' => 10000,
        'tax_rate'     => 19,
        'stock'        => 20,
        'company_id'   => $company->id,
        'is_active'    => true,
    ]);

    $customer = createSyncTestCustomer($company);

    $user = User::create([
        'name'       => 'Vendedor Test',
        'email'      => 'vend_' . Str::random(5) . '@example.com',
        'password'   => Hash::make('secret'),
        'company_id' => $company->id,
        'role_id'    => 2,
    ]);

    // Creamos una orden con el producto
    $order = Order::create([
        'company_id'   => $company->id,
        'user_id'      => $user->id,
        'customer_id'  => $customer->id,
        'status'       => 'CRUDO',
        'subtotal'     => 10000,
        'total_tax'    => 1900,
        'total'        => 11900,
    ]);

    $order->products()->attach($product->id, [
        'quantity'    => 1,
        'price_final' => 10000,
        'subtotal'    => 10000,
        'total_tax'   => 1900,
        'total'       => 11900,
    ]);

    // Ahora deshabilitamos el producto simulando base_price_1 = 0
    $product->update([
        'is_active'    => false,
        'base_price_1' => 0,
    ]);

    // La orden antigua aún debe cargar el producto perfectamente
    $loadedOrder = Order::with('products')->find($order->id);
    expect($loadedOrder->products)->toHaveCount(1);
    expect($loadedOrder->products->first()->code)->toBe($uniqueCode);
    expect($loadedOrder->products->first()->pivot->total)->toEqual(11900.0);
});

test('search api does not return inactive products', function () {
    $company = createSyncTestCompany();
    $activeCode = 'ACT_' . Str::random(5);
    $inactiveCode = 'INACT_' . Str::random(5);

    Product::create([
        'name'         => 'Producto Visible Activo',
        'code'         => $activeCode,
        'base_price'   => 5000,
        'base_price_1' => 5000,
        'tax_rate'     => 19,
        'company_id'   => $company->id,
        'is_active'    => true,
    ]);

    Product::create([
        'name'         => 'Producto Oculto Inactivo',
        'code'         => $inactiveCode,
        'base_price'   => 0,
        'base_price_1' => 0,
        'tax_rate'     => 19,
        'company_id'   => $company->id,
        'is_active'    => false,
    ]);

    $response = $this->getJson('/products/search?query=Producto');
    $response->assertStatus(200);

    $results = collect($response->json());
    expect($results->pluck('code'))->toContain($activeCode);
    expect($results->pluck('code'))->not->toContain($inactiveCode);
});

test('cannot create new order with disabled product', function () {
    $company = createSyncTestCompany();
    $uniqueCode = 'TEST_FAIL_ORDER_' . Str::random(6);

    $product = Product::create([
        'name'         => 'Producto Inactivo Venta',
        'code'         => $uniqueCode,
        'base_price'   => 0,
        'base_price_1' => 0,
        'tax_rate'     => 19,
        'stock'        => 20,
        'company_id'   => $company->id,
        'is_active'    => false,
    ]);

    $customer = createSyncTestCustomer($company);

    $user = User::create([
        'name'       => 'Vendedor Test',
        'email'      => 'seller_' . Str::random(5) . '@example.com',
        'password'   => Hash::make('secret'),
        'company_id' => $company->id,
        'role_id'    => 2,
    ]);

    $orderPayload = [
        'customer_id' => $customer->id,
        'status'      => 'CRUDO',
        'products'    => [$product->id],
        'quantities'  => [2],
        'base_prices' => [0],
    ];

    $response = $this->actingAs($user)->post('/orders', $orderPayload);
    // Debe fallar con 404 porque Product::active()->findOrFail($productId) no lo encuentra activo
    $response->assertStatus(404);
});

