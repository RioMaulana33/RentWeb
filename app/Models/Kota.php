<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuid;

class Kota extends Model
{
    use HasFactory, Uuid;

    protected $table  = 'kota';

    protected $fillable = ['nama', 'alamat', 'deskripsi', 'foto', 'latitude','longitude'];

    public function stokmobil ()
    {
        return $this->hasMany(StokMobil::class);
    }


    public function penyewaan ()
    {
        return $this->hasMany(Penyewaan::class);
    }

    public function wishlist ()
    {
        return $this->hasMany(Wishlist::class);
    }
    
}
