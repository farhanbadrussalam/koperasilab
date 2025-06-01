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
        'count',
        'id_pengguna',
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

    protected $casts = [
        'id_tld' => 'json',
        'count' => 'integer',
        'id_permohonan_tld' => 'integer',
        'id_permohonan' => 'integer',
        'id_pengguna' => 'integer',
        'id_divisi' => 'integer',
        'created_by' => 'integer',
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

    public function pengguna()
    {
        return $this->belongsTo(Master_pengguna::class, 'id_pengguna', 'id_pengguna');
    }

    public function divisi()
    {
        return $this->belongsTo(Master_divisi::class, 'id_divisi', 'id_divisi');
    }
}
