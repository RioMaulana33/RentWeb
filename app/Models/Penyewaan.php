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
        'kode_penyewaan',
        'user_id',
        'mobil_id',
        'kota_id',
        'delivery_id',
        'rentaloptions_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'jam_mulai',
        'status',
        'total_biaya',
        'alamat_pengantaran',
        'deskripsi_alamat',
        'waktu_pengembalian_aktual',
        'denda',
        'maintenance_end',
    ];

    public function Mobil()
    {
        return $this->belongsTo(Mobil::class, 'mobil_id');
    }
    public function Kota()
    {
        return $this->belongsTo(Kota::class, 'kota_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function Delivery()
    {
        return $this->belongsTo(Delivery::class, 'delivery_id');
    }
    public function RentalOption()
    {
        return $this->belongsTo(Rentaloption::class, 'rentaloptions_id');
    }
}
