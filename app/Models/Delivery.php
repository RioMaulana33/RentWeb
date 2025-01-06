<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuid;

class Delivery extends Model
{
    use HasFactory, Uuid;
    
    protected $table  = 'delivery';

    protected $fillable = [
        'nama',
        'deskripsi',
        'cost',
    ];


    public function penyewaan()
    {
        return $this->hasMany(Penyewaan::class);
    }
}
