<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Master_ekspedisi extends Model
{
    use HasFactory;

    protected $table = 'master_ekspedisi';
    protected $primaryKey = 'id_ekspedisi';

    protected $fillable = [
        'name',
        'deskripsi',
        'status',
        'created_by'
    ];

    protected $hidden = [
        'id_ekspedisi',
    ];

    protected $appends = [
        'ekspedisi_hash'
    ];

    protected $casts = [
        'status' => 'integer'
    ];

    public function getEkspedisiHashAttribute()
    {
        return encryptor($this->id_ekspedisi);
    }

}
