<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Master_pengguna;
use App\Models\Master_divisi;

/**
 * @property int $id
 * @property int|null $id_permohonan
 * @property int|null $id_pengguna_divisi
 * @property int|null $id_tld
 * @property string|null $jenis
 * @property int|null $status
 * @property string|null $type
 * @property int|null $pengguna_lama
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|\Eloquent|null $entitas
 * @property-read mixed $permohonan_detail_hash
 * @property-read Master_pengguna|null $penggunaLama
 * @property-read \App\Models\Master_tld|null $tld
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_detail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_detail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_detail query()
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_detail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_detail whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_detail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_detail whereIdPenggunaDivisi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_detail whereIdPermohonan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_detail whereIdTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_detail whereJenis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_detail wherePenggunaLama($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_detail whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_detail whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_detail whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Permohonan_detail extends Model
{
    use HasFactory;

    protected static function booted()
    {
        Relation::morphMap([
            'pengguna' => Master_pengguna::class,
            'kontrol' => Master_divisi::class
        ]);
    }

    protected $table = 'permohonan_detail';
    protected $primaryKey = 'id';

    protected $guarded = [
        'id'
    ];

    protected $hidden = [
        'id'
    ];

    protected $appends = [
        'permohonan_detail_hash'
    ];

    protected $casts = [
        'id_permohonan' => 'integer',
        'id_pengguna_divisi' => 'integer',
        'id_divisi_selected' => 'integer',
        'id_tld' => 'integer',
        'status' => 'integer',
        'pengguna_lama' => 'integer',
        'created_by' => 'integer'
    ];

    public function getPermohonanDetailHashAttribute()
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

    public function tld()
    {
        return $this->hasOne(Master_tld::class, 'id_tld', 'id_tld')->withTrashed();
    }

    public function penggunaLama()
    {
        return $this->hasOne(Master_pengguna::class, 'id_pengguna', 'pengguna_lama')->withTrashed();
    }

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class, 'id_permohonan', 'id_permohonan');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }
}
