<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permohonan_tandaterima extends Model
{
    use HasFactory;

    protected $table = "permohonan_tandaterima";

    protected $fillable = [
        'id_permohonan',
        'id_pertanyaan',
        'jawaban',
        'note',
        'created_by'
    ];

    protected $hidden = [
        'id_permohonan',
        'id_pertanyaan'
    ];

    protected $appends = [
        'permohonan_hash',
        'pertanyaan_hash'
    ];

    protected $casts = [
        'id_permohonan' => 'integer',
        'id_pertanyaan' => 'integer',
        'created_by' => 'integer'
    ];

    public function getPermohonanHashAttribute()
    {
        return encryptor($this->id_permohonan);
    }

    public function getPertanyaanHashAttribute()
    {
        return encryptor($this->id_pertanyaan);
    }

    public function pertanyaan(){
        return $this->belongsTo(Master_pertanyaan::class, 'id_pertanyaan', 'id_pertanyaan');
    }
}
