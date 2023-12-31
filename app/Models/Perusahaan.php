<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perusahaan extends Model
{
    use HasFactory;

    protected $table = 'perusahaan';

    protected $fillable = [
        'user_id',
        'npwp',
        'name',
        'email',
        'surat_kuasa',
        'alamat',
    ];

    public function media(){
        return $this->belongsTo(tbl_media::class, 'surat_kuasa', 'id');
    }
}
