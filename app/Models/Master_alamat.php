<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Master_alamat extends Model
{
    use HasFactory;

    protected $table = 'master_alamat';
    protected $primaryKey = 'id_alamat';

    protected $fillable = [
        'id_perusahaan',
        'alamat',
        'jenis',
        'kode_pos',
        'status',
        'created_at'
    ];

    protected $hidden = [
        'id_alamat',
    ];

    protected $appends = [
        'alamat_hash'
    ];

    protected $casts = [
        'status' => 'integer'
    ];

    public function getAlamatHashAttribute()
    {
        return encryptor($this->id_alamat);
    }
}
