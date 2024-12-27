<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kontrak_periode extends Model
{
    use HasFactory;

    protected $table = "kontrak_periode";
    protected $primaryKey = 'id_periode';

    protected $fillable = [
        'id_kontrak',
        'periode',
        'id_permohonan',
        'start_date',
        'end_date',
        'status',
        'created_by',
        'created_at'
    ];

    protected $hidden = [
        'id_periode',
        'id_permohonan'
    ];

    protected $appends = [
        'periode_hash',
        'permohonan_hash'
    ];

    public function getPeriodeHashAttribute()
    {
        return encryptor($this->id_periode);
    }

    public function getPermohonanHashAttribute()
    {
        return encryptor($this->id_permohonan);
    }

    public function kontrak(){
        return $this->belongsTo(Kontrak::class,'id_kontrak', 'id_kontrak');
    }

    public function permohonan(){
        return $this->belongsTo(Permohonan::class,'id_permohonan', 'id_permohonan');
    }
}
