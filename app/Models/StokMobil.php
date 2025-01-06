<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuid;

class StokMobil extends Model
{
    use HasFactory, Uuid;

    protected $table = 'stok_mobil'; 
    protected $fillable = ['mobil_id', 'kota_id', 'stok']; 

    public function mobil()
    {
        return $this->belongsTo(Mobil::class);
    }

 
    public function kota()
    {
        return $this->belongsTo(Kota::class);
    }
}
