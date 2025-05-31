<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class notifikasi extends Model
{
    use HasFactory;

    protected $table = 'notifikasi';

    protected $fillable = [
        'recipient',
        'sender',
        'message',
        'type',
        'status'
    ];

    protected $casts = [
        'status' => 'integer'
    ];

    public function getRecipient(){
        return $this->hasOne(User::class, 'id', 'recipient');
    }

    public function getSender(){
        return $this->hasOne(User::class, 'id', 'sender');
    }
}
