<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keuangan extends Model
{
    use HasFactory;

    protected $table = "keuangan";
    protected $primaryKey = 'id_keuangan';

    protected $fillable = [
        'id_keuangan',
        'id_permohonan',
        'no_invoice',
        'status',
        'ppn',
        'document_faktur',
        'bukti_bayar',
        'bukti_bayar_pph',
        'ttd',
        'ttd_by',
        'total_harga',
        'created_at',
        'created_by'
    ];

    protected $hidden = [
        'id_keuangan',
        'id_permohonan'
    ];

    protected $appends = [
        'keuangan_hash',
        'permohonan_hash'
    ];

    public function getKeuanganHashAttribute()
    {
        return encryptor($this->id_keuangan);
    }

    public function getPermohonanHashAttribute()
    {
        return encryptor($this->id_permohonan);
    }

    public function permohonan()
    {
        return $this->belongsTo(permohonan::class, 'id_permohonan', 'id_permohonan');
    }
}
