<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('settings')->truncate();

        Setting::create([
            'app' => 'BluCarra',
            'description' =>  'Aplikasi e-Rental yang menyediakan layanan sewa mobil online',
            'logo' =>  '/media/misc/logoz.png',
            'bg_auth' =>  '/media/misc/bg_auth.jpg',
            'banner' =>  '/media/misc/banner.jpg',
            'inlog' =>  '/media/misc/mobilelog.png',
            'pemerintah' =>  'Pemerintah Provinsi Jawa Timur',
            'dinas' =>  'Dinas Lingkungan Hidup',
            'alamat' =>  'Jl. Tentara Genie Pelajar No.26, Petemon, Kec. Sawahan, Surabaya, Jawa Timur 60252',
            'telepon' =>  '08123456789',
            'email' =>  'blucarra@gmail.com',
        ]);
    }
}
