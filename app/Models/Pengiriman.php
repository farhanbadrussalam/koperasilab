<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengiriman extends Model
{
    use HasFactory;

    protected $table = 'pengiriman';
    protected $primaryKey = 'id_pengiriman';

    protected $fillable = [
        'id_pengiriman',
        'no_resi',
        'jenis_pengiriman',
        'id_permohonan',
        'no_kontrak',
        'alamat',
        'detail_alamat',
        'status',
        'tujuan',
        'periode',
        'bukti_pengiriman',
        'bukti_penerima',
        'send_at',
        'recived_at',
        'created_by',
        'created_at'
    ];

    protected $hidden = [
        'id_pengiriman',
        'id_permohonan'
    ];

    protected $appends = [
        'pengiriman_hash',
        'permohonan_hash'
    ];

    public function getPermohonanHashAttribute()
    {
        return encryptor($this->id_permohonan);
    }

    public function getPengirimanHashAttribute()
    {
        return encryptor($this->id_pengiriman);
    }

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class, 'id_permohonan', 'id_permohonan');
    }
}
