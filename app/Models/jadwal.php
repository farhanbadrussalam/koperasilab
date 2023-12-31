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
        'date_mulai',
        'date_selesai',
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
}
