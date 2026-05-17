<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id_ekspedisi
 * @property string|null $name
 * @property string|null $deskripsi
 * @property int|null $status
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $ekspedisi_hash
 * @method static \Illuminate\Database\Eloquent\Builder|Master_ekspedisi newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_ekspedisi newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_ekspedisi query()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_ekspedisi whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_ekspedisi whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_ekspedisi whereDeskripsi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_ekspedisi whereIdEkspedisi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_ekspedisi whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_ekspedisi whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_ekspedisi whereUpdatedAt($value)
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|Master_ekspedisi onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_ekspedisi whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_ekspedisi withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_ekspedisi withoutTrashed()
 * @mixin \Eloquent
 */
class Master_ekspedisi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'master_ekspedisi';
    protected $primaryKey = 'id_ekspedisi';

    protected $fillable = [
        'name',
        'deskripsi',
        'status',
        'created_by'
    ];

    protected $hidden = [
        'id_ekspedisi',
    ];

    protected $appends = [
        'ekspedisi_hash'
    ];

    protected $casts = [
        'status' => 'integer',
        'created_by' => 'integer'
    ];

    public function getEkspedisiHashAttribute()
    {
        return $this->id_ekspedisi ? encryptor($this->id_ekspedisi) : null;
    }

    public function pengiriman()
    {
        return $this->hasMany(Pengiriman::class, 'id_ekspedisi', 'id_ekspedisi');
    }
}
