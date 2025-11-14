<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * @property-read mixed $permohonan_log_hash
 * @method static \Illuminate\Database\Eloquent\Builder|Detail_permohonan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Detail_permohonan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Detail_permohonan query()
 * @mixin \Eloquent
 */
class Detail_permohonan extends Model
{
    use HasFactory;

    protected $table = "permohonan_log";

    protected $fillable = [
        'permohonan_id',
        'status',
        'flag',
        'note',
        'file',
        'created_by'
    ];

    protected $appends = [
        'permohonan_log_hash'
    ];

    public function getPermohonanLogHashAttribute()
    {
        return encryptor($this->id);
    }
}
