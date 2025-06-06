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

    protected $casts = [
        'status' => 'integer',
        'id_permohonan' => 'integer',
        'flag' => 'integer',
        'created_by' => 'integer',
        'id' => 'integer',
        'file' => 'integer'
    ];

    public function getLogPermohonanHashAttribute()
    {
        return encryptor($this->id);
    }

    public function media(){
        return $this->belongsTo(tbl_media::class, 'file', 'id');
    }
}
