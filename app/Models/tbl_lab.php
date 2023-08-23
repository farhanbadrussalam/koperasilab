<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tbl_lab extends Model
{
    use HasFactory;
    protected $table = 'tbl_lab';
    protected $fillable = [
        'name_lab'
    ];
}
