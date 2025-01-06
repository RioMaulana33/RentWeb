<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([ 
            'name' => 'Admin',
            'email' => 'blucarra552@gmail.com',
            'password' => bcrypt('12345678'),
            'phone' => '08123456789',
        ])->assignRole('admin');    

        User::create([
            'name' => 'Customer',
            'email' => 'customertest@gmail.com',
            'password' => bcrypt('12345678'),
            'phone' => '089878787878',
            'verify_ktp' => 'Terverifikasi',
        ])->assignRole('user');
    }
}
