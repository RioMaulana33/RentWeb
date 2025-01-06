<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Kota;

class KotaSeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            'Jakarta', 'Bandung', 'Surabaya', 'Yogyakarta', 'Semarang',
            'Malang', 'Medan', 'Palembang', 'Makassar', 'Manado',
            'Denpasar', 'Balikpapan', 'Pontianak', 'Padang', 'Pekanbaru',
            'Banjarmasin', 'Samarinda', 'Tasikmalaya', 'Bogor', 'Cirebon',
            'Magelang', 'Solo', 'Mataram', 'Ambon', 'Jayapura',
            'Kupang', 'Jambi', 'Bengkulu', 'Banda Aceh', 'Serang'
        ];

        foreach ($cities as $city) {
            Kota::create(['nama' => $city]);
        }
    }
}