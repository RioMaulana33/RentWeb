<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuid;

class Rentaloption extends Model
{
    use HasFactory, Uuid;
    
    protected $table  = 'rentaloptions';

    protected $fillable = [
        'nama',
        'deskripsi',
        'biaya',
    ];


    public function penyewaan()
    {
        return $this->hasMany(Penyewaan::class);
    }
}
