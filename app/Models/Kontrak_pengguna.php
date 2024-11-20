<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kontrak_pengguna extends Model
{
    use HasFactory;

    protected $table = "kontrak_pengguna";

    protected $fillable = [
        'id_kontrak',
        'nama',
        'posisi',
        'id_radiasi',
        'file_ktp',
        'status',
        'created_by',
        'created_at'
    ];

    protected $hidden = [
        'id_pengguna',
        'id_radiasi'
    ];

    protected $appends = [
        'permohonan_pengguna_hash'
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
}
