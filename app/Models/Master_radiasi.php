<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Master_radiasi extends Model
{
    use HasFactory;

    protected $table = 'master_radiasi';

    protected $fillable = [
        'nama_radiasi',
        'status'
    ];

    protected $hidden = [
        'id_radiasi'
    ];

    protected $appends = [
        'radiasi_hash'
    ];

    public function getRadiasiHashAttribute()
    {
        return encryptor($this->id_radiasi);
    }
}
