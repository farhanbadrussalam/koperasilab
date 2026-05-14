<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_map
 * @property int|null $id_penyelia
 * @property int|null $id_jobs
 * @property int|null $order
 * @property int|null $status 1 = selesai
 * @property int|null $point_jobs
 * @property int|null $done_by
 * @property string|null $done_at
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $doneBy
 * @property-read mixed $jobs_hash
 * @property-read mixed $map_hash
 * @property-read \App\Models\Master_jobs|null $jobs
 * @property-read \App\Models\Master_jobs|null $jobs_paralel
 * @property-read \App\Models\Penyelia|null $penyelia
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Penyelia_petugas> $petugas
 * @property-read int|null $petugas_count
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_map newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_map newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_map query()
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_map whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_map whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_map whereDoneAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_map whereDoneBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_map whereIdJobs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_map whereIdMap($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_map whereIdPenyelia($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_map whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_map wherePointJobs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_map whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_map whereUpdatedAt($value)
 * @property string|null $note
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Log_proses> $logs
 * @property-read int|null $logs_count
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia_map whereNote($value)
 * @mixin \Eloquent
 */
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
        'note'
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
        return $this->id_map ? encryptor($this->id_map) : null;
    }

    public function getJobsHashAttribute()
    {
        return $this->id_jobs ? encryptor($this->id_jobs) : null;
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
        return $this->belongsTo(User::class, 'done_by', 'id')->withTrashed();
    }

    public function penyelia()
    {
        return $this->belongsTo(Penyelia::class, 'id_penyelia', 'id_penyelia');
    }

    public function logs(){
        return $this->morphMany(Log_proses::class, 'subject')->orderBy('created_at', 'desc');
    }
}
