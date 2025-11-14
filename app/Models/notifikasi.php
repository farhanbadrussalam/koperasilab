<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $recipient
 * @property int $sender
 * @property string $message
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $status
 * @method static \Illuminate\Database\Eloquent\Builder|notifikasi newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|notifikasi newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|notifikasi query()
 * @method static \Illuminate\Database\Eloquent\Builder|notifikasi whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|notifikasi whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|notifikasi whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|notifikasi whereRecipient($value)
 * @method static \Illuminate\Database\Eloquent\Builder|notifikasi whereSender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|notifikasi whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|notifikasi whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|notifikasi whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
        'status' => 'integer',
        'recipient' => 'integer',
        'sender' => 'integer',
        'id' => 'integer'
    ];

    public function getRecipient(){
        return $this->hasOne(User::class, 'id', 'recipient');
    }

    public function getSender(){
        return $this->hasOne(User::class, 'id', 'sender');
    }
}
