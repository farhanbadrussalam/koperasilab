<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Master_price extends Model
{
    use HasFactory;
    protected $table = 'master_price';

    protected $fillable = [
        'id_jenisLayanan',
        'keterangan',
        'qty',
        'price'
    ];

    protected $hidden = [
        'id_price',
        'id_jenisTld'
    ];

    protected $appends = [
        'price_hash',
        'jenis_tld_hash'
    ];

    public function getPriceHashAttribute()
    {
        return encryptor($this->id_price);
    }

    public function getJenisTldHashAttribute()
    {
        return encryptor($this->id_jenisTld);
    }

    public function jenisTld(){
        return $this->belongsTo(Master_jenistld::class, 'id_jenisTld', 'id_jenisTld');
    }
}
