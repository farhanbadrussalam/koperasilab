<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jadwal_petugas extends Model
{
    use HasFactory;

    protected $table = 'jadwal_petugas';

    protected $fillable = [
        'jadwal_id',
        'petugas_id',
        'jobs',
        'status'
    ];

    protected $hidden = [
        'id',
        'jadwal_id',
        'petugas_id',
        'jobs'
    ];

    protected $appends = [
        'jadwalpetugas_hash',
        'avatar',
        'otorisasi'
    ];

    public $timestamps = false;

    public function getJadwalpetugasHashAttribute()
    {
        return encryptor($this->id);
    }

    // public function getAvatarAttribute()
    // {
    //     return getAvatar(encryptor($this->petugas_id));
    // }

    public function petugas(){
        return $this->belongsTo(User::class, 'petugas_id', 'id');
    }

    public function getOtorisasiAttribute(){
        $user = User::findOrFail($this->petugas_id);
        return $user->getDirectPermissions();
    }
}
