<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permohonan_tld extends Model
{
    use HasFactory;

    protected $table = 'permohonan_tld';
    protected $primaryKey = 'id_permohonan_tld';

    protected $fillable = [
        'id_permohonan_tld',
        'id_permohonan',
        'id_tld',
        'tld_tmp',
        'id_map_pengguna',
        'id_divisi',
        'periode',
        'created_by',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        'id_permohonan_tld',
        'id_permohonan',
        'id_tld',
    ];

    protected $appends = [
        'permohonan_tld_hash',
        'permohonan_hash'
    ];

    public function getPermohonanTldHashAttribute()
    {
        return encryptor($this->id_permohonan_tld);
    }

    public function getPermohonanHashAttribute()
    {
        return encryptor($this->id_permohonan);
    }

    public function tld()
    {
        return $this->belongsTo(Master_tld::class, 'id_tld', 'id_tld');
    }

    public function pengguna_map()
    {
        return $this->belongsTo(Permohonan_pengguna::class, 'id_map_pengguna', 'id_map_pengguna');
    }

    public function divisi()
    {
        return $this->belongsTo(Master_divisi::class, 'id_divisi', 'id_divisi');
    }
}
