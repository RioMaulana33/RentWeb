<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuid;

class Wishlist extends Model
{
    use HasFactory, Uuid;

    protected $table = 'wishlist';

    protected $fillable = [
        'mobil_id',
        'user_id',
        'kota_id',
    ];

    public function mobil()
    {
        return $this->belongsTo(Mobil::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function kota()
    {
        return $this->belongsTo(Kota::class);
    }
}
