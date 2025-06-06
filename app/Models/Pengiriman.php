<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengiriman extends Model
{
    use HasFactory;

    protected $table = 'pengiriman';
    protected $primaryKey = 'id_pengiriman';
    protected $keyType = 'string';

    protected $fillable = [
        'id_pengiriman',
        'no_resi',
        'jenis_pengiriman',
        'id_ekspedisi',
        'id_permohonan',
        'id_kontrak',
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
        'id_permohonan',
        'bukti_pengiriman',
        'bukti_penerima'
    ];

    protected $appends = [
        'permohonan_hash'
    ];

    protected $casts = [
        'bukti_pengiriman' => 'array',
        'bukti_penerima' => 'array',
        'status' => 'integer',
        'periode' => 'integer',
        'id_ekspedisi' => 'integer',
        'id_permohonan' => 'integer',
        'id_kontrak' => 'integer',
        'tujuan' => 'integer',
        'alamat' => 'integer',
        'created_by' => 'integer'
    ];

    public function getPermohonanHashAttribute()
    {
        return encryptor($this->id_permohonan);
    }

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class, 'id_permohonan', 'id_permohonan');
    }

    public function detail(){
        return $this->hasMany(Pengiriman_detail::class, 'id_pengiriman', 'id_pengiriman');
    }

    public function alamat(){
        return $this->belongsTo(Master_alamat::class, 'alamat', 'id_alamat');
    }

    public function kontrak(){
        return $this->belongsTo(Kontrak::class, 'id_kontrak', 'id_kontrak');
    }

    public function ekspedisi(){
        return $this->belongsTo(Master_ekspedisi::class, 'id_ekspedisi', 'id_ekspedisi');
    }

    public function tujuan(){
        return $this->belongsTo(User::class, 'tujuan', 'id');
    }
}
