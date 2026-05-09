<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $avatar
 * @property string|null $nik
 * @property string|null $alamat
 * @property string|null $no_hp
 * @property string|null $jenis_kelamin
 * @property int|null $surat_kuasa
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Master_media|null $media
 * @property-read \App\Models\Master_media|null $suratkuasa
 * @method static \Illuminate\Database\Eloquent\Builder|Profile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Profile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Profile query()
 * @method static \Illuminate\Database\Eloquent\Builder|Profile whereAlamat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Profile whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Profile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Profile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Profile whereJenisKelamin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Profile whereNik($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Profile whereNoHp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Profile whereSuratKuasa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Profile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Profile whereUserId($value)
 * @mixin \Eloquent
 */
class Profile extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'profiles';

    protected $fillable = [
        'id',
        'user_id',
        'avatar',
        'nik',
        'alamat',
        'no_hp',
        'jenis_kelamin',
        'surat_kuasa'
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'avatar' => 'integer',
        'surat_kuasa' => 'integer'
    ];

    public function media()
    {
        return $this->belongsTo(Master_media::class, 'avatar', 'id');
    }

    public function suratkuasa()
    {
        return $this->belongsTo(Master_media::class, 'surat_kuasa', 'id');
    }
}
