<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Master_pengguna;
use App\Models\Master_divisi;

class Kontrak_detail extends Model
{
    use HasFactory;

    protected static function booted()
    {
        Relation::morphMap([
            'pengguna' => Master_pengguna::class,
            'kontrol' => Master_divisi::class
        ]);
    }

    protected $table = 'kontrak_detail';
    protected $primaryKey = 'id';

    protected $guarded = [
        'id'
    ];

    protected $hidden = [
        'id'
    ];

    protected $appends = [
        'kontrak_detail_hash'
    ];

    public function getKontrakDetailHashAttribute()
    {
        return $this->id ? encryptor($this->id) : null;
    }

    public function entitas()
    {
        return $this->morphTo(null, 'jenis', 'id_pengguna_divisi');
    }

    public function tld_1()
    {
        return $this->belongsTo(Master_tld::class, 'tld_1', 'id_tld');
    }

    public function tld_2()
    {
        return $this->belongsTo(Master_tld::class, 'tld_2', 'id_tld');
    }

    public function kontrak()
    {
        return $this->belongsTo(Kontrak::class, 'id_kontrak', 'id_kontrak');
    }

    public function penggunaLama()
    {
        return $this->hasOne(Master_pengguna::class, 'id_pengguna', 'pengguna_lama');
    }
}
