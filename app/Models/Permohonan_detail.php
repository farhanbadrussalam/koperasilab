<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Master_pengguna;
use App\Models\Master_divisi;

class Permohonan_detail extends Model
{
    use HasFactory;

    protected static function booted()
    {
        Relation::morphMap([
            'pengguna' => Master_pengguna::class,
            'kontrol' => Master_divisi::class
        ]);
    }

    protected $table = 'permohonan_detail';
    protected $primaryKey = 'id';

    protected $guarded = [
        'id'
    ];

    protected $hidden = [
        'id'
    ];

    protected $appends = [
        'permohonan_detail_hash'
    ];

    protected $casts = [
        'id_permohonan' => 'integer',
        'id_pengguna_divisi' => 'integer',
        'id_tld' => 'integer',
        'status' => 'integer',
        'pengguna_lama' => 'integer',
        'created_by' => 'integer'
    ];

    public function getPermohonanDetailHashAttribute()
    {
        return $this->id ? encryptor($this->id) : null;
    }

    public function entitas()
    {
        return $this->morphTo(null, 'jenis', 'id_pengguna_divisi');
    }

    public function tld()
    {
        return $this->hasOne(Master_tld::class, 'id_tld', 'id_tld');
    }

    public function penggunaLama()
    {
        return $this->hasOne(Master_pengguna::class, 'id_pengguna', 'pengguna_lama');
    }
}
