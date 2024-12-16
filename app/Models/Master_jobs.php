<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Master_jobs extends Model
{
    use HasFactory;

    protected $table = 'master_jobs';
    protected $primaryKey = 'id_jobs';

    protected $fillable = [
        'name',
        'status'
    ];

    protected $hidden = [
        'id_jobs'
    ];

    protected $appends = [
        'jobs_hash'
    ];

    public function getJobsHashAttribute()
    {
        return encryptor($this->id_jobs);
    }
}
