<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_jenisLayanan
 * @property string|null $name
 * @property int|null $parent
 * @property array|null $jobs
 * @property array|null $jobs_paralel
 * @property int|null $jobs_paralel_point
 * @property int|null $status
 * @property string|null $alias
 * @property int|null $created_by
 * @property string|null $created_date
 * @property string|null $updated_date
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Master_jenisLayanan> $child
 * @property-read int|null $child_count
 * @property-read mixed $jenis_layanan_hash
 * @property-read mixed $parent_hash
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jenisLayanan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jenisLayanan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jenisLayanan query()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jenisLayanan whereAlias($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jenisLayanan whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jenisLayanan whereCreatedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jenisLayanan whereIdJenisLayanan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jenisLayanan whereJobs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jenisLayanan whereJobsParalel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jenisLayanan whereJobsParalelPoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jenisLayanan whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jenisLayanan whereParent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jenisLayanan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jenisLayanan whereUpdatedDate($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Kontrak> $kontrak
 * @property-read int|null $kontrak_count
 * @mixin \Eloquent
 */
class Master_jenisLayanan extends Model
{
    use HasFactory;
    protected $table = 'master_jenislayanan';

    protected $fillable = [
        'id_jenisLayanan',
        'name',
        'jobs',
        'jobs_paralel',
        'jobs_paralel_point',
        'status',
        'alias'
    ];

    protected $hidden = [
        'parent'
    ];

    protected $appends = [
        'jenis_layanan_hash',
        'parent_hash'
    ];

    protected $casts = [
        'jobs' => 'array',
        'jobs_paralel' => 'array',
        'parent' => 'integer',
        'job_paralel_point' => 'integer',
        'id_jenisLayanan' => 'integer'
    ];

    public function getJenisLayananHashAttribute()
    {
        return $this->id_jenisLayanan ? encryptor($this->id_jenisLayanan) : null;
    }

    public function getParentHashAttribute()
    {
        return $this->parent ? encryptor($this->parent) : null;
    }

    public function child()
    {
        return $this->hasMany(Master_jenisLayanan::class, 'parent', 'id_jenisLayanan');
    }

    public function kontrak()
    {
        return $this->hasMany(Kontrak::class, 'jenis_layanan_1', 'id_jenisLayanan');
    }
}
