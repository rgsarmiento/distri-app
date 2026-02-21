<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Company;
use App\Models\Product;
use App\Models\CustomerDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    /**
     * Seed the application's database with demo data.
     */
    public function run(): void
    {
        // 1. Crear Empresas
        $company1 = Company::updateOrCreate(
            ['nit' => '900.123.456-1'],
            [
                'name' => 'Distribuidora del Caribe',
                'phone' => '3001234567',
                'address' => 'Calle 10 # 5-20',
                'municipality' => 'Barranquilla',
                'department' => 'Atlántico',
                'alert_days' => 15
            ]
        );

        $company2 = Company::updateOrCreate(
            ['nit' => '800.987.654-2'],
            [
                'name' => 'Logística Central SAS',
                'phone' => '3109876543',
                'address' => 'Carrera 45 # 80-12',
                'municipality' => 'Medellín',
                'department' => 'Antioquia',
                'alert_days' => 30
            ]
        );

        // 2. Crear Usuarios
        User::updateOrCreate(
            ['email' => 'admin@distriapp.com'],
            [
                'name' => 'Administrador Global',
                'password' => Hash::make('password'),
                'role_id' => 1,
                'company_id' => $company1->id,
            ]
        );

        User::updateOrCreate(
            ['email' => 'supervisor@caribe.com'],
            [
                'name' => 'Supervisor Caribe',
                'password' => Hash::make('password'),
                'role_id' => 3,
                'company_id' => $company1->id,
            ]
        );

        User::updateOrCreate(
            ['email' => 'distribuidor@caribe.com'],
            [
                'name' => 'Vendedor 1 Caribe',
                'password' => Hash::make('password'),
                'role_id' => 2,
                'company_id' => $company1->id,
            ]
        );

        // 3. Crear Productos
        $products = [
            ['name' => 'Coca Cola 1.5L', 'code' => 'CC15', 'price' => 4500, 'stock' => 100, 'min' => 20],
            ['name' => 'Harina PAN 1kg', 'code' => 'HP01', 'price' => 3200, 'stock' => 15, 'min' => 25],
            ['name' => 'Aceite Girasol 1L', 'code' => 'AG01', 'price' => 8900, 'stock' => 5, 'min' => 10],
            ['name' => 'Arroz Diana 5kg', 'code' => 'AD05', 'price' => 12500, 'stock' => 50, 'min' => 15],
            ['name' => 'Café Sello Rojo 500g', 'code' => 'CSR5', 'price' => 9800, 'stock' => 40, 'min' => 5],
        ];

        foreach ($products as $p) {
            Product::updateOrCreate(
                ['code' => $p['code']],
                [
                    'name' => $p['name'],
                    'base_price' => $p['price'],
                    'base_price_1' => $p['price'],
                    'tax_rate' => 19,
                    'company_id' => $company1->id,
                    'stock' => $p['stock'],
                    'min_stock' => $p['min'],
                ]
            );
        }

        // 4. Crear Clientes
        CustomerDetail::updateOrCreate(
            ['identification' => '12.345.678'],
            [
                'full_name' => 'Tienda la Esquina',
                'phone' => '3000000001',
                'email' => 'tienda@esquina.com',
                'address' => 'Calle Falsa 123',
                'municipality' => 'Barranquilla',
                'company_id' => $company1->id,
            ]
        );

        CustomerDetail::updateOrCreate(
            ['identification' => '98.765.432'],
            [
                'full_name' => 'Supermercado El Éxito',
                'phone' => '3000000002',
                'email' => 'ventas@exito.com',
                'address' => 'Avenida Siempre Viva 742',
                'municipality' => 'Medellín',
                'company_id' => $company2->id,
            ]
        );
    }
}
