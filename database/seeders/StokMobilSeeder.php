<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\StokMobil;
use App\Models\Kota;
use App\Models\Mobil;

class StokMobilSeeder extends Seeder
{
    public function run(): void
    {
        $mobils = Mobil::all();
        $kotas = Kota::all();
        
        foreach ($kotas as $kota) {
            foreach ($mobils as $mobil) {
                // 70% 
                if (rand(1, 100) <= 70) {
                    StokMobil::create([
                        'mobil_id' => $mobil->id,
                        'kota_id' => $kota->id,
                        'stok' => rand(1, 8)  // Random stock 
                    ]);
                }
            }
        }
    }
}