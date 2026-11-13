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

function createTestCompany(): Company {
    return Company::create([
        'nit'          => '900' . rand(100000, 999999),
        'name'         => 'Empresa Test ' . Str::random(5),
        'phone'        => '3001112233',
        'address'      => 'Calle 123',
        'department'   => 'Valle',
        'municipality' => 'Cali',
        'alert_days'   => 3,
    ]);
}

function createTestUser(Company $company, string $name = 'Usuario Test', int $roleId = 2): User {
    \Illuminate\Support\Facades\DB::table('roles')->insertOrIgnore([
        ['id' => 1, 'name' => 'Admin'],
        ['id' => 2, 'name' => 'Distribuidor'],
        ['id' => 3, 'name' => 'Supervisor'],
    ]);

    return User::create([
        'name'       => $name,
        'email'      => 'user_' . Str::random(8) . '@example.com',
        'password'   => Hash::make('secret123'),
        'company_id' => $company->id,
        'role_id'    => $roleId,
    ]);
}

function createTestCustomer(Company $company, string $fullName = 'Cliente Test', string $identification = '123456789'): CustomerDetail {
    return CustomerDetail::create([
        'company_id'     => $company->id,
        'full_name'      => $fullName,
        'identification' => $identification,
        'email'          => 'client_' . Str::random(6) . '@example.com',
        'address'        => 'Calle 10 # 20-30',
        'phone'          => '3001234567',
        'route_id'       => 1,
        'discount_rate'  => 0,
    ]);
}

it('searches orders with date filters, status, and traceability operators', function () {
    $company   = createTestCompany();
    $seller    = createTestUser($company, 'Vendedor Ruta 1');
    $separator = createTestUser($company, 'Carlos Pérez');
    $reviewer  = createTestUser($company, 'Ana Gómez');
    $debugger  = createTestUser($company, 'Luis Martínez');
    $customer  = createTestCustomer($company, 'Supermercado El Sol', '123456789');

    $product = Product::create([
        'company_id' => $company->id,
        'code'       => 'PROD01',
        'name'       => 'Arroz Diana 1kg',
        'base_price' => 4500.00,
        'tax_rate'   => 0,
        'stock'      => 100,
    ]);

    $order = Order::create([
        'company_id'   => $company->id,
        'user_id'      => $seller->id,
        'separator_id' => $separator->id,
        'reviewer_id'  => $reviewer->id,
        'debugger_id'  => $debugger->id,
        'customer_id'  => $customer->id,
        'route_id'     => 1,
        'payment_term' => 'CONTADO',
        'subtotal'     => 45000.00,
        'total_tax'    => 0.00,
        'total'        => 45000.00,
        'status'       => 'DEPURADO',
        'observations' => 'Entregar en la mañana',
    ]);

    $order->products()->attach($product->id, [
        'quantity'    => 10,
        'price_final' => 4500.00,
        'subtotal'    => 45000.00,
        'total_tax'   => 0.00,
        'total'       => 45000.00,
    ]);

    $today = now()->format('Y-m-d');

    // 1. Consulta con filtros from, to y status=DEPURADO
    $response = $this->getJson("/api/orders/search/{$company->id}/DEPURADO?from={$today}&to={$today}");

    $response->assertStatus(200);
    $data = $response->json();

    expect($data)->toBeArray()->not->toBeEmpty();
    $first = $data[0];

    expect($first['document'])->toBe($order->id)
        ->and($first['status'])->toBe('DEPURADO')
        ->and($first['total'])->toBe('45000.00')
        ->and($first['observations'])->toBe('Entregar en la mañana')
        ->and($first['customer']['identification'])->toBe('123456789')
        ->and($first['customer']['name'])->toBe('Supermercado El Sol')
        ->and($first['user']['id'])->toBe($seller->id)
        ->and($first['separator']['id'])->toBe($separator->id)
        ->and($first['separator']['name'])->toBe('Carlos Pérez')
        ->and($first['reviewer']['id'])->toBe($reviewer->id)
        ->and($first['reviewer']['name'])->toBe('Ana Gómez')
        ->and($first['debugger']['id'])->toBe($debugger->id)
        ->and($first['debugger']['name'])->toBe('Luis Martínez');

    expect($first['products'][0]['code'])->toBe('PROD01')
        ->and($first['products'][0]['name'])->toBe('Arroz Diana 1kg')
        ->and($first['products'][0]['quantity'])->toEqual(10)
        ->and($first['products'][0]['base_price_selected'])->toEqual(4500.00)
        ->and($first['products'][0]['tax_rate'])->toEqual(0)
        ->and($first['products'][0]['total_price'])->toEqual(45000.00);

    // 2. Consulta con status=TODOS
    $responseAll = $this->getJson("/api/orders/search/{$company->id}/TODOS?from={$today}&to={$today}");
    $responseAll->assertStatus(200);
    expect($responseAll->json())->not->toBeEmpty();
});

