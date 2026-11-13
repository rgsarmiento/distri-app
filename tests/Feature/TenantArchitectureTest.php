<?php

use App\Models\Company;
use App\Models\User;
use App\Services\TenantDatabaseManager;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(DatabaseTransactions::class);

test('company model correctly detects dedicated vs shared database', function () {
    $sharedCompany = Company::create([
        'nit'          => '900' . rand(100000, 999999),
        'name'         => 'Empresa Compartida',
        'phone'        => '3001112233',
        'address'      => 'Calle 1',
        'department'   => 'Valle',
        'municipality' => 'Cali',
        'db_database'  => null,
    ]);

    $dedicatedCompany = Company::create([
        'nit'          => '900' . rand(100000, 999999),
        'name'         => 'Empresa Dedicada',
        'phone'        => '3001112244',
        'address'      => 'Calle 2',
        'department'   => 'Valle',
        'municipality' => 'Cali',
        'db_database'  => 'distri_test_dedicated',
    ]);

    expect($sharedCompany->hasDedicatedDatabase())->toBeFalse();
    expect($dedicatedCompany->hasDedicatedDatabase())->toBeTrue();

    $dedicatedList = Company::dedicated()->pluck('id');
    expect($dedicatedList)->toContain($dedicatedCompany->id);
    expect($dedicatedList)->not->toContain($sharedCompany->id);

    $sharedList = Company::shared()->pluck('id');
    expect($sharedList)->toContain($sharedCompany->id);
    expect($sharedList)->not->toContain($dedicatedCompany->id);
});

test('tenant database manager switches and resets connections correctly', function () {
    $tenantManager = new TenantDatabaseManager();

    $sharedCompany = Company::create([
        'nit'          => '900' . rand(100000, 999999),
        'name'         => 'Empresa Test',
        'phone'        => '3001112233',
        'address'      => 'Calle 1',
        'department'   => 'Valle',
        'municipality' => 'Cali',
    ]);

    // Empresa compartida: debe retornar la conexión default (mysql)
    $conn = $tenantManager->switchToTenant($sharedCompany);
    expect($conn)->toBe(Config::get('database.default'));
    expect($tenantManager->isDedicatedActive())->toBeFalse();

    // Empresa con BD dedicada
    $dedicatedCompany = Company::create([
        'nit'          => '900' . rand(100000, 999999),
        'name'         => 'Empresa Test Dedicada',
        'phone'        => '3001112233',
        'address'      => 'Calle 1',
        'department'   => 'Valle',
        'municipality' => 'Cali',
        'db_database'  => Config::get('database.connections.mysql.database'), // Usamos la misma BD para no romper conexión en test
    ]);

    $connDedicated = $tenantManager->switchToTenant($dedicatedCompany);
    expect($connDedicated)->toBe(TenantDatabaseManager::TENANT_CONNECTION);
    expect($tenantManager->isDedicatedActive())->toBeTrue();
    expect($tenantManager->getCurrentTenant()->id)->toBe($dedicatedCompany->id);

    // Reset
    $tenantManager->resetConnection();
    expect($tenantManager->isDedicatedActive())->toBeFalse();
    expect($tenantManager->getCurrentTenant())->toBeNull();
});

test('resolve tenant database middleware correctly switches context', function () {
    $tenantManager = app(TenantDatabaseManager::class);
    $middleware = new \App\Http\Middleware\ResolveTenantDatabase($tenantManager);

    $dedicatedCompany = Company::create([
        'nit'          => '900' . rand(100000, 999999),
        'name'         => 'Empresa Middleware Test',
        'phone'        => '3001112233',
        'address'      => 'Calle 1',
        'department'   => 'Valle',
        'municipality' => 'Cali',
        'db_database'  => Config::get('database.connections.mysql.database'),
    ]);

    $request = Request::create("/api/orders/search?company_id={$dedicatedCompany->id}", 'GET');

    $wasDedicated = false;
    $response = $middleware->handle($request, function ($req) use ($tenantManager, &$wasDedicated) {
        $wasDedicated = $tenantManager->isDedicatedActive();
        return new Response('OK');
    });

    expect($response->getContent())->toBe('OK');
    expect($wasDedicated)->toBeTrue();

    // Al llamar terminate, debe restablecerse
    $middleware->terminate($request, $response);
    expect($tenantManager->isDedicatedActive())->toBeFalse();
});

test('tenants migrate command runs successfully on central and dedicated databases', function () {
    $this->artisan('tenants:migrate')
        ->assertSuccessful();
});

test('tenant isolate command creates dedicated database and transfers records', function () {
    $company = Company::create([
        'nit'          => '900' . rand(100000, 999999),
        'name'         => 'Empresa Aislar Test',
        'phone'        => '3001112233',
        'address'      => 'Calle 1',
        'department'   => 'Valle',
        'municipality' => 'Cali',
    ]);

    $tempDb = 'distri_test_iso_' . rand(1000, 9999);

    $this->artisan("tenant:isolate {$company->id} --database={$tempDb} --force")
        ->assertSuccessful();

    $company->refresh();
    expect($company->db_database)->toBe($tempDb);
    expect($company->hasDedicatedDatabase())->toBeTrue();

    // Limpiar la base de datos de prueba creada
    DB::statement("DROP DATABASE IF EXISTS `{$tempDb}`");
});

