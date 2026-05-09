<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id_perusahaan
 * @property string $nama_perusahaan
 * @property string|null $npwp_perusahaan
 * @property string|null $kode_perusahaan
 * @property string|null $email
 * @property int|null $surat_kuasa
 * @property int|null $status
 * @property string|null $confirm_at
 * @property int|null $confirm_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Master_alamat> $alamat
 * @property-read int|null $alamat_count
 * @property-read mixed $perusahaan_hash
 * @property-read mixed $pic
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $history_pic
 * @property-read int|null $history_pic_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Master_media> $suratkuasa
 * @property-read int|null $suratkuasa_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|Perusahaan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Perusahaan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Perusahaan query()
 * @method static \Illuminate\Database\Eloquent\Builder|Perusahaan whereConfirmAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Perusahaan whereConfirmBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Perusahaan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Perusahaan whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Perusahaan whereIdPerusahaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Perusahaan whereKodePerusahaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Perusahaan whereNamaPerusahaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Perusahaan whereNpwpPerusahaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Perusahaan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Perusahaan whereSuratKuasa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Perusahaan whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Perusahaan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'perusahaan';
    protected $primaryKey = 'id_perusahaan';

    protected $fillable = [
        'nama_perusahaan',
        'npwp_perusahaan',
        'kode_perusahaan',
        'email',
        'status',
        'surat_kuasa',
        'confirm_at',
    ];

    protected $hidden = [
        'id_perusahaan'
    ];

    protected $appends = [
        'perusahaan_hash',
        'pic',
    ];

    protected $casts = [
        'status' => 'integer',
        'id_perusahaan' => 'integer',
        'confirm_by' => 'integer',
        'surat_kuasa' => 'integer',
    ];

    public function getPerusahaanHashAttribute()
    {
        return $this->id_perusahaan ? encryptor($this->id_perusahaan) : null;
    }

    public function getPicAttribute()
    {
        return $this->users()->where('status', '1')->first();
    }

    public function alamat()
    {
        return $this->hasMany(Master_alamat::class, 'id_perusahaan', 'id_perusahaan');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'id_perusahaan', 'id_perusahaan');
    }

    public function suratkuasa()
    {
        return $this->hasMany(Master_media::class, 'id', 'surat_kuasa');
    }

    public function history_pic()
    {
        return $this->hasMany(User::class, 'id_perusahaan', 'id_perusahaan');
    }
}
