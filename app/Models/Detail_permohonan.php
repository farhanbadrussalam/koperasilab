<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Detail_permohonan extends Model
{
    use HasFactory;

    protected $table = "permohonan_log";

    protected $fillable = [
        'permohonan_id',
        'status',
        'flag',
        'note',
        'file',
        'created_by'
    ];

    protected $appends = [
        'permohonan_log_hash'
    ];

    public function getPermohonanLogHashAttribute()
    {
        return encryptor($this->id);
    }

    public function media(){
        return $this->belongsTo(tbl_media::class, 'file', 'id');
    }
}
