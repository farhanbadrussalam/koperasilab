<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $name
 * @property array|null $jobs
 * @property array|null $jobs_paralel
 * @property int|null $status
 * @property-read mixed $list_jobs
 * @property-read mixed $list_jobs_paralel
 * @property-read mixed $setting_layanan_hash
 * @method static \Illuminate\Database\Eloquent\Builder|Setting_layanan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting_layanan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting_layanan query()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting_layanan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting_layanan whereJobs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting_layanan whereJobsParalel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting_layanan whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting_layanan whereStatus($value)
 * @mixin \Eloquent
 */
class Setting_layanan extends Model
{
    use HasFactory;

    protected $table = 'setting_layanan';

    protected $fillable = [
        'id',
        'name',
        'jobs',
        'jobs_paralel',
        'jobs_paralel_name',
    ];

    protected $hidden = [
        'id'
    ];

    protected $appends = [
        'setting_layanan_hash',
        'list_jobs',
        'list_jobs_paralel',
    ];

    protected $casts = [
        'jobs' => 'array',
        'jobs_paralel' => 'array',
        'jobs_paralel_name' => 'integer',
    ];

    public function getSettingLayananHashAttribute()
    {
        return $this->id ? encryptor($this->id) : null;
    }

    public function getListJobsAttribute()
    {
        return Master_jobs::whereIn('id_jobs', $this->jobs)->orderByRaw('FIELD(id_jobs, ' . implode(',', $this->jobs) . ')')->get();
    }

    public function getListJobsParalelAttribute()
    {
        return Master_jobs::whereIn('id_jobs', $this->jobs_paralel)->orderByRaw('FIELD(id_jobs, ' . implode(',', $this->jobs_paralel) . ')')->get();
    }
}
