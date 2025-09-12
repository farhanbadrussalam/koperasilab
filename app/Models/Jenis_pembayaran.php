<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jenis_pembayaran extends Model
{
    use HasFactory;

    protected $table = 'jenis_pembayaran';
    protected $primaryKey = 'id_jenis_pembayaran';

    protected $fillable = [
        'id_jenis_pembayaran',
        'name',
        'content',
        'status',
        'variables',
        'created_at',
        'created_by',
        'updated_by'
    ];

    protected $hidden = [
        'id_jenis_pembayaran',
    ];

    protected $appends = [
        'jenis_pembayaran_hash',
    ];

    protected $casts = [
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'id_jenis_pembayaran' => 'integer',
        'variables' => 'json'
    ];

    public function getJenisPembayaranHashAttribute()
    {
        return encryptor($this->id_jenis_pembayaran);
    }
}
