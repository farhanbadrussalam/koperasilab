<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jawaban_lhu extends Model
{
    use HasFactory;

    protected $table = 'jawaban_lhu';
    protected $fillable = [
        'lhu_id',
        'pertanyaan_id',
        'jawaban',
        'created_by'
    ];
    protected $hidden = [
        'id'
    ];
    protected $appends = [
        'jawaban_lhu_hash'
    ];

    public function getJawabanLhuHashAttribute()
    {
        return encryptor($this->id);
    }

    public function pertanyaan(){
        return $this->belongsTo(pertanyaan_lhu::class, 'pertanyaan_id', 'id');
    }
}
