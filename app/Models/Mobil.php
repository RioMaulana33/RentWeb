<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mobil extends Model    
{
    use HasFactory, Uuid;
    
    protected $table  = 'mobil';

    protected $fillable = [
        "merk",
        "model",
        "type",
        "tahun",
        "tarif",
        "kapasitas",
        "bahan_bakar",
        "foto",
    ];

    public function Penyewaan ()
    {
        return $this->hasMany(Penyewaan::class);
    }

    public function stokmobil()
    {
        return $this->hasMany(StokMobil::class);
    }
}
