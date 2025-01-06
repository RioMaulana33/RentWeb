<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuid;

class Kota extends Model
{
    use HasFactory, Uuid;

    protected $table  = 'kota';

    protected $fillable = ['nama'];

    public function stokmobil ()
    {
        return $this->hasMany(StokMobil::class);
    }
    
}
