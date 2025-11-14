<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_layanan
 * @property string|null $nama_layanan
 * @property int $status
 * @property array|null $jobs
 * @property int|null $satuankerja_id
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $layanan_hash
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Master_jobs> $jobs_pelaksana
 * @property-read int|null $jobs_pelaksana_count
 * @property-read \App\Models\Satuan_kerja|null $satuankerja
 * @method static \Illuminate\Database\Eloquent\Builder|Master_layanan_jasa newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_layanan_jasa newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_layanan_jasa query()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_layanan_jasa whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_layanan_jasa whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_layanan_jasa whereIdLayanan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_layanan_jasa whereJobs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_layanan_jasa whereNamaLayanan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_layanan_jasa whereSatuankerjaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_layanan_jasa whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_layanan_jasa whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Master_layanan_jasa extends Model
{
    use HasFactory;

    protected $table = 'master_layanan_jasa';

    protected $fillable = [
        'nama_layanan',
        'status',
        'satuankerja_id',
        'jobs',
        'created_by',
    ];

    protected $hidden = [
        'id_layanan'
    ];

    // Casting kolom sebagai array
    protected $casts = [
        'jobs' => 'array',
        'status' => 'integer',
        'id_layanan' => 'integer',
        'created_by' => 'integer',
        'satuankerja_id' => 'integer'
    ];

    protected $appends = [
        'layanan_hash'
    ];

    public function getLayananHashAttribute()
    {
        return $this->id_layanan ? encryptor($this->id_layanan) : null;
    }

    public function jobs_pelaksana(){
        return $this->hasMany(Master_jobs::class, 'id_layanan', 'id_layanan');
    }

    public function satuankerja(){
        return $this->belongsTo(Satuan_kerja::class, 'satuankerja_id', 'id');
    }
}
