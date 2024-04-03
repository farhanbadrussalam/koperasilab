<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class pertanyaan_lhu extends Model
{
    use HasFactory;

    protected $table = 'pertanyaan_lhu';
    protected $fillable = [
        'title',
        'type'
    ];
    protected $hidden = [
        'id'
    ];
    protected $appends = [
        'pertanyaan_lhu_hash'
    ];

    public function getPertanyaanLhuHashAttribute()
    {
        return encryptor($this->id);
    }
}
