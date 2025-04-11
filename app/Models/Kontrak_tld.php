<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kontrak_tld extends Model
{
    use HasFactory;

    protected $table = 'kontrak_tld';

    protected $primaryKey = 'id_kontrak_tld';

    protected $fillable = [
        'id_kontrak',
        'id_tld',
        'id_pengguna',
        'periode',
        'status',
        'created_by'
    ];

    protected $hidden = [
        'id_kontrak',
        'id_tld',
        'id_pengguna',
        'created_by'
    ];

    protected $appends = [
        'kontrak_tld_hash',
        'kontrak_hash'
    ];

    public function getKontrakTldHashAttribute()
    {
        return encryptor($this->id_kontrak_tld);
    }

    public function getKontrakHashAttribute()
    {
        return encryptor($this->id_kontrak);
    }

    public function tld()
    {
        return $this->belongsTo(Master_tld::class, 'id_tld', 'id_tld');
    }

    public function pengguna()
    {
        return $this->belongsTo(Kontrak_pengguna::class, 'id_pengguna', 'id_pengguna');
    }
}
