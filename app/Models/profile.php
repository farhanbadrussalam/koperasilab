<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'user_id',
        'avatar',
        'nik',
        'alamat',
        'no_hp',
        'jenis_kelamin',
    ];

    public function media(){
        return $this->belongsTo(tbl_media::class, 'avatar', 'id');
    }
}
