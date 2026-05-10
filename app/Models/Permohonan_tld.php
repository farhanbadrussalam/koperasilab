<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_permohonan_tld
 * @property int|null $id_permohonan
 * @property array|null $id_tld
 * @property int|null $id_kontrak_tld
 * @property string|null $tld_tmp
 * @property int|null $count
 * @property int|null $id_pengguna
 * @property int|null $id_divisi
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Master_divisi|null $divisi
 * @property-read mixed $kontrak_tld_hash
 * @property-read mixed $permohonan_hash
 * @property-read mixed $permohonan_tld_hash
 * @property-read mixed $tld
 * @property-read \App\Models\Master_pengguna|null $pengguna
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tld newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tld newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tld query()
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tld whereCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tld whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tld whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tld whereIdDivisi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tld whereIdKontrakTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tld whereIdPengguna($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tld whereIdPermohonan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tld whereIdPermohonanTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tld whereIdTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tld whereTldTmp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tld whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Permohonan_tld extends Model
{
    use HasFactory;

    protected $table = 'permohonan_tld';
    protected $primaryKey = 'id_permohonan_tld';

    protected $fillable = [
        'id_permohonan_tld',
        'id_permohonan',
        'id_tld',
        'id_kontrak_tld',
        'tld_tmp',
        'count',
        'id_pengguna',
        'id_divisi',
        'periode',
        'created_by',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        'id_permohonan_tld',
        'id_permohonan',
        'id_kontrak_tld'
    ];

    protected $appends = [
        'permohonan_tld_hash',
        'permohonan_hash',
        'kontrak_tld_hash',
        'tld',
    ];

    protected $casts = [
        'id_tld' => 'json',
        'count' => 'integer',
        'id_permohonan_tld' => 'integer',
        'id_permohonan' => 'integer',
        'id_pengguna' => 'integer',
        'id_divisi' => 'integer',
        'created_by' => 'integer',
    ];

    public function getPermohonanTldHashAttribute()
    {
        return $this->id_permohonan_tld ? encryptor($this->id_permohonan_tld) : null;
    }

    public function getPermohonanHashAttribute()
    {
        return $this->id_permohonan ? encryptor($this->id_permohonan) : null;
    }

    public function getTldAttribute()
    {
        $decodedIds = $this->id_tld;
        $decodedIds = is_array($decodedIds) ? $decodedIds : [];
        $get = Master_tld::whereIn('id_tld', $decodedIds)->get();

        return count($get) > 0 ? $get : null;
    }

    public function getKontrakTldHashAttribute()
    {
        return $this->id_kontrak_tld ? encryptor($this->id_kontrak_tld) : null;
    }

    public function pengguna()
    {
        return $this->belongsTo(Master_pengguna::class, 'id_pengguna', 'id_pengguna')->withTrashed();
    }

    public function divisi()
    {
        return $this->belongsTo(Master_divisi::class, 'id_divisi', 'id_divisi')->withTrashed();
    }
}
