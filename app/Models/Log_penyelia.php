<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log_penyelia extends Model
{
    use HasFactory;
    protected $table = "log_penyelia";

    protected $fillable = [
        'id_penyelia',
        'id_map',
        'status',
        'message',
        'note',
        'document',
        'created_by'
    ];

    protected $appends = [
        'log_penyelia_hash'
    ];

    protected $casts = [
        'status' => 'integer',
        'id_penyelia' => 'integer',
        'id_map' => 'integer',
        'created_by' => 'integer',
        'id' => 'integer'
    ];

    public function getLogPenyeliaHashAttribute()
    {
        return encryptor($this->id);
    }

    public function user(){
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
