<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $id_permohonan
 * @property int $status
 * @property int|null $flag
 * @property string|null $note
 * @property int|null $file
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $log_permohonan_hash
 * @method static \Illuminate\Database\Eloquent\Builder|Log_permohonan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Log_permohonan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Log_permohonan query()
 * @method static \Illuminate\Database\Eloquent\Builder|Log_permohonan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_permohonan whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_permohonan whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_permohonan whereFlag($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_permohonan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_permohonan whereIdPermohonan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_permohonan whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_permohonan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log_permohonan whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Log_permohonan extends Model
{
    use HasFactory;

    protected $table = "log_permohonan";

    protected $fillable = [
        'id_permohonan',
        'status',
        'flag',
        'note',
        'file',
        'created_by'
    ];

    protected $appends = [
        'log_permohonan_hash'
    ];

    protected $casts = [
        'status' => 'integer',
        'id_permohonan' => 'integer',
        'flag' => 'integer',
        'created_by' => 'integer',
        'id' => 'integer',
        'file' => 'integer'
    ];

    public function getLogPermohonanHashAttribute()
    {
        return $this->id ? encryptor($this->id) : null;
    }
}