it('updates multiple orders to FACTURADO and sets invoiced_at', function () {
    $company  = createTestCompany();
    $seller   = createTestUser($company);
    $customer = createTestCustomer($company, 'Cliente Test', '999888777');

    $p1 = Product::create(['company_id' => $company->id, 'code' => 'P1_' . Str::random(4), 'name' => 'Prod 1', 'base_price' => 1000, 'tax_rate' => 0, 'stock' => 50]);

    $o1 = Order::create([
        'company_id'  => $company->id,
        'user_id'     => $seller->id,
        'customer_id' => $customer->id,
        'status'      => 'DEPURADO',
        'subtotal'    => 2000,
        'total_tax'   => 0,
        'total'       => 2000,
    ]);
    $o1->products()->attach($p1->id, ['quantity' => 2, 'price_final' => 1000, 'subtotal' => 2000, 'total_tax' => 0, 'total' => 2000]);

    $o2 = Order::create([
        'company_id'  => $company->id,
        'user_id'     => $seller->id,
        'customer_id' => $customer->id,
        'status'      => 'DEPURADO',
        'subtotal'    => 3000,
        'total_tax'   => 0,
        'total'       => 3000,
    ]);
    $o2->products()->attach($p1->id, ['quantity' => 3, 'price_final' => 1000, 'subtotal' => 3000, 'total_tax' => 0, 'total' => 3000]);

    $response = $this->postJson('/api/orders/update-multiple', [
        'order_ids' => [$o1->id, $o2->id]
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success'       => true,
            'message'       => 'Órdenes actualizadas a FACTURADO exitosamente.',
            'updated_count' => 2,
        ]);

    $o1->refresh();
    $o2->refresh();
    $p1->refresh();

    expect($o1->status)->toBe('FACTURADO')
        ->and($o1->invoiced_at)->not->toBeNull()
        ->and($o2->status)->toBe('FACTURADO')
        ->and($o2->invoiced_at)->not->toBeNull()
        ->and($p1->stock)->toEqual(45); // 50 - 2 - 3 = 45
});

it('updates a single order via GET fallback endpoint /api/orders/update/{id}', function () {
    $company  = createTestCompany();
    $seller   = createTestUser($company);
    $customer = createTestCustomer($company, 'Cliente Solo', '111222333');

    $p = Product::create(['company_id' => $company->id, 'code' => 'PSOLO_' . Str::random(4), 'name' => 'Prod Solo', 'base_price' => 500, 'tax_rate' => 0, 'stock' => 20]);

    $order = Order::create([
        'company_id'  => $company->id,
        'user_id'     => $seller->id,
        'customer_id' => $customer->id,
        'status'      => 'DEPURADO',
        'subtotal'    => 500,
        'total_tax'   => 0,
        'total'       => 500,
    ]);
    $order->products()->attach($p->id, ['quantity' => 1, 'price_final' => 500, 'subtotal' => 500, 'total_tax' => 0, 'total' => 500]);

    $response = $this->getJson("/api/orders/update/{$order->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success'  => true,
            'order_id' => $order->id,
            'status'   => 'FACTURADO',
        ]);

    $order->refresh();
    $p->refresh();

    expect($order->status)->toBe('FACTURADO')
        ->and($order->invoiced_at)->not->toBeNull()
        ->and($p->stock)->toEqual(19);
});

it('returns null for unassigned operators and correctly responds with 404/400 on error cases', function () {
    $company  = createTestCompany();
    $seller   = createTestUser($company);
    $customer = createTestCustomer($company);

    $p = Product::create(['company_id' => $company->id, 'code' => 'P_NULL', 'name' => 'Prod Null', 'base_price' => 100, 'tax_rate' => 0, 'stock' => 10]);

    $order = Order::create([
        'company_id'  => $company->id,
        'user_id'     => $seller->id,
        'customer_id' => $customer->id,
        'status'      => 'CRUDO',
        'subtotal'    => 100,
        'total_tax'   => 0,
        'total'       => 100,
    ]);
    $order->products()->attach($p->id, ['quantity' => 1, 'price_final' => 100, 'subtotal' => 100, 'total_tax' => 0, 'total' => 100]);

    // Consultar con query param status=CRUDO
    $response = $this->getJson("/api/orders/search/{$company->id}/0?status=CRUDO");
    $response->assertStatus(200);
    $data = $response->json();
    $found = collect($data)->firstWhere('document', $order->id);

    expect($found)->not->toBeNull()
        ->and($found['separator'])->toBeNull()
        ->and($found['reviewer'])->toBeNull()
        ->and($found['debugger'])->toBeNull();

    // 404 en update individual no existente
    $res404 = $this->getJson("/api/orders/update/999999");
    $res404->assertStatus(404)
        ->assertJson(['success' => false]);

    // 400 en update-multiple con IDs vacíos o inválidos
    $res400 = $this->postJson("/api/orders/update-multiple", ['order_ids' => []]);
    $res400->assertStatus(400)
        ->assertJson(['success' => false]);

    // 400 en search cuando no se envía company_id o se envía 0 sin allow_all
    $resSearchNoCompany = $this->getJson("/api/orders/search");
    $resSearchNoCompany->assertStatus(400)
        ->assertJsonStructure(['error', 'hint']);

    $resSearchZero = $this->getJson("/api/orders/search/0");
    $resSearchZero->assertStatus(400);

    // Consulta pasando company_id por query string
    $resQueryParam = $this->getJson("/api/orders/search?company_id={$company->id}");
    $resQueryParam->assertStatus(200);
});

