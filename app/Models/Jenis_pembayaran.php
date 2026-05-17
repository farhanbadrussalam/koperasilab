<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id_jenis_pembayaran
 * @property int|null $id_satuankerja
 * @property string|null $name
 * @property string|null $content
 * @property int|null $status 1 = aktif, 0 = tidak aktif
 * @property array|null $variables
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $jenis_pembayaran_hash
 * @method static \Illuminate\Database\Eloquent\Builder|Jenis_pembayaran newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Jenis_pembayaran newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Jenis_pembayaran query()
 * @method static \Illuminate\Database\Eloquent\Builder|Jenis_pembayaran whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jenis_pembayaran whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jenis_pembayaran whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jenis_pembayaran whereIdJenisPembayaran($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jenis_pembayaran whereIdSatuankerja($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jenis_pembayaran whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jenis_pembayaran whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jenis_pembayaran whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jenis_pembayaran whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jenis_pembayaran whereVariables($value)
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|Jenis_pembayaran onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Jenis_pembayaran whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jenis_pembayaran withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Jenis_pembayaran withoutTrashed()
 * @mixin \Eloquent
 */
class Jenis_pembayaran extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'jenis_pembayaran';
    protected $primaryKey = 'id_jenis_pembayaran';

    protected $fillable = [
        'id_jenis_pembayaran',
        'name',
        'content',
        'status',
        'variables',
        'created_at',
        'created_by',
        'updated_by'
    ];

    protected $hidden = [
        'id_jenis_pembayaran',
    ];

    protected $appends = [
        'jenis_pembayaran_hash',
    ];

    protected $casts = [
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'id_jenis_pembayaran' => 'integer',
        'variables' => 'json'
    ];

    public function getJenisPembayaranHashAttribute()
    {
        return $this->id_jenis_pembayaran ? encryptor($this->id_jenis_pembayaran) : null;
    }
}
