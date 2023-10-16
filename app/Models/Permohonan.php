<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permohonan extends Model
{
    use HasFactory;

    protected $table = "permohonan";

    protected $fillable = [
        'layananjasa_id',
        'jenis_layanan',
        'tarif',
        'jadwal_id',
        'no_bapeten',
        'jenis_limbah',
        'sumber_radioaktif',
        'jumlah',
        'dokumen',
        'status',
        'flag',
        'tag',
        'nomor_antrian',
        'created_by'
    ];

    protected $hidden = [
        'id'
    ];

    protected $appends = [
        'permohonan_hash'
    ];

    public function getPermohonanHashAttribute()
    {
        return encryptor($this->id);
    }

    public function layananjasa(){
        return $this->belongsTo(Layanan_jasa::class);
    }

    public function jadwal(){
        return $this->belongsTo(jadwal::class);
    }

    public function user(){
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function media(){
        return $this->belongsTo(tbl_media::class, 'dokumen', 'id');
    }

    public function suratTerbit(){
        return $this->belongsTo(tbl_media::class, 'surat_terbitan', 'id');
    }
}
