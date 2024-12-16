<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penyelia_petugas extends Model
{
    use HasFactory;
    
    protected $table = 'penyelia_petugas';
    protected $primaryKey = 'id_petugas';
    
    protected $fillable = [
        'id_user',
        'id_map',
        'id_penyelia',
        'status',
        'created_by'
    ];

    protected $hidden = [
        'id_petugas',
        'id_map',
        'id_penyelia',
        'id_user'
    ];

    protected $appends = [
        'petugas_hash',
        'map_hash',
        'penyelia_hash',
        'user_hash'
    ];

    public function getPetugasHashAttribute()
    {
        return encryptor($this->id_petugas);
    }

    public function getMapHashAttribute()
    {
        return encryptor($this->id_map);
    }

    public function getPenyeliaHashAttribute()
    {
        return encryptor($this->id_penyelia);
    }

    public function getUserHashAttribute()
    {
        return encryptor($this->id_user);
    }

    public function jobs()
    {
        return $this->belongsTo(Penyelia_map::class, 'id_map', 'id_map');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
}
