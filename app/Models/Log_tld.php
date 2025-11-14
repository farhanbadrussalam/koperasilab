<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_log_tld
 * @property int|null $id_tld
 * @property int|null $status
 * @property string|null $message
 * @property string|null $note
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $log_tld_hash
 * @method static \Illuminate\Database\Eloquent\Builder|Log_tld newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Log_tld newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Log_tld query()
 * @method static \Illuminate\Database\Eloquent\Builder|Log_tld whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_tld whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_tld whereIdLogTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_tld whereIdTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_tld whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_tld whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_tld whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_tld whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Log_tld extends Model
{
    use HasFactory;

    protected $table = 'log_tld';
    protected $primaryKey = 'id_log_tld';

    protected $fillable = [
        'id_log_tld',
        'id_tld',
        'status',
        'message',
        'note',
        'created_by',
        'created_at',
        'updated_at'
    ];

    protected $appends = [
        'log_tld_hash'
    ];

    protected $casts = [
        'status' => 'integer',
        'created_by' => 'integer',
        'id_log_tld' => 'integer',
        'id_tld' => 'integer'
    ];

    public function getLogTldHashAttribute()
    {
        return $this->id_log_tld ? encryptor($this->id_log_tld) : null;
    }

}
