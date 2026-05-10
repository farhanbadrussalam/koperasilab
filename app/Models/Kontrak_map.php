<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Master_pengguna;
use App\Models\Master_divisi;

class Kontrak_map extends Model
{
    use HasFactory;

    protected $table = 'kontrak_map';
    protected $primaryKey = 'id_map';

    protected $guarded = [
        'id_map'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'kontrak_map_hash'
    ];

    protected static function booted()
    {
        Relation::morphMap([
            'pengguna' => Master_pengguna::class,
            'kontrol' => Master_divisi::class
        ]);
    }

    public function getKontrakMapHashAttribute()
    {
        return $this->id_map ? encryptor($this->id_map) : null;
    }

    public function entitas()
    {
        return $this->morphTo(null, 'jenis', 'id_pengguna_divisi');
    }

    public function tld()
    {
        return $this->belongsTo(Master_tld::class, 'id_tld')->withTrashed();
    }
}
