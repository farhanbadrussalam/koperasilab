<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_map_pengguna
 * @property int $id_kontrak
 * @property int $id_pengguna
 * @property int|null $id_tld
 * @property int $status
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $pengguna_map_hash
 * @property-read \App\Models\Kontrak_tld|null $kontrak_tld
 * @property-read \App\Models\Master_pengguna|null $pengguna
 * @property-read \App\Models\Master_tld|null $tld_pengguna
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_pengguna newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_pengguna newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_pengguna query()
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_pengguna whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_pengguna whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_pengguna whereIdKontrak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_pengguna whereIdMapPengguna($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_pengguna whereIdPengguna($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_pengguna whereIdTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_pengguna whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_pengguna whereUpdatedAt($value)
 * @property int $id_pengguna_divisi
 * @property string|null $jenis
 * @property int $periode
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_pengguna whereIdPenggunaDivisi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_pengguna whereJenis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_pengguna wherePeriode($value)
 * @mixin \Eloquent
 */
class Kontrak_pengguna extends Model
{
    use HasFactory;

    protected $table = "kontrak_pengguna";
    protected $primaryKey = "id_map_pengguna";

    protected $fillable = [
        'id_map_pengguna',
        'id_kontrak',
        'id_pengguna',
        'id_tld',
        'status',
        'created_by',
        'created_at'
    ];

    protected $hidden = [
        'id_map_pengguna'
    ];

    protected $appends = [
        'pengguna_map_hash'
    ];

    protected $casts = [
        'id_radiasi' => 'array',
        'status' => 'integer',
        'created_by' => 'integer',
        'id_map_pengguna' => 'integer',
        'id_kontrak' => 'integer',
        'id_pengguna' => 'integer',
        'id_tld' => 'integer'
    ];

    public function getPenggunaMapHashAttribute()
    {
        return $this->id_map_pengguna ? encryptor($this->id_map_pengguna) : null;
    }

    public function tld_pengguna(){
        return $this->belongsTo(Master_tld::class, 'id_tld', 'id_tld')->withTrashed();
    }

    public function kontrak_tld(){
        return $this->belongsTo(Kontrak_tld::class, 'id_map_pengguna', 'id_map_pengguna');
    }

    public function pengguna(){
        return $this->belongsTo(Master_pengguna::class, 'id_pengguna', 'id_pengguna')->withTrashed();
    }
}
