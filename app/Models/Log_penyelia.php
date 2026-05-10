<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $id_penyelia
 * @property int|null $id_map
 * @property int|null $status
 * @property string|null $message
 * @property string|null $note
 * @property string|null $document
 * @property int|null $flag
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $log_penyelia_hash
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Log_penyelia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Log_penyelia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Log_penyelia query()
 * @method static \Illuminate\Database\Eloquent\Builder|Log_penyelia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_penyelia whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_penyelia whereDocument($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_penyelia whereFlag($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_penyelia whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_penyelia whereIdMap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_penyelia whereIdPenyelia($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_penyelia whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_penyelia whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_penyelia whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_penyelia whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Log_penyelia extends Model
{
    use HasFactory;
    protected $table = "log_penyelia";

    protected $fillable = [
        'id_penyelia',
        'id_map',
        'status',
        'message',
        'note',
        'document',
        'created_by'
    ];

    protected $appends = [
        'log_penyelia_hash'
    ];

    protected $casts = [
        'status' => 'integer',
        'id_penyelia' => 'integer',
        'id_map' => 'integer',
        'created_by' => 'integer',
        'id' => 'integer'
    ];

    public function getLogPenyeliaHashAttribute()
    {
        return $this->id ? encryptor($this->id) : null;
    }

    public function user(){
        return $this->belongsTo(User::class, 'created_by', 'id')->withTrashed();
    }
}
