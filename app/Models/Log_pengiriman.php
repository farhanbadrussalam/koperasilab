<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $id_pengiriman
 * @property int|null $status
 * @property string|null $note
 * @property int|null $media
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $log_pengiriman_hash
 * @method static \Illuminate\Database\Eloquent\Builder|Log_pengiriman newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Log_pengiriman newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Log_pengiriman query()
 * @method static \Illuminate\Database\Eloquent\Builder|Log_pengiriman whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_pengiriman whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_pengiriman whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_pengiriman whereIdPengiriman($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_pengiriman whereMedia($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_pengiriman whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_pengiriman whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_pengiriman whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Log_pengiriman extends Model
{
    use HasFactory;
    protected $table = "log_pengiriman";

    protected $fillable = [
        'id_pengiriman',
        'status',
        'note',
        'media',
        'created_by'
    ];

    protected $appends = [
        'log_pengiriman_hash'
    ];

    protected $casts = [
        'status' => 'integer',
        'id_pengiriman' => 'integer',
        'created_by' => 'integer',
        'media' => 'integer',
        'id' => 'integer'
    ];

    public function getLogPengirimanHashAttribute()
    {
        return $this->id ? encryptor($this->id) : null;
    }
}
