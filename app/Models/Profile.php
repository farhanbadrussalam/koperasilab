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
        'surat_kuasa'
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'avatar' => 'integer',
        'surat_kuasa' => 'integer'
    ];

    public function media(){
        return $this->belongsTo(Master_media::class, 'avatar', 'id');
    }

    public function suratkuasa(){
        return $this->belongsTo(Master_media::class, 'surat_kuasa', 'id');
    }
}
