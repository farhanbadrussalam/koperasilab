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
        'id_permohonan'
    ];

    protected $appends = [
        'permohonan_hash'
    ];

    protected $casts = [
        'bukti_pengiriman' => 'array',
        'bukti_penerima' => 'array',
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
}
