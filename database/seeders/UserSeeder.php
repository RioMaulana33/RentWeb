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
        // Main Admin
        User::create([ 
            'name' => 'Admin',
            'email' => 'blucarra552@gmail.com',
            'password' => bcrypt('12345678'),
            'phone' => '08123456789',
        ])->assignRole('admin');

        // Admin for each city
        $cities = [
            'Jakarta', 'Bandung', 'Surabaya', 'Yogyakarta', 'Semarang',
            'Malang', 'Medan', 'Palembang', 'Makassar', 'Manado',
            'Denpasar', 'Balikpapan', 'Pontianak', 'Padang', 'Pekanbaru',
            'Banjarmasin', 'Samarinda', 'Tasikmalaya', 'Bogor', 'Cirebon',
            'Magelang', 'Solo', 'Mataram', 'Ambon', 'Jayapura',
            'Kupang', 'Jambi', 'Bengkulu', 'Banda Aceh', 'Serang'
        ];

        foreach ($cities as $city) {
            User::create([
                'name' => $city,
                'email' => 'admin'.strtolower(str_replace(' ', '', $city)).'@gmail.com',
                'password' => bcrypt('12345678'),
                // 'phone' => '082546789456',
            ])->assignRole('admin-kota');
        }

        // Regular customer user
        User::create([
            'name' => 'Customer',
            'email' => 'customertest@gmail.com',
            'password' => bcrypt('12345678'),
            'phone' => '089878787878',
            'verify_ktp' => 'Terverifikasi',
        ])->assignRole('user');
    }
}