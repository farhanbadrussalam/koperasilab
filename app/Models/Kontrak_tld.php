<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_kontrak_tld
 * @property int|null $id_kontrak
 * @property array|null $id_tld
 * @property int|null $count
 * @property int|null $id_pengguna
 * @property int|null $id_divisi
 * @property int|null $count_tld
 * @property int|null $status
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Master_divisi|null $divisi
 * @property-read mixed $kontrak_hash
 * @property-read mixed $kontrak_tld_hash
 * @property-read \App\Models\Master_tld|null $tld
 * @property-read \App\Models\Kontrak|null $kontrak
 * @property-read \App\Models\Master_pengguna|null $pengguna
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_tld newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_tld newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_tld query()
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_tld whereCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_tld whereCountTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_tld whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_tld whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_tld whereIdDivisi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_tld whereIdKontrak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_tld whereIdKontrakTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_tld whereIdPengguna($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_tld whereIdTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_tld whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_tld whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Kontrak_tld extends Model
{
    use HasFactory;

    protected $table = 'kontrak_tld';

    protected $primaryKey = 'id_kontrak_tld';

    protected $fillable = [
        'id_kontrak',
        'id_tld',
        'id_pengguna',
        'id_divisi',
        'count',
        'count_tld',
        'status',
        'created_by'
    ];

    protected $hidden = [
        'id_kontrak',
        'id_tld',
        'id_pengguna',
        'created_by'
    ];

    protected $appends = [
        'kontrak_tld_hash',
        'kontrak_hash',
        'tld'
    ];

    protected $casts = [
        'id_tld' => 'array',
        'status' => 'integer',
        'count' => 'integer',
        'count_tld' => 'integer',
        'created_by' => 'integer',
        'id_kontrak' => 'integer',
        'id_pengguna' => 'integer',
        'id_divisi' => 'integer',
        'id_kontrak_tld' => 'integer'
    ];

    public function getKontrakTldHashAttribute()
    {
        return $this->id_kontrak_tld ? encryptor($this->id_kontrak_tld) : null;
    }

    public function getKontrakHashAttribute()
    {
        return $this->id_kontrak ? encryptor($this->id_kontrak) : null;
    }

    public function getTldAttribute()
    {
        $decodedIds = $this->id_tld;
        $decodedIds = is_array($decodedIds) ? $decodedIds : [];
        $get = Master_tld::whereIn('id_tld', $decodedIds)->get();

        return count($get) > 0 ? $get : null;
    }

    public function tld()
    {
        return $this->belongsTo(Master_tld::class, 'id_tld', 'id_tld');
    }

    public function pengguna()
    {
        return $this->belongsTo(Master_pengguna::class, 'id_pengguna', 'id_pengguna');
    }

    public function kontrak()
    {
        return $this->belongsTo(Kontrak::class, 'id_kontrak', 'id_kontrak');
    }

    public function divisi()
    {
        return $this->belongsTo(Master_divisi::class, 'id_divisi', 'id_divisi');
    }
}
