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
        'start_date',
        'end_date',
        'status',
        'created_by',
        'created_at'
    ];

    protected $hidden = [
        'id_periode'
    ];

    protected $appends = [
        'periode_hash'
    ];

    public function getPeriodeHashAttribute()
    {
        return encryptor($this->id_periode);
    }

    public function kontrak(){
        return $this->belongsTo(Kontrak::class,'id_kontrak', 'id_kontrak');
    }
}
