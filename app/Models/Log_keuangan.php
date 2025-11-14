<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $id_keuangan
 * @property int|null $status
 * @property string|null $note
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $log_keuangan_hash
 * @method static \Illuminate\Database\Eloquent\Builder|Log_keuangan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Log_keuangan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Log_keuangan query()
 * @method static \Illuminate\Database\Eloquent\Builder|Log_keuangan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_keuangan whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_keuangan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_keuangan whereIdKeuangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_keuangan whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_keuangan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_keuangan whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Log_keuangan extends Model
{
    use HasFactory;
    protected $table = "log_keuangan";

    protected $fillable = [
        'id_keuangan',
        'status',
        'note',
        'created_by'
    ];

    protected $appends = [
        'log_keuangan_hash'
    ];

    protected $casts = [
        'status' => 'integer',
        'id_keuangan' => 'integer',
        'created_by' => 'integer',
        'id' => 'integer'
    ];

    public function getLogKeuanganHashAttribute()
    {
        return $this->id ? encryptor($this->id) : null;
    }
}
