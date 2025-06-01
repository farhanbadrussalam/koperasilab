<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log_keuangan extends Model
{
    use HasFactory;
    protected $table = "log_keuangan";

    protected $fillable = [
        'id_keuangan',
        'status',
        'note',
        'created_by'
    ];

    protected $appends = [
        'log_keuangan_hash'
    ];

    protected $casts = [
        'status' => 'integer',
        'id_keuangan' => 'integer',
        'created_by' => 'integer',
        'id' => 'integer'
    ];

    public function getLogKeuanganHashAttribute()
    {
        return encryptor($this->id);
    }
}
