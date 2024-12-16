<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Master_media extends Model
{
    use HasFactory;

    protected $table = 'master_media';

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

    public function keuangan()
    {
        return $this->belongsTo(Keuangan::class);
    }
}
