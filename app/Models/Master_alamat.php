<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_alamat
 * @property int|null $id_perusahaan
 * @property string|null $jenis
 * @property string|null $alamat
 * @property string|null $kode_pos
 * @property int|null $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $alamat_hash
 * @method static \Illuminate\Database\Eloquent\Builder|Master_alamat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_alamat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_alamat query()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_alamat whereAlamat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_alamat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_alamat whereIdAlamat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_alamat whereIdPerusahaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_alamat whereJenis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_alamat whereKodePos($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_alamat whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_alamat whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Master_alamat extends Model
{
    use HasFactory;

    protected $table = 'master_alamat';
    protected $primaryKey = 'id_alamat';

    protected $fillable = [
        'id_perusahaan',
        'alamat',
        'jenis',
        'kode_pos',
        'status',
        'created_at'
    ];

    protected $hidden = [
        'id_alamat',
    ];

    protected $appends = [
        'alamat_hash'
    ];

    protected $casts = [
        'status' => 'integer',
        'id_perusahaan' => 'integer'
    ];

    public function getAlamatHashAttribute()
    {
        return $this->id_alamat ? encryptor($this->id_alamat) : null;
    }
}
