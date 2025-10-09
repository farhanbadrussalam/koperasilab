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
        'perusahaan_hash',
        'pic',
    ];

    protected $casts = [
        'status' => 'integer',
        'id_perusahaan' => 'integer',
        'confirm_by' => 'integer',
        'surat_kuasa' => 'integer',
    ];

    public function getPerusahaanHashAttribute()
    {
        return $this->id_perusahaan ? encryptor($this->id_perusahaan) : null;
    }

    public function getPicAttribute()
    {
        return $this->users()->where('status', '1')->first();
    }

    public function alamat(){
        return $this->hasMany(Master_alamat::class, 'id_perusahaan', 'id_perusahaan');
    }

    public function users(){
        return $this->hasMany(User::class, 'id_perusahaan', 'id_perusahaan');
    }

    public function suratkuasa(){
        return $this->hasMany(Master_media::class, 'id', 'surat_kuasa');
    }
}
