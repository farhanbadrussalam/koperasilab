<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tbl_kip extends Model
{
    use HasFactory;

    protected $table = 'tbl_kip';
    protected $fillable = [
        'no_kontrak',
        'no_invoice',
        'harga',
        'pajak',
        'status',
        'bukti_pembayaran',
        'ttd_1',
        'created_by'
    ];

    protected $hidden = [
        'id'
    ];

    protected $appends = [
        'kip_hash'
    ];

    public function getKipHashAttribute()
    {
        return encryptor($this->id);
    }

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class, 'no_kontrak', 'no_kontrak');
    }
}
