<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id_tld
 * @property string|null $no_seri_tld
 * @property string|null $merk
 * @property string|null $jenis
 * @property string|null $tanggal_pengadaan
 * @property int|null $kepemilikan
 * @property string|null $digunakan
 * @property int|null $status
 * @property-read mixed $tld_hash
 * @property-read \App\Models\Perusahaan|null $pemilik
 * @method static \Illuminate\Database\Eloquent\Builder|Master_tld newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_tld newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_tld query()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_tld whereDigunakan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_tld whereIdTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_tld whereJenis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_tld whereKepemilikan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_tld whereMerk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_tld whereNoSeriTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_tld whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_tld whereTanggalPengadaan($value)
 * @mixin \Eloquent
 */
class Master_tld extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'master_tld';

    protected $primaryKey = 'id_tld';

    public $timestamps = false;

    protected $fillable = [
        'no_seri_tld',
        'merk',
        'jenis',
        'status',
        'tanggal_pengadaan',
        'kepemilikan',
        'digunakan'
    ];

    protected $hidden = [
        'id_tld'
    ];

    protected $appends = [
        'tld_hash'
    ];

    protected $casts = [
        'status' => 'integer',
        'id_tld' => 'integer',
        'kepemilikan' => 'integer'
    ];

    public function getTldHashAttribute()
    {
        return $this->id_tld ? encryptor($this->id_tld) : null;
    }

    public function pemilik()
    {
        return $this->belongsTo(Perusahaan::class, 'kepemilikan', 'id_perusahaan');
    }
}
