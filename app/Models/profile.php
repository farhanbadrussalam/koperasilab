<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;
    protected $table = 'profiles';

    protected $fillable = [
        'id',
        'user_id',
        'avatar',
        'nik',
        'alamat',
        'no_hp',
        'jenis_kelamin',
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'avatar' => 'integer',
    ];

    public function media(){
        return $this->belongsTo(Master_media::class, 'avatar', 'id');
    }
}
