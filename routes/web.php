<?php

use App\Http\Middleware\RoleMiddleware;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CustomerDetailController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

// ──────────────────────────────────────────────────────────────
// DASHBOARD (Admin, Supervisor, Distribuidor)
// ──────────────────────────────────────────────────────────────
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// ──────────────────────────────────────────────────────────────
// RUTAS AUTENTICADAS
// ──────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── Solo Admin ────────────────────────────────────────────
    Route::middleware([RoleMiddleware::class])->group(function () {
        Route::resource('companies', CompanyController::class);
    });

    // ── Usuarios: Admin + Supervisor ──────────────────────────
    Route::resource('users', UserController::class);

    // ── Clientes: Admin + Supervisor + Distribuidor ───────────
    Route::resource('customer-details', CustomerDetailController::class);

    // ── Órdenes: Admin + Supervisor + Distribuidor ────────────
    Route::get('/orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
    Route::resource('orders', OrderController::class);

    // ── Productos (Inventario) ────────────────────────────────
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');

    // ── Reportes ──────────────────────────────────────────────
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/orders/excel', [ReportController::class, 'exportExcel'])->name('reports.orders.excel');
    Route::get('/reports/orders/pdf', [ReportController::class, 'exportPdf'])->name('reports.orders.pdf');
    Route::get('/reports/orders/{order}/pdf', [ReportController::class, 'orderPdf'])->name('reports.order.pdf');

    // ── Configuración de alertas por empresa (Admin + Supervisor) ─
    Route::patch('/companies/{company}/alert-days', [CompanyController::class, 'updateAlertDays'])
        ->name('companies.update-alert-days');
});

// ──────────────────────────────────────────────────────────────
// APIs de productos y ubicaciones (sin auth requerida — para integración con Nodo POS)
// ──────────────────────────────────────────────────────────────
Route::post('/products/store', [ProductController::class, 'store'])->name('products.store');
Route::get('/products/get', [ProductController::class, 'getProducts'])->name('products.get');
Route::get('/products/search', [ProductController::class, 'searchProducts'])->name('products.search');
Route::get('/get-municipalities', [LocationController::class, 'getMunicipalities'])->name('get-municipalities');
Route::get('/customers/search', [CustomerDetailController::class, 'search'])->name('customers.search');

require __DIR__ . '/auth.php';
