<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model    
{
    use HasFactory, Uuid;
    
    protected $table  = 'pelanggan';

    protected $fillable = [
         'nama', 
         'email', 
         'password', 
         'nomor_telepon',
         'tanggal_lahir', 
         'ktp', 
    ];


}
