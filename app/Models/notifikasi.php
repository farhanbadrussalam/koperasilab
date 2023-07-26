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

    public function getRecipient(){
        return $this->hasOne(User::class, 'recipient', 'id');
    }

    public function getSender(){
        return $this->hasOne(User::class, 'sender', 'id');
    }
}
