<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Mobil;

class MobilSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Mobil::create([
            'merk' => 'Toyota Avanza',
            'model' => 'MPV',
            'tahun' => '2022',
            'tarif' => 500000,
            'kapasitas' => '7',
            'foto' => '/media/mobil/avanza2022.png'
        ]);

        Mobil::create([
            'merk' => 'Honda Civic',
            'model' => 'Sedan',
            'tahun' => '2021',
            'tarif' => 800000,
            'kapasitas' => '5',
        ]);

        Mobil::create([
            'merk' => 'Mitsubishi Pajero Sport',
            'model' => 'SUV',
            'tahun' => '2020',
            'tarif' => 1000000,
            'kapasitas' => '7'
        ]);

        Mobil::create([
            'merk' => 'Suzuki Ertiga',
            'model' => 'MPV',
            'tahun' => '2019',
            'tarif' => 450000,
            'kapasitas' => '7',
            'foto' => '/media/mobil/ertiga2019.png'
        ]);

        Mobil::create([
            'merk' => 'Daihatsu Xenia',
            'model' => 'MPV',
            'tahun' => '2022',
            'tarif' => 400000,
            'kapasitas' => '7',
            'foto' => '/media/mobil/xenia2022.png'
        ]);
    }
}
