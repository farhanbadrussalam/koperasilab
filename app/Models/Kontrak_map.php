<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Master_pengguna;
use App\Models\Master_divisi;

/**
 * @property int $id_map
 * @property int $id_kontrak
 * @property int|null $id_kontrak_detail
 * @property int|null $id_pengguna_divisi
 * @property int|null $id_tld
 * @property string|null $jenis
 * @property int $periode
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|\Eloquent|null $entitas
 * @property-read mixed $kontrak_map_hash
 * @property-read \App\Models\Master_tld|null $tld
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_map newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_map newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_map query()
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_map whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_map whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_map whereIdKontrak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_map whereIdKontrakDetail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_map whereIdMap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_map whereIdPenggunaDivisi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_map whereIdTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_map whereJenis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_map wherePeriode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_map whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Kontrak_map extends Model
{
    use HasFactory;

    protected $table = 'kontrak_map';
    protected $primaryKey = 'id_map';

    protected $guarded = [
        'id_map'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'kontrak_map_hash'
    ];

    protected static function booted()
    {
        Relation::morphMap([
            'pengguna' => Master_pengguna::class,
            'kontrol' => Master_divisi::class
        ]);
    }

    public function getKontrakMapHashAttribute()
    {
        return $this->id_map ? encryptor($this->id_map) : null;
    }

    public function entitas()
    {
        return $this->morphTo(null, 'jenis', 'id_pengguna_divisi');
    }

    public function tld()
    {
        return $this->belongsTo(Master_tld::class, 'id_tld')->withTrashed();
    }
}
