<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perusahaan extends Model
{
    use HasFactory;

    protected $table = 'perusahaan';
    protected $primaryKey = 'id_perusahaan';

    protected $fillable = [
        'nama_perusahaan',
        'npwp_perusahaan',
        'kode_perusahaan',
        'email',
        'status',
        'surat_kuasa',
        'confirm_at',
    ];

    protected $hidden = [
        'id_perusahaan'
    ];

    protected $appends = [
        'perusahaan_hash'
    ];

    protected $casts = [
        'status' => 'integer',
        'id_perusahaan' => 'integer',
        'confirm_by' => 'integer'
    ];

    public function getPerusahaanHashAttribute()
    {
        return encryptor($this->id_perusahaan);
    }

    public function media(){
        return $this->belongsTo(tbl_media::class, 'surat_kuasa', 'id');
    }

    public function alamat(){
        return $this->hasMany(Master_alamat::class, 'id_perusahaan', 'id_perusahaan');
    }

    public function users(){
        return $this->hasMany(User::class, 'id_perusahaan', 'id_perusahaan');
    }
}
