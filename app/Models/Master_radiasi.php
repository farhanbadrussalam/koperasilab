<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Master_radiasi extends Model
{
    use HasFactory;

    protected $table = 'master_radiasi';
    protected $primaryKey = 'id_radiasi';
    public $timestamps = false;

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

    protected $casts = [
        'status' => 'integer',
        'id_radiasi' => 'integer'
    ];

    public function getRadiasiHashAttribute()
    {
        return encryptor($this->id_radiasi);
    }
}
