<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_pengiriman_detail
 * @property string|null $id_pengiriman
 * @property string|null $jenis
 * @property int|null $periode
 * @property array|null $list_tld
 * @property string|null $nomer_surpeng
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $pengiriman_detail_hash
 * @property-read mixed $pengiriman_hash
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman_detail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman_detail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman_detail query()
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman_detail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman_detail whereIdPengiriman($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman_detail whereIdPengirimanDetail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman_detail whereJenis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman_detail whereListTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman_detail whereNomerSurpeng($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman_detail wherePeriode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman_detail whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Pengiriman_detail extends Model
{
    use HasFactory;

    protected $table = 'pengiriman_detail';
    protected $primaryKey = 'id_pengiriman_detail';

    protected $fillable = [
        'id_pengiriman',
        'jenis',
        'periode',
        'list_tld',
        'nomer_surpeng'
    ];

    protected $hidden = [
        'id_pengiriman_detail',
        'id_pengiriman'
    ];

    protected $appends = [
        'pengiriman_detail_hash',
        'pengiriman_hash'
    ];

    protected $casts = [
        'list_tld' => 'array',
        'id_pengiriman_detail' => 'integer',
        'periode' => 'integer',
    ];

    public function getPengirimanDetailHashAttribute()
    {
        return $this->id_pengiriman_detail ? encryptor($this->id_pengiriman_detail) : null;
    }

    public function getPengirimanHashAttribute()
    {
        return $this->id_pengiriman ? encryptor($this->id_pengiriman) : null;
    }
}
