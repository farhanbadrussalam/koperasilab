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
        'status'
    ];
}