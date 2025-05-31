<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Master_layanan_jasa extends Model
{
    use HasFactory;

    protected $table = 'master_layanan_jasa';

    protected $fillable = [
        'nama_layanan',
        'status',
        'jobs'
    ];

    protected $hidden = [
        'id_layanan'
    ];

    // Casting kolom sebagai array
    protected $casts = [
        'jobs' => 'array',
        'status' => 'integer'
    ];

    protected $appends = [
        'layanan_hash'
    ];

    public function getLayananHashAttribute()
    {
        return encryptor($this->id_layanan);
    }

    public function jobs_pelaksana(){
        return $this->hasMany(Master_jobs::class, 'id_layanan', 'id_layanan');
    }
}
