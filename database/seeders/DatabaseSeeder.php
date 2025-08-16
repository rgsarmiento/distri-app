<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\CustomerDetailSeeder;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call(CompanySeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(ProductSeeder::class);
        $this->call(CustomerDetailSeeder::class);

        User::factory()->create([
            'name' => 'Administrador',
            'email' => 'admin@nodo.com',
            'password' => Hash::make('admin@nodo.com'),
            'role_id'=> 1,
            'company_id' => 1,
        ]);

        User::factory()->create([
            'name' => 'duitama',
            'email' => 'duitama@nodo.com',
            'password' => Hash::make('ruta.123'),
            'role_id'=> 2,
            'company_id' => 1,
        ]);

        User::factory()->create([
            'name' => 'paipa',
            'email' => 'paipa@nodo.com',
            'password' => Hash::make('ruta.123'),
            'role_id'=> 2,
            'company_id' => 1,
        ]);

        User::factory()->create([
            'name' => 'wilinfer',
            'email' => 'wilinfer@nodo.com',
            'password' => Hash::make('ruta.123'),
            'role_id'=> 2,
            'company_id' => 1,
        ]);

        User::factory()->create([
            'name' => 'soata',
            'email' => 'soata@nodo.com',
            'password' => Hash::make('ruta.123'),
            'role_id'=> 2,
            'company_id' => 1,
        ]);

        User::factory()->create([
            'name' => 'sogamoso',
            'email' => 'sogamoso@nodo.com',
            'password' => Hash::make('ruta.123'),
            'role_id'=> 2,
            'company_id' => 1,
        ]);

        User::factory()->create([
            'name' => 'barbosa',
            'email' => 'barbosa@nodo.com',
            'password' => Hash::make('rutaapp2025'),
            'role_id'=> 2,
            'company_id' => 2,
        ]);

        User::factory()->create([
            'name' => 'foranea',
            'email' => 'foranea@nodo.com',
            'password' => Hash::make('rutaapp2025'),
            'role_id'=> 2,
            'company_id' => 2,
        ]);

        User::factory()->create([
            'name' => 'norte',
            'email' => 'norte@nodo.com',
            'password' => Hash::make('ruta.123'),
            'role_id'=> 2,
            'company_id' => 2,
        ]);

        User::factory()->create([
            'name' => 'santander',
            'email' => 'santander@nodo.com',
            'password' => Hash::make('ruta.123'),
            'role_id'=> 2,
            'company_id' => 2,
        ]);

        User::factory()->create([
            'name' => 'sur',
            'email' => 'sur@nodo.com',
            'password' => Hash::make('rutaapp2025'),
            'role_id'=> 2,
            'company_id' => 2,
        ]);

        User::factory()->create([
            'name' => 'tenza',
            'email' => 'tenza@nodo.com',
            'password' => Hash::make('ruta.123'),
            'role_id'=> 2,
            'company_id' => 2,
        ]);
    }
}

