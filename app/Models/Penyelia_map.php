<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penyelia_map extends Model
{
    use HasFactory;

    protected $table = 'penyelia_map';
    protected $primaryKey = 'id_map';

    protected $fillable = [
        'id_jobs',
        'id_penyelia',
        'order',
        'created_by'
    ];

    protected $hidden = [
        'id_jobs',
        'id_map'
    ];

    protected $appends = [
        'map_hash',
        'jobs_hash'
    ];

    public function getMapHashAttribute()
    {
        return encryptor($this->id_map);
    }
    
    public function getJobsHashAttribute()
    {
        return encryptor($this->id_jobs);
    }

    public function jobs()
    {
        return $this->belongsTo(Master_jobs::class, 'id_jobs', 'id_jobs');
    }
}
