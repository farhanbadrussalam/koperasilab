<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kontrak_pengguna extends Model
{
    use HasFactory;

    protected $table = "kontrak_pengguna";
    protected $primaryKey = "id_map_pengguna";

    protected $fillable = [
        'id_map_pengguna',
        'id_kontrak',
        'id_pengguna',
        'id_tld',
        'status',
        'created_by',
        'created_at'
    ];

    protected $hidden = [
        'id_map_pengguna'
    ];

    protected $appends = [
        'pengguna_map_hash'
    ];

    protected $casts = [
        'id_radiasi' => 'array'
    ];

    public function getPenggunaMapHashAttribute()
    {
        return encryptor($this->id_map_pengguna);
    }

    public function tld_pengguna(){
        return $this->belongsTo(Master_tld::class, 'id_tld', 'id_tld');
    }

    public function kontrak_tld(){
        return $this->belongsTo(Kontrak_tld::class, 'id_map_pengguna', 'id_map_pengguna');
    }

    public function pengguna(){
        return $this->belongsTo(Master_pengguna::class, 'id_pengguna', 'id_pengguna');
    }
}
