<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Master_pertanyaan extends Model
{
    use HasFactory;

    protected $table = 'master_pertanyaan';
    protected $primaryKey = 'id_pertanyaan';

    protected $fillable = [
        'id_layananjasa',
        'pertanyaan',
        'type',
        'mandatory'
    ];
    protected $hidden = [
        'id_pertanyaan',
    ];
    protected $appends = [
        'pertanyaan_hash'
    ];

    protected $casts = [
        'type' => 'integer',
        'mandatory' => 'integer',
        'id_layananjasa' => 'integer',
        'id_pertanyaan' => 'integer',
    ];

    public function getPertanyaanHashAttribute()
    {
        return encryptor($this->id_pertanyaan);
    }
}
