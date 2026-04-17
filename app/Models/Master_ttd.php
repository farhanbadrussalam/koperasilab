<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Master_ttd extends Model
{
    use HasFactory;
    protected $table = 'master_ttd';
    protected $guarded = ['id'];

    protected $casts = [
        'user_id' => 'integer',
        'status' => 'integer',
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
