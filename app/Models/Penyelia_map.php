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
        'status',
        'point_jobs',
        'created_by',
        'done_by',
        'done_at',
    ];

    protected $hidden = [
        'id_jobs',
        'id_map'
    ];

    protected $appends = [
        'map_hash',
        'jobs_hash'
    ];

    protected $casts = [
        'status' => 'integer',
        'id_map' => 'integer',
        'id_jobs' => 'integer',
        'id_penyelia' => 'integer',
        'order' => 'integer',
        'point_jobs' => 'integer',
        'created_by' => 'integer',
        'done_by' => 'integer',
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

    public function jobs_paralel()
    {
        return $this->belongsTo(Master_jobs::class, 'point_jobs', 'id_jobs');
    }

    public function petugas()
    {
        return $this->hasMany(Penyelia_petugas::class, 'id_map', 'id_map');
    }

    public function doneBy()
    {
        return $this->belongsTo(User::class, 'done_by', 'id');
    }

    public function penyelia()
    {
        return $this->belongsTo(Penyelia::class, 'id_penyelia', 'id_penyelia');
    }
}
