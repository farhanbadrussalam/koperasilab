<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penyelia extends Model
{
    use HasFactory;

    protected $table = 'penyelia';
    protected $primaryKey = 'id_penyelia';
    
    protected $fillable = [
        'id_permohonan',
        'start_date',
        'end_date',
        'status',
        'ttd',
        'ttd_by',
        'petugas',
        'created_by',
        'created_at'
    ];

    protected $hidden = [
        'id_penyelia',
        'id_permohonan'
    ];

    protected $appends = [
        'penyelia_hash',
        'permohonan_hash'
    ];

    public function getPermohonanHashAttribute()
    {
        return encryptor($this->id_permohonan);
    }

    public function getPenyeliaHashAttribute()
    {
        return encryptor($this->id_penyelia);
    }

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class, 'id_permohonan', 'id_permohonan');
    }

    public function usersig(){
        return $this->belongsTo(User::class, 'ttd_by', 'id');
    }
}
