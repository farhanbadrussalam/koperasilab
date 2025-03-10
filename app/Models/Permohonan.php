<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permohonan extends Model
{
    use HasFactory;

    protected $table = "permohonan";
    protected $primaryKey = 'id_permohonan';

    protected $fillable = [
        'id_layanan',
        'jenis_layanan_2',
        'jenis_layanan_1',
        'id_kontrak',
        'id_pengiriman',
        'id_alamat',
        'tipe_kontrak',
        'no_kontrak',
        'jenis_tld',
        'periode_pemakaian',
        'periode',
        'jumlah_pengguna',
        'jumlah_kontrol',
        'total_harga',
        'harga_layanan',
        'list_tld',
        'pic',
        'no_hp',
        'ttd',
        'ttd_by',
        'status',
        'note',
        'file_lhu',
        'flag_read',
        'created_by',
        'created_at',
        'verify_at'
    ];

    protected $hidden = [
        'jenis_layanan_2',
        'jenis_layanan_1',
        'id_layanan',
        'id_permohonan',
        'list_tld',
        'id_kontrak',
    ];

    protected $appends = [
        'permohonan_hash',
        'kontrak_hash'
    ];

    protected $casts = [
        'periode_pemakaian' => 'array',
        'list_tld' => 'array',
    ];

    public function getPermohonanHashAttribute()
    {
        return encryptor($this->id_permohonan);
    }

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
        return $this->hasMany(Permohonan_pengguna::class, 'id_permohonan', 'id_permohonan');
    }

    public function pelanggan() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function tandaterima() {
        return $this->hasMany(Permohonan_tandaterima::class, 'id_permohonan', 'id_permohonan');
    }
    
    public function kontrak(){
        return $this->belongsTo(Kontrak::class, 'id_kontrak', 'id_kontrak');
    }

    public function invoice(){
        return $this->hasOne(Keuangan::class, 'id_permohonan', 'id_permohonan');
    }

    public function lhu(){
        return $this->hasOne(Penyelia::class, 'id_permohonan', 'id_permohonan');
    }

    public function pengiriman(){
        return $this->belongsTo(Pengiriman::class, 'id_pengiriman', 'id_pengiriman');
    }

    public function file_lhu(){
        return $this->hasOne(Master_media::class, 'id', 'file_lhu');
    }

    public function dokumen(){
        return $this->hasMany(Permohonan_dokumen::class, 'id_permohonan', 'id_permohonan');
    }

    public function signature(){
        return $this->belongsTo(User::class, 'ttd_by', 'id');
    }

    public function periodenow(){
        return $this->belongsTo(Kontrak_periode::class, 'id_permohonan', 'id_permohonan');
    }
}
