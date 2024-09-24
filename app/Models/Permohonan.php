<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permohonan extends Model
{
    use HasFactory;

    protected $table = "permohonan";

    protected $fillable = [
        'tipe_kontrak',
        'no_kontrak',
        'jenis_tld',
        'periode_pemakaian',
        'jumlah_pengguna',
        'jumlah_kontrol',
        'total_harga',
        'harga_layanan',
        'ttd',
        'ttd_by',
        'status',
        'note',
        'flag_read',
        'created_by',
        'created_at'
    ];

    protected $hidden = [
        'jenis_layanan_2',
        'jenis_layanan_1',
        'id_layanan',
        'id_permohonan'
    ];

    protected $appends = [
        'permohonan_hash'
    ];

    public function getPermohonanHashAttribute()
    {
        return encryptor($this->id_permohonan);
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

}
