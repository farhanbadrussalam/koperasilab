<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengiriman_detail extends Model
{
    use HasFactory;

    protected $table = 'pengiriman_detail';
    protected $primaryKey = 'id_pengiriman_detail';

    protected $fillable = [
        'id_pengiriman',
        'jenis',
        'periode',
        'list_tld',
        'nomer_surpeng'
    ];

    protected $hidden = [
        'id_pengiriman_detail',
        'id_pengiriman'
    ];

    protected $appends = [
        'pengiriman_detail_hash',
        'pengiriman_hash'
    ];

    protected $casts = [
        'list_tld' => 'array',
        'id_pengiriman_detail' => 'integer',
        'periode' => 'integer',
    ];

    public function getPengirimanDetailHashAttribute()
    {
        return encryptor($this->id_pengiriman_detail);
    }

    public function getPengirimanHashAttribute()
    {
        return encryptor($this->id_pengiriman);
    }
}
