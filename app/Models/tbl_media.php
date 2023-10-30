<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tbl_media extends Model
{
    use HasFactory;

    protected $table = 'tbl_media';

    protected $fillable = [
        'file_hash',
        'file_ori',
        'file_size',
        'file_type',
        'file_path',
        'status'
    ];

    protected $hidden = [
        'id'
    ];

    protected $appends = [
        'media_hash'
    ];

    public function getMediaHashAttribute()
    {
        return encryptor($this->id);
    }
}
