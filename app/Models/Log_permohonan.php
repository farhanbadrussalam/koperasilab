<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log_permohonan extends Model
{
    use HasFactory;

    protected $table = "log_permohonan";

    protected $fillable = [
        'id_permohonan',
        'status',
        'flag',
        'note',
        'file',
        'created_by'
    ];

    protected $appends = [
        'log_permohonan_hash'
    ];

    public function getLogPermohonanHashAttribute()
    {
        return encryptor($this->id);
    }

    public function media(){
        return $this->belongsTo(tbl_media::class, 'file', 'id');
    }
}
