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
