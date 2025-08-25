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
        'id_doc_template',
        'nomer',
        'nama',
        'status',
        'jenis',
        'ttd',
        'ttd_by',
        'catatan',
        'content_value',
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

    protected $casts = [
        'status' => 'integer',
        'id_dokumen' => 'integer',
        'id_permohonan' => 'integer',
        'created_by' => 'integer',
        'id_doc_template' => 'integer',
        'content_value' => 'array'
    ];

    public function getDokumenHashAttribute()
    {
        return encryptor($this->id_dokumen);
    }

    public function getPermohonanHashAttribute()
    {
        return encryptor($this->id_permohonan);
    }

    public function doc_template(){
        return $this->belongsTo(Documents::class, 'id_doc_template', 'id_doc');
    }
}
