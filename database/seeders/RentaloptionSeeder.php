<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Rentaloption;

class RentaloptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Rentaloption::create([
          'nama' => 'Dengan Supir',
          'deskripsi' => 'Mobil dikendarai oleh sopir profesional',
          'biaya' => 200000
        ]);

        Rentaloption::create([
          'nama' => 'Lepas Kunci',
          'deskripsi' => 'Anda mengemudi sendir (harus memiliki SIM)',
          'biaya' => 0
        ]);

    
    }
}
