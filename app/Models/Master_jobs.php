<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_jobs
 * @property int|null $id_layanan
 * @property string|null $name
 * @property int|null $order
 * @property int|null $status
 * @property int|null $upload_doc
 * @property-read mixed $jobs_hash
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jobs newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jobs newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jobs query()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jobs whereIdJobs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jobs whereIdLayanan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jobs whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jobs whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jobs whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jobs whereUploadDoc($value)
 * @property string|null $color
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Penyelia_map> $penyelia_map
 * @property-read int|null $penyelia_map_count
 * @method static \Illuminate\Database\Eloquent\Builder|Master_jobs whereColor($value)
 * @mixin \Eloquent
 */
class Master_jobs extends Model
{
    use HasFactory;

    protected $table = 'master_jobs';
    protected $primaryKey = 'id_jobs';

    protected $fillable = [
        'name',
        'status',
        'upload_doc',
        'color'
    ];

    protected $hidden = [
        'id_jobs'
    ];

    protected $appends = [
        'jobs_hash'
    ];

    protected $casts = [
        'status' => 'integer',
        'order' => 'integer',
        'upload_doc' => 'integer',
        'id_jobs' => 'integer'
    ];

    public function getJobsHashAttribute()
    {
        return $this->id_jobs ? encryptor($this->id_jobs) : null;
    }

    public function penyelia_map()
    {
        return $this->hasMany(Penyelia_map::class, 'id_jobs', 'id_jobs');
    }
}
