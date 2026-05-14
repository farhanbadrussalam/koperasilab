<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id_radiasi
 * @property string|null $nama_radiasi
 * @property int|null $status
 * @property-read mixed $radiasi_hash
 * @method static \Illuminate\Database\Eloquent\Builder|Master_radiasi newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_radiasi newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_radiasi query()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_radiasi whereIdRadiasi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_radiasi whereNamaRadiasi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_radiasi whereStatus($value)
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|Master_radiasi onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_radiasi whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_radiasi withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_radiasi withoutTrashed()
 * @mixin \Eloquent
 */
class Master_radiasi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'master_radiasi';
    protected $primaryKey = 'id_radiasi';
    public $timestamps = false;

    protected $fillable = [
        'nama_radiasi',
        'status'
    ];

    protected $hidden = [
        'id_radiasi'
    ];

    protected $appends = [
        'radiasi_hash'
    ];

    protected $casts = [
        'status' => 'integer',
        'id_radiasi' => 'integer'
    ];

    public function getRadiasiHashAttribute()
    {
        return $this->id_radiasi ? encryptor($this->id_radiasi) : null;
    }
}
