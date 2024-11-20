<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penyewaan extends Model    
{
    use HasFactory, Uuid;
    
    protected $table  = 'penyewaan';

    protected $fillable = [
     'mobil_id',
     'tanggal_mulai',
     'tanggal_selesai',
     'rental_option',
     'status', 
     'total_biaya',
     'alamat_pengantaran'
    ];

    public function Mobil ()
    {
        return $this->belongsTo(Mobil::class, 'mobil_id');
    }
}
