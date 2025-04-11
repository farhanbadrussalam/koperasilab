<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kontrak extends Model
{
    use HasFactory;

    protected $table = "kontrak";
    protected $primaryKey = 'id_kontrak';

    protected $fillable = [
        'id_layanan',
        'id_keuangan',
        'jenis_layanan_2',
        'jenis_layanan_1',
        'tipe_kontrak',
        'no_kontrak',
        'jenis_tld',
        'jumlah_pengguna',
        'jumlah_kontrol',
        'total_harga',
        'harga_layanan',
        'list_tld',
        'ttd',
        'ttd_by',
        'status',
        'note',
        'file_lhu',
        'id_pelanggan',
        'created_by',
        'created_at'
    ];

    protected $hidden = [
        'id_kontrak'
    ];

    protected $appends = [
        'kontrak_hash'
    ];

    protected $casts = [
        'list_tld' => 'array',
    ];

    public function getKontrakHashAttribute()
    {
        return encryptor($this->id_kontrak);
    }

    public function jenisTld(){
        return $this->belongsTo(Master_jenistld::class,'jenis_tld', 'id_jenisTld');
    }
    
    public function jenis_layanan(){
        return $this->belongsTo(Master_jenisLayanan::class,'jenis_layanan_2', 'id_jenisLayanan');
    }
    
    public function jenis_layanan_parent(){
        return $this->belongsTo(Master_jenisLayanan::class,'jenis_layanan_1', 'id_jenisLayanan');
    }

    public function layanan_jasa() {
        return $this->belongsTo(Master_layanan_jasa::class, 'id_layanan', 'id_layanan');
    }

    public function pengguna() {
        return $this->hasMany(Kontrak_pengguna::class, 'id_kontrak', 'id_kontrak');
    }

    public function pelanggan() {
        return $this->belongsTo(User::class, 'id_pelanggan', 'id');
    }

    public function periode(){
        return $this->hasMany(Kontrak_periode::class, 'id_kontrak', 'id_kontrak');
    }

    public function pengiriman(){
        return $this->hasMany(Pengiriman::class, 'id_kontrak', 'id_kontrak');
    }

    public function invoice() {
        return $this->belongsTo(Keuangan::class, 'id_keuangan', 'id_keuangan');
    }

    public function rincian_list_tld(){
        return $this->hasMany(Kontrak_tld::class, 'id_kontrak', 'id_kontrak');
    }
}
