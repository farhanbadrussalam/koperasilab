<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Master_ttd extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'master_ttd';
    protected $guarded = ['id'];

    protected $casts = [
        'user_id' => 'integer',
        'status' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->withTrashed();
    }
}
