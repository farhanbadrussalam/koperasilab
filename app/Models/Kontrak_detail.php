<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Master_pengguna;
use App\Models\Master_divisi;

/**
 * @property int $id
 * @property int|null $id_pengguna_divisi
 * @property int|null $id_kontrak
 * @property \App\Models\Master_tld|null $tld_1
 * @property int|null $status_tld_1
 * @property int|null $periode_tld_1
 * @property \App\Models\Master_tld|null $tld_2
 * @property int|null $status_tld_2
 * @property int|null $periode_tld_2
 * @property string|null $jenis
 * @property int|null $periode
 * @property int|null $status 1=active, 2=standby, 99=diganti
 * @property string|null $type
 * @property int|null $pengguna_lama
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|\Eloquent|null $entitas
 * @property-read mixed $kontrak_detail_hash
 * @property-read \App\Models\Kontrak|null $kontrak
 * @property-read Master_pengguna|null $penggunaLama
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_detail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_detail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_detail query()
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_detail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_detail whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_detail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_detail whereIdKontrak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_detail whereIdPenggunaDivisi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_detail whereJenis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_detail wherePenggunaLama($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_detail wherePeriode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_detail wherePeriodeTld1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_detail wherePeriodeTld2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_detail whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_detail whereStatusTld1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_detail whereStatusTld2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_detail whereTld1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_detail whereTld2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_detail whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_detail whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Kontrak_detail extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted()
    {
        Relation::morphMap([
            'pengguna' => Master_pengguna::class,
            'kontrol' => Master_divisi::class
        ]);
    }

    protected $table = 'kontrak_detail';
    protected $primaryKey = 'id';

    protected $guarded = [
        'id'
    ];

    protected $hidden = [
        'id'
    ];

    protected $casts = [
        'id_pengguna_divisi' => 'integer',
        'id_divisi_selected' => 'integer',
        'tld_1' => 'integer',
        'tld_2' => 'integer',
        'id_kontrak' => 'integer',
        'pengguna_lama' => 'integer',
        'status_tld_1' => 'integer',
        'status_tld_2' => 'integer',
        'periode_tld_1' => 'integer',
        'periode_tld_2' => 'integer',
        'periode' => 'integer',
        'created_by' => 'integer'
    ];

    protected $appends = [
        'kontrak_detail_hash'
    ];

    public function getKontrakDetailHashAttribute()
    {
        return $this->id ? encryptor($this->id) : null;
    }

    public function entitas()
    {
        return $this->morphTo(null, 'jenis', 'id_pengguna_divisi');
    }

    public function divisiSelected()
    {
        return $this->belongsTo(Master_divisi::class, 'id_divisi_selected', 'id_divisi')->withTrashed();
    }

    public function tld_1()
    {
        return $this->belongsTo(Master_tld::class, 'tld_1', 'id_tld')->withTrashed();
    }
    public function tld_awal()
    {
        return $this->belongsTo(Master_tld::class, 'tld_1', 'id_tld')->withTrashed();
    }

    public function tld_2()
    {
        return $this->belongsTo(Master_tld::class, 'tld_2', 'id_tld')->withTrashed();
    }
    public function tld_second()
    {
        return $this->belongsTo(Master_tld::class, 'tld_2', 'id_tld')->withTrashed();
    }

    public function kontrak()
    {
        return $this->belongsTo(Kontrak::class, 'id_kontrak', 'id_kontrak');
    }

    public function penggunaLama()
    {
        return $this->hasOne(Master_pengguna::class, 'id_pengguna', 'pengguna_lama')->withTrashed();
    }
}
