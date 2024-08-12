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
        'no_kontrak',
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
        'surat_tugas',
        'ttd_1',
        'ttd_1_by',
        'ttd_2',
        'ttd_2_by',
        'created_by'
    ];

    protected $hidden = [
        'id'
    ];

    protected $appends = [
        'permohonan_hash',
        'progress'
    ];

    public function getPermohonanHashAttribute()
    {
        return encryptor($this->id);
    }

    public function getProgressAttribute(){
        return Detail_permohonan::where('permohonan_id', $this->id)->orderBy('created_at', 'DESC')->first();
    }

    public function layananjasa(){
        return $this->belongsTo(Layanan_jasa::class);
    }

    public function jadwal(){
        return $this->belongsTo(jadwal::class, 'id', 'permohonan_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function media(){
        return $this->belongsTo(tbl_media::class, 'dokumen', 'id');
    }

    public function tbl_lhu(){
        return $this->belongsTo(tbl_lhu::class, 'id', 'id_jadwal');
    }

    public function tbl_kip(){
        return $this->belongsTo(tbl_kip::class, 'id', 'id_permohonan');
    }

    public function signature_1(){
        return $this->belongsTo(User::class, 'ttd_1_by', 'id');
    }
    public function signature_2(){
        return $this->belongsTo(User::class, 'ttd_2_by', 'id');
    }

}
