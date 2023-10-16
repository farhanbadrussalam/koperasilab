<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Detail_permohonan extends Model
{
    use HasFactory;

    protected $table = "detail_permohonan";

    protected $fillable = [
        'permohonan_id',
        'status',
        'flag',
        'note',
        'surat_terbitan',
        'created_by'
    ];

    protected $appends = [
        'detail_permohonan_hash'
    ];

    public function getDetailPermohonanHashAttribute()
    {
        return encryptor($this->id);
    }
}
