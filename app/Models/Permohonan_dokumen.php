<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permohonan_dokumen extends Model
{
    use HasFactory;

    protected $table = 'permohonan_dokumen';
    protected $primaryKey = 'id_dokumen';

    protected $fillable = [
        'id_permohonan',
        'nomer',
        'nama',
        'status',
        'jenis',
        'created_by',
        'created_at',
    ];

    protected $hidden = [
        'updated_at',
        'id_dokumen'
    ];
    
    protected $appends = [
        'dokumen_hash',
        'permohonan_hash'
    ];

    public function getDokumenHashAttribute()
    {
        return encryptor($this->id_dokumen);
    }

    public function getPermohonanHashAttribute()
    {
        return encryptor($this->id_permohonan);
    }
}
