<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Master_layanan_jasa extends Model
{
    use HasFactory;

    protected $table = 'master_layanan_jasa';

    protected $fillable = [
        'nama_layanan',
        'status'
    ];

    protected $hidden = [
        'id_layanan'
    ];

    protected $appends = [
        'layanan_hash'
    ];

    public function getLayananHashAttribute()
    {
        return encryptor($this->id_layanan);
    }
}
