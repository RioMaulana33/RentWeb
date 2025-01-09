<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create([
            'name' => 'admin',
            'guard_name' => 'api',
            'full_name' => 'Administrator',
        ]);

        Role::create([
            'name' => 'admin-kota',
            'guard_name' => 'api',
            'full_name' => 'Admin Kota',
        ]);

        Role::create([
            'name' => 'user',   
            'guard_name' => 'api',
            'full_name' => 'Customer',
        ]);
    }
}
