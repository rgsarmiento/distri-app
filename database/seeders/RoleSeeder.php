<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['id' => 1, 'name' => 'Admin'],
            ['id' => 2, 'name' => 'Distribuidor'],
            ['id' => 3, 'name' => 'Supervisor'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['id' => $role['id']], ['name' => $role['name']]);
        }
    }
}
