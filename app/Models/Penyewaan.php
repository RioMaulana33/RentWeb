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
        'user_id',
        'mobil_id',
        'kota_id',
        'delivery_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'jam_mulai',
        'rental_option',
        'status',
        'total_biaya',
        'alamat_pengantaran'
    ];

    public function Mobil()
    {
        return $this->belongsTo(Mobil::class, 'mobil_id');
    }
    public function Kota()
    {
        return $this->belongsTo(Kota::class, 'kota_id');
    }

    public function Delivery()
    {
        return $this->belongsTo(Delivery::class, 'delivery_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
