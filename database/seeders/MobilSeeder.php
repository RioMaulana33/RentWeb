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
        // MODEL MPV
        Mobil::create([
            'merk' => 'Toyota Avanza',
            'model' => 'MPV',
            'type' => 'G MT',
            'tahun' => '2022',
            'tarif' => 500000,
            'kapasitas' => '7',
            'bahan_bakar' => 'Bensin',
        ]);

        Mobil::create([
            'merk' => 'Toyota Innova Zenix',
            'model' => 'MPV',
            'type' => 'G MT',
            'tahun' => '2023',
            'tarif' => 800000,
            'kapasitas' => '7',
            'bahan_bakar' => 'Bensin',
        ]);

        Mobil::create([
            'merk' => 'Mitsubishi Xpander',
            'model' => 'MPV',
            'type' => 'Ultimate AT',
            'tahun' => '2023',
            'tarif' => 600000,
            'kapasitas' => '7',
            'bahan_bakar' => 'Bensin',
        ]);

        Mobil::create([
            'merk' => 'Toyota Veloz',
            'model' => 'MPV',
            'type' => 'Q CVT TSS',
            'tahun' => '2023',
            'tarif' => 600000,
            'kapasitas' => '7',
            'bahan_bakar' => 'Bensin',
        ]);

        Mobil::create([
            'merk' => 'Suzuki Ertiga',
            'model' => 'MPV',
            'type' => 'GX AT',
            'tahun' => '2023',
            'tarif' => 450000,
            'kapasitas' => '7',
            'bahan_bakar' => 'Bensin',
        ]);

        Mobil::create([
            'merk' => 'Hyundai Stargazer',
            'model' => 'MPV',
            'type' => 'Prime AT',
            'tahun' => '2023',
            'tarif' => 550000,
            'kapasitas' => '7',
            'bahan_bakar' => 'Bensin',
        ]);

        // MODEL SUV
        Mobil::create([
            'merk' => 'Toyota Fortuner',
            'model' => 'SUV',
            'type' => 'VRZ 4x2 AT',
            'tahun' => '2023',
            'tarif' => 1300000,
            'kapasitas' => '7',
            'bahan_bakar' => 'Solar',
        ]);

        Mobil::create([
            'merk' => 'Mitsubishi Pajero Sport',
            'model' => 'SUV',
            'type' => 'Dakar 4x2 AT',
            'tahun' => '2023',
            'tarif' => 1200000,
            'kapasitas' => '7',
            'bahan_bakar' => 'Solar',
        ]);

        Mobil::create([
            'merk' => 'Honda CR-V',
            'model' => 'SUV',
            'type' => 'Turbo Prestige',
            'tahun' => '2023',
            'tarif' => 1000000,
            'kapasitas' => '5',
            'bahan_bakar' => 'Bensin',
        ]);

        Mobil::create([
            'merk' => 'Hyundai Creta',
            'model' => 'SUV',
            'type' => 'Prime AT',
            'tahun' => '2023',
            'tarif' => 600000,
            'kapasitas' => '5',
            'bahan_bakar' => 'Bensin',
        ]);

        Mobil::create([
            'merk' => 'Honda HR-V',
            'model' => 'SUV',
            'type' => 'RS Turbo',
            'tahun' => '2023',
            'tarif' => 700000,
            'kapasitas' => '5',
            'bahan_bakar' => 'Bensin',
        ]);

        // MODEL SEDAN
        Mobil::create([
            'merk' => 'Toyota Camry',
            'model' => 'Sedan',
            'type' => '2.5 V AT',
            'tahun' => '2023',
            'tarif' => 1500000,
            'kapasitas' => '5',
            'bahan_bakar' => 'Bensin',
        ]);

        Mobil::create([
            'merk' => 'Honda Civic',
            'model' => 'Sedan',
            'type' => 'RS Turbo',
            'tahun' => '2023',
            'tarif' => 900000,
            'kapasitas' => '5',
            'bahan_bakar' => 'Bensin',
        ]);

        Mobil::create([
            'merk' => 'Toyota Corolla Altis',
            'model' => 'Sedan',
            'type' => '1.8 V AT',
            'tahun' => '2023',
            'tarif' => 800000,
            'kapasitas' => '5',
            'bahan_bakar' => 'Bensin',
        ]);

        Mobil::create([
            'merk' => 'Honda City',
            'model' => 'Sedan',
            'type' => 'RS CVT',
            'tahun' => '2023',
            'tarif' => 600000,
            'kapasitas' => '5',
            'bahan_bakar' => 'Bensin',
        ]);

        Mobil::create([
            'merk' => 'Hyundai Elantra',
            'model' => 'Sedan',
            'type' => 'Premium',
            'tahun' => '2022',
            'tarif' => 700000,
            'kapasitas' => '5',
            'bahan_bakar' => 'Bensin',
        ]);

        // MODEL LCGC
        Mobil::create([
            'merk' => 'Toyota Agya',
            'model' => 'LCGC',
            'type' => 'G MT',
            'tahun' => '2023',
            'tarif' => 300000,
            'kapasitas' => '5',
            'bahan_bakar' => 'Bensin',
        ]);

        Mobil::create([
            'merk' => 'Daihatsu Ayla',
            'model' => 'LCGC',
            'type' => 'R MT',
            'tahun' => '2023',
            'tarif' => 300000,
            'kapasitas' => '5',
            'bahan_bakar' => 'Bensin',
        ]);

        Mobil::create([
            'merk' => 'Honda Brio Satya',
            'model' => 'LCGC',
            'type' => 'E CVT',
            'tahun' => '2023',
            'tarif' => 320000,
            'kapasitas' => '5',
            'bahan_bakar' => 'Bensin',
        ]);

        Mobil::create([
            'merk' => 'Toyota Calya',
            'model' => 'LCGC',
            'type' => 'G MT',
            'tahun' => '2023',
            'tarif' => 350000,
            'kapasitas' => '7',
            'bahan_bakar' => 'Bensin',
        ]);

        Mobil::create([
            'merk' => 'Daihatsu Sigra',
            'model' => 'LCGC',
            'type' => 'R MT',
            'tahun' => '2023',
            'tarif' => 350000,
            'kapasitas' => '7',
            'bahan_bakar' => 'Bensin',
        ]);
    }
}