<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Layanan_jasa extends Model
{
    use HasFactory;

    protected $table = 'layananjasa';

    protected $fillable = [
        'satuankerja_id',
        'user_id',
        'nama_layanan',
        'biaya_layanan',
        'status',
        'name',
        'created_by'
    ];

    protected $hidden = [
        'id',
        'user_id',
        'satuankerja_id'
    ];

    protected $appends = [
        'layanan_hash'
    ];

    public function getLayananHashAttribute()
    {
        return encryptor($this->id);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function satuanKerja()
    {
        return $this->belongsTo(Satuan_kerja::class, 'satuankerja_id', 'id');
    }
}
