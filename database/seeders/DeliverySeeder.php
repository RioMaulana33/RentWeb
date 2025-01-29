<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Delivery;

class DeliverySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Delivery::create([
          'nama' => 'Ambil Sendiri',
          'deskripsi' => 'Pelanggan datang langsung ke lokasi rental BluCarra untuk mengambil kendaraan yang telah dipesan',
          'biaya' => 0
        ]);

        Delivery::create([
          'nama' => 'Layanan Jemput',
          'deskripsi' => 'Kendaraan diantarkan ke lokasi pelanggan oleh sopir BluCarra sesuai jadwal yang disepakati',
          'biaya' => 8000
        ]);

    
    }
}
