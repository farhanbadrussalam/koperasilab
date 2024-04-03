<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class jadwal extends Model
{
    use HasFactory;

    protected $table = 'jadwal';

    protected $fillable = [
        'layananjasa_id',
        'jenislayanan',
        'tarif',
        'permohonan_id',
        'date_mulai',
        'date_selesai',
        'ttd_1',
        'ttd_1_by',
        'kuota',
        'dokumen',
        'status',
        'created_by'
    ];

    protected $hidden = [
        'id'
    ];

    protected $appends = [
        'jadwal_hash'
    ];

    public function getJadwalHashAttribute()
    {
        return encryptor($this->id);
    }

    public function petugas(){
        return $this->hasMany(Jadwal_petugas::class, 'jadwal_id', 'id');
    }

    public function layananjasa(){
        return $this->belongsTo(Layanan_jasa::class, 'layananjasa_id', 'id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function media(){
        return $this->belongsTo(tbl_media::class, 'dokumen', 'id');
    }

    public function permohonan(){
        return $this->belongsTo(Permohonan::class, 'permohonan_id', 'id');
    }

    public function signature_1(){
        return $this->belongsTo(User::class, 'ttd_1_by', 'id');
    }

    public function tbl_lhu(){
        return $this->belongsTo(tbl_lhu::class, 'id', 'id_jadwal');
    }
}
