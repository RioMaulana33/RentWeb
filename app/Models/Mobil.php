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
        "tahun",
        "tarif",
        "kapasitas",
        "foto",
    ];

    public function Penyewaan ()
    {
        return $this->hasMany(Penyewaan::class);
    }
}
