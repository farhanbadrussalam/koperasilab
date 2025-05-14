<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log_tld extends Model
{
    use HasFactory;

    protected $table = 'log_tld';
    protected $primaryKey = 'id_log_tld';

    protected $fillable = [
        'id_log_tld',
        'id_tld',
        'status',
        'message',
        'note',
        'created_by',
        'created_at',
        'updated_at'
    ];

    protected $appends = [
        'log_tld_hash'
    ];

    public function getLogTldHashAttribute()
    {
        return encryptor($this->id_log_tld);
    }

}
