<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penyelia extends Model
{
    use HasFactory;

    protected $table = 'penyelia';
    protected $primaryKey = 'id_penyelia';
    
    protected $fillable = [
        'id_permohonan',
        'start_date',
        'end_date',
        'periode',
        'status',
        'ttd',
        'ttd_by',
        'petugas',
        'document',
        'created_by',
        'created_at'
    ];

    protected $hidden = [
        'id_penyelia',
        'id_permohonan'
    ];

    protected $appends = [
        'penyelia_hash',
        'permohonan_hash'
    ];

    public function getPermohonanHashAttribute()
    {
        return encryptor($this->id_permohonan);
    }

    public function getPenyeliaHashAttribute()
    {
        return encryptor($this->id_penyelia);
    }

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class, 'id_permohonan', 'id_permohonan');
    }

    public function usersig(){
        return $this->belongsTo(User::class, 'ttd_by', 'id');
    }

    public function log(){
        return $this->hasMany(Log_penyelia::class, 'id_penyelia', 'id_penyelia');
    }

    public function media(){
        return $this->belongsTo(Master_media::class, 'document', 'id');
    }

    public function petugas(){
        return $this->hasMany(Penyelia_petugas::class, 'id_penyelia', 'id_penyelia');
    }

    public function penyelia_map(){
        return $this->hasMany(Penyelia_map::class, 'id_penyelia', 'id_penyelia')->orderBy('order', 'asc');
    }

    public function pengiriman(){
        return $this->belongsTo(Pengiriman::class, 'id_pengiriman', 'id_pengiriman');
    }
}
