<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tbl_lhu extends Model
{
    use HasFactory;

    protected $table = 'tbl_lhu';
    protected $fillable = [
        'no_kontrak',
        'level',
        'active',
        'surat_tugas',
        'document',
        'created_by'
    ];
    protected $hidden = [
        'id'
    ];
    protected $appends = [
        'lhu_hash'
    ];

    public function getLhuHashAttribute()
    {
        return encryptor($this->id);
    }

    public function media()
    {
        return $this->belongsTo(tbl_media::class, 'surat_tugas', 'id');
    }
}
