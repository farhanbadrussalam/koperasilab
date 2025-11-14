<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_divisi
 * @property string|null $kode_lencana
 * @property int|null $id_perusahaan
 * @property string|null $name
 * @property int|null $status
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $divisi_hash
 * @property-read \App\Models\Perusahaan|null $perusahaan
 * @method static \Illuminate\Database\Eloquent\Builder|Master_divisi newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_divisi newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_divisi query()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_divisi whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_divisi whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_divisi whereIdDivisi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_divisi whereIdPerusahaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_divisi whereKodeLencana($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_divisi whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_divisi whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_divisi whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Master_divisi extends Model
{
    use HasFactory;

    protected $table = 'master_divisi';
    protected $primaryKey = 'id_divisi';

    protected $fillable = [
        'id_divisi',
        'kode_lencana',
        'id_perusahaan',
        'name',
        'status',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        // 'created_at',
        // 'updated_at',
    ];

    protected $appends = [
        'divisi_hash'
    ];

    protected $casts = [
        'status' => 'integer',
        'id_divisi' => 'integer',
        'id_perusahaan' => 'integer',
        'created_by' => 'integer',
    ];

    public function getDivisiHashAttribute()
    {
        return $this->id_divisi ? encryptor($this->id_divisi) : null;
    }

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class, 'id_perusahaan', 'id_perusahaan');
    }
}
