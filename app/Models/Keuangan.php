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
        'id_jenis_pembayaran',
        'variabel_jenis_pembayaran',
        'no_invoice',
        'status',
        'ppn',
        'pph',
        'document_faktur',
        'bukti_bayar',
        'bukti_bayar_pph',
        'ttd',
        'ttd_by',
        'plt',
        'total_harga',
        'created_at',
        'paid_at',
        'created_by'
    ];

    // Casting kolom sebagai array
    protected $casts = [
        'document_faktur' => 'array',
        'bukti_bayar' => 'array',
        'bukti_bayar_pph' => 'array',
        'status' => 'integer',
        'id_keuangan' => 'integer',
        'id_permohonan' => 'integer',
        'id_jenis_pembayaran' => 'integer',
        'variabel_jenis_pembayaran' => 'array',
        'ppn' => 'integer',
        'pph' => 'integer',
        'ttd_by' => 'integer',
        'total_harga' => 'integer',
        'created_by' => 'integer'
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
        'permohonan_hash',
        'media',
        'media_bukti_bayar',
        'media_bukti_bayar_pph'
    ];

    public function getKeuanganHashAttribute()
    {
        return $this->id_keuangan ? encryptor($this->id_keuangan) : null;
    }

    public function getPermohonanHashAttribute()
    {
        return encryptor($this->id_permohonan);
    }

    public function getMediaAttribute()
    {
        $decodedIds = $this->document_faktur;
        $decodedIds = is_array($decodedIds) ? $decodedIds : [];

        return Master_media::whereIn('id', $decodedIds)->get();
    }

    public function getMediaBuktiBayarAttribute()
    {
        $decodedIds = $this->bukti_bayar;
        $decodedIds = is_array($decodedIds) ? $decodedIds : [];

        return Master_media::whereIn('id', $decodedIds)->get();
    }

    public function getMediaBuktiBayarPphAttribute()
    {
        $decodedIds = $this->bukti_bayar_pph;
        $decodedIds = is_array($decodedIds) ? $decodedIds : [];

        return Master_media::whereIn('id', $decodedIds)->get();
    }

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class, 'id_permohonan', 'id_permohonan');
    }

    public function diskon()
    {
        return $this->hasMany(Keuangan_diskon::class, 'id_keuangan', 'id_keuangan');
    }

    // public function media_bayar(){
    //     return $this->belongsTo(Master_media::class, 'bukti_bayar', 'id');
    // }

    // public function media_bayar_pph(){
    //     return $this->belongsTo(Master_media::class, 'bukti_bayar_pph', 'id');
    // }

    public function usersig(){
        return $this->belongsTo(user::class, 'ttd_by', 'id');
    }

    public function pengiriman(){
        return $this->belongsTo(Pengiriman::class, 'id_pengiriman', 'id_pengiriman');
    }

    public function metode_pembayaran(){
        return $this->belongsTo(Jenis_pembayaran::class, 'id_jenis_pembayaran', 'id_jenis_pembayaran');
    }
}
