<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tbl_kip extends Model
{
    use HasFactory;

    protected $table = 'tbl_kip';
    protected $fillable = [
        'id_permohonan',
        'no_kontrak',
        'no_invoice',
        'harga',
        'pajak',
        'status',
        'ttd_1',
        'ttd_1_by',
        'created_by'
    ];

    protected $hidden = [
        'id',
        'bukti_pembayaran'
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
        return $this->belongsTo(Permohonan::class, 'id_permohonan', 'id');
    }

    public function bukti()
    {
        return $this->belongsTo(tbl_media::class, 'bukti_pembayaran', 'id');
    }

    public function user()
    {
        return $this->belongsTo(user::class, 'ttd_1_by', 'id');
    }
}
