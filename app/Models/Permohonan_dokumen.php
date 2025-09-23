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
        'id_kontrak',
        'id_doc_template',
        'periode',
        'nomer',
        'nama',
        'status',
        'jenis',
        'ttd',
        'ttd_by',
        'catatan',
        'variables',
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
        'id_kontrak' => 'integer',
        'created_by' => 'integer',
        'id_doc_template' => 'integer',
        'content_value' => 'array',
        'variables' => 'array'
    ];

    public function getDokumenHashAttribute()
    {
        return $this->id_dokumen ? encryptor($this->id_dokumen) : null;
    }

    public function getPermohonanHashAttribute()
    {
        return $this->id_permohonan ? encryptor($this->id_permohonan) : null;
    }

    public function doc_template(){
        return $this->belongsTo(Documents::class, 'id_doc_template', 'id_doc');
    }

    public function usersig(){
        return $this->belongsTo(user::class, 'ttd_by', 'id');
    }
}
