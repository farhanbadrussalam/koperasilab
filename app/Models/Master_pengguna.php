<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id_pengguna
 * @property array|null $id_radiasi
 * @property int|null $id_perusahaan
 * @property string|null $kode_lencana
 * @property string|null $nik
 * @property string|null $name
 * @property int|null $id_divisi
 * @property string|null $jenis_kelamin
 * @property string|null $tempat_lahir
 * @property string|null $tanggal_lahir
 * @property int|null $ktp
 * @property string|null $keterangan
 * @property int|null $status
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Master_divisi|null $divisi
 * @property-read mixed $pengguna_hash
 * @property-read mixed $radiasi
 * @property-read \App\Models\Master_media|null $media_ktp
 * @property-read \App\Models\Perusahaan|null $perusahaan
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pengguna newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pengguna newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pengguna query()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pengguna whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pengguna whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pengguna whereIdDivisi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pengguna whereIdPengguna($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pengguna whereIdPerusahaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pengguna whereIdRadiasi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pengguna whereJenisKelamin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pengguna whereKeterangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pengguna whereKodeLencana($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pengguna whereKtp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pengguna whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pengguna whereNik($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pengguna whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pengguna whereTanggalLahir($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pengguna whereTempatLahir($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pengguna whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Master_pengguna extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'master_pengguna';
    protected $primaryKey = 'id_pengguna';

    protected $fillable = [
        'id_pengguna',
        'id_radiasi',
        'id_perusahaan',
        'kode_lencana',
        'nik',
        'name',
        'id_divisi',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'ktp',
        'keterangan',
        'status',
        'created_by',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        'id_pengguna',
        'id_radiasi',
        'id_perusahaan',
        'ktp'
    ];

    protected $appends = [
        'pengguna_hash',
        'radiasi'
    ];

    protected $casts = [
        'id_radiasi' => 'array',
        'status' => 'integer',
        'id_pengguna' => 'integer',
        'id_perusahaan' => 'integer',
        'id_divisi' => 'integer',
        'ktp' => 'integer',
        'created_by' => 'integer'
    ];

    public function getPenggunaHashAttribute()
    {
        return $this->id_pengguna ? encryptor($this->id_pengguna) : null;
    }

    public function getRadiasiAttribute()
    {
        $decodeArr = $this->id_radiasi;
        $decodeArr = is_array($decodeArr) ? $decodeArr : [];

        return Master_radiasi::whereIn('id_radiasi', $decodeArr)->get();
    }

    /**
     * Get the media ktp associated with the Master_pengguna
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function media_ktp()
    {
        return $this->belongsTo(Master_media::class, 'ktp', 'id');
    }

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class, 'id_perusahaan', 'id_perusahaan')->withTrashed();
    }

    public function divisi()
    {
        return $this->belongsTo(Master_divisi::class, 'id_divisi', 'id_divisi')->withTrashed();
    }
}
