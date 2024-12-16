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
        'pph',
        'document_faktur',
        'bukti_bayar',
        'bukti_bayar_pph',
        'ttd',
        'ttd_by',
        'total_harga',
        'created_at',
        'created_by'
    ];

    // Casting kolom sebagai array
    protected $casts = [
        'document_faktur' => 'array'
    ];

    protected $hidden = [
        'id_keuangan',
        'id_permohonan',
        'bukti_bayar',
        'bukti_bayar_pph',
        'document_faktur'
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

    public function diskon()
    {
        return $this->hasMany(Keuangan_diskon::class, 'id_keuangan', 'id_keuangan');
    }

    public function media_bayar(){
        return $this->belongsTo(Master_media::class, 'bukti_bayar', 'id');
    }

    public function media_bayar_pph(){
        return $this->belongsTo(Master_media::class, 'bukti_bayar_pph', 'id');
    }

    public function usersig(){
        return $this->belongsTo(user::class, 'ttd_by', 'id');
    }
}
