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
        'nomer_surpeng',
        'created_surpeng_at',
        'status',
        'count_tld',
        'created_by',
        'created_at'
    ];

    protected $hidden = [
        'id_periode',
        'id_permohonan'
    ];

    protected $appends = [
        'periode_hash',
        'permohonan_hash',
        'tld_in_periode'
    ];

    protected $casts = [
        'periode' => 'integer',
        'status' => 'integer',
        'created_by' => 'integer',
        'id_permohonan' => 'integer',
        'id_kontrak' => 'integer',
        'count_tld' => 'integer'
    ];

    public function getPeriodeHashAttribute()
    {
        return encryptor($this->id_periode);
    }

    public function getPermohonanHashAttribute()
    {
        return encryptor($this->id_permohonan);
    }

    public function getTldInPeriodeAttribute(){
        $idKontrak = $this->id_kontrak;
        $countTld = $this->count_tld;
        $get = Kontrak_tld::with('pengguna')->where('id_kontrak', $idKontrak)->where('count_tld', $countTld)->get();

        return count($get) > 0 ? $get : null;
    }

    public function kontrak(){
        return $this->belongsTo(Kontrak::class,'id_kontrak', 'id_kontrak');
    }

    public function permohonan(){
        return $this->belongsTo(Permohonan::class,'id_permohonan', 'id_permohonan');
    }

    public function penyelia() {
        return $this->belongsTo(Penyelia::class, 'id_permohonan', 'id_permohonan');
    }

    public function getTldInPeriode(){
        return $this->hasMany(Kontrak_tld::class, 'id_periode', 'id_periode');
    }
}
