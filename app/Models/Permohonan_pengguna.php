<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permohonan_pengguna extends Model
{
    use HasFactory;

    protected $table = "permohonan_pengguna";

    protected $primaryKey = "id_pengguna";

    protected $fillable = [
        'id_permohonan',
        'id_tld',
        'tld',
        'nama',
        'posisi',
        'id_radiasi',
        'file_ktp',
        'status',
        'keterangan',
        'created_by',
        'created_at'
    ];

    protected $hidden = [
        'id_pengguna',
        'id_radiasi',
        'id_tld'
    ];

    protected $appends = [
        'permohonan_pengguna_hash'
    ];

    protected $casts = [
        'id_radiasi' => 'array'
    ];

    public function getPermohonanPenggunaHashAttribute()
    {
        return encryptor($this->id_pengguna);
    }

    public function radiasi(){
        // return Master_radiasi::whereJsonContains('id_radiasi', $this->id_radiasi)->get();
        return $this->belongsTo(Master_radiasi::class, 'id_radiasi', 'id_radiasi');
    }

    public function media(){
        return $this->belongsTo(Master_media::class, 'file_ktp', 'id');
    }

    public function tld_pengguna(){
        return $this->belongsTo(Master_tld::class, 'id_tld', 'id_tld');
    }

    public function permohonan_tld(){
        return $this->belongsTo(Permohonan_tld::class, 'id_pengguna', 'id_pengguna');
    }
}
