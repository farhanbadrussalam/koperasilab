<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_map_pengguna
 * @property int $id_permohonan
 * @property int $id_pengguna
 * @property int|null $id_tld
 * @property int|null $status
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $pengguna_map_hash
 * @property-read \App\Models\Master_pengguna|null $pengguna
 * @property-read \App\Models\Permohonan_tld|null $permohonan_tld
 * @property-read \App\Models\Master_tld|null $tld_pengguna
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_pengguna newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_pengguna newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_pengguna query()
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_pengguna whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_pengguna whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_pengguna whereIdMapPengguna($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_pengguna whereIdPengguna($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_pengguna whereIdPermohonan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_pengguna whereIdTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_pengguna whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_pengguna whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Permohonan_pengguna extends Model
{
    use HasFactory;

    protected $table = "permohonan_pengguna";

    protected $primaryKey = "id_map_pengguna";

    protected $fillable = [
        'id_map_pengguna',
        'id_permohonan',
        'id_pengguna',
        'id_tld',
        'status',
        'created_by',
        'created_at'
    ];

    protected $hidden = [
        'id_map_pengguna',
        'id_tld'
    ];

    protected $appends = [
        'pengguna_map_hash'
    ];

    protected $casts = [
        'id_map_pengguna' => 'integer',
        'id_permohonan' => 'integer',
        'id_pengguna' => 'integer',
        'id_tld' => 'integer',
        'status' => 'integer',
        'created_by' => 'integer'
    ];

    public function getPenggunaMapHashAttribute()
    {
        return $this->id_map_pengguna ? encryptor($this->id_map_pengguna) : null;
    }

    public function tld_pengguna(){
        return $this->belongsTo(Master_tld::class, 'id_tld', 'id_tld');
    }

    public function permohonan_tld(){
        return $this->belongsTo(Permohonan_tld::class, 'id_map_pengguna', 'id_map_pengguna');
    }

    public function pengguna(){
        return $this->belongsTo(Master_pengguna::class, 'id_pengguna', 'id_pengguna');
    }
}
