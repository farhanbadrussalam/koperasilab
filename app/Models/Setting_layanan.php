<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
