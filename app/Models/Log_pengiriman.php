<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log_pengiriman extends Model
{
    use HasFactory;
    protected $table = "log_pengiriman";

    protected $fillable = [
        'id_pengiriman',
        'status',
        'note',
        'media',
        'created_by'
    ];

    protected $appends = [
        'log_pengiriman_hash'
    ];

    protected $casts = [
        'status' => 'integer'
    ];

    public function getLogPengirimanHashAttribute()
    {
        return encryptor($this->id);
    }
}
