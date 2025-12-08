<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_petugas
 * @property int|null $id_user
 * @property int|null $id_map
 * @property int|null $id_penyelia
 * @property int|null $status
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $map_hash
 * @property-read mixed $penyelia_hash
 * @property-read mixed $petugas_hash
 * @property-read mixed $user_hash
 * @property-read \App\Models\Penyelia_map|null $jobs
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_petugas newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_petugas newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_petugas query()
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_petugas whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_petugas whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_petugas whereIdMap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_petugas whereIdPenyelia($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_petugas whereIdPetugas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_petugas whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_petugas whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_petugas whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Penyelia_petugas extends Model
{
    use HasFactory;

    protected $table = 'penyelia_petugas';
    protected $primaryKey = 'id_petugas';

    protected $fillable = [
        'id_user',
        'id_map',
        'id_penyelia',
        'status',
        'created_by'
    ];

    protected $hidden = [
        'id_petugas',
        'id_map',
        'id_penyelia',
        'id_user'
    ];

    protected $appends = [
        'petugas_hash',
        'map_hash',
        'penyelia_hash',
        'user_hash'
    ];

    protected $casts = [
        'status' => 'integer',
        'id_petugas' => 'integer',
        'id_user' => 'integer',
        'id_map' => 'integer',
        'id_penyelia' => 'integer',
        'created_by' => 'integer'
    ];

    public function getPetugasHashAttribute()
    {
        return $this->id_petugas ? encryptor($this->id_petugas) : null;
    }

    public function getMapHashAttribute()
    {
        return $this->id_map ? encryptor($this->id_map) : null;
    }

    public function getPenyeliaHashAttribute()
    {
        return $this->id_penyelia ? encryptor($this->id_penyelia) : null;
    }

    public function getUserHashAttribute()
    {
        return $this->id_user ? encryptor($this->id_user) : null;
    }

    public function jobs()
    {
        return $this->belongsTo(Penyelia_map::class, 'id_map', 'id_map');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function penyelia_map()
    {
        return $this->belongsTo(Penyelia_map::class, 'id_map', 'id_map');
    }
}
