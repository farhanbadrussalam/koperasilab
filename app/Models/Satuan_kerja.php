<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Satuan_kerja extends Model
{
    use HasFactory;

    protected $table = 'satuankerja';

    protected $fillable = [
        'name'
    ];

    protected $hidden = [
        'id'
    ];

    protected $appends = [
        'satuan_hash'
    ];

    public function getSatuanHashAttribute()
    {
        return encryptor($this->id);
    }

    public function layananJasa()
    {
        return hasOne(Layanan_jasa::class);
    }
}
