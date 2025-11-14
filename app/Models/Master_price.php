<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_price
 * @property int|null $id_jenisTld
 * @property string|null $id_jenisLayanan
 * @property string|null $keterangan
 * @property int|null $qty
 * @property int|null $price
 * @property int|null $created_by
 * @property string|null $created_date
 * @property string|null $updated_date
 * @property-read mixed $jenis_tld_hash
 * @property-read mixed $price_hash
 * @property-read \App\Models\Master_jenistld|null $jenisTld
 * @method static \Illuminate\Database\Eloquent\Builder|Master_price newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_price newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_price query()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_price whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_price whereCreatedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_price whereIdJenisLayanan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_price whereIdJenisTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_price whereIdPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_price whereKeterangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_price wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_price whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_price whereUpdatedDate($value)
 * @mixin \Eloquent
 */
class Master_price extends Model
{
    use HasFactory;
    protected $table = 'master_price';

    protected $fillable = [
        'id_jenisLayanan',
        'keterangan',
        'qty',
        'price'
    ];

    protected $hidden = [
        'id_price',
        'id_jenisTld'
    ];

    protected $appends = [
        'price_hash',
        'jenis_tld_hash'
    ];

    protected $casts = [
        'price' => 'integer',
        'qty' => 'integer',
        'id_price' => 'integer',
        'id_jenisTld' => 'integer',
        'created_by' => 'integer',
    ];

    public function getPriceHashAttribute()
    {
        return $this->id_price ? encryptor($this->id_price) : null;
    }

    public function getJenisTldHashAttribute()
    {
        return $this->id_jenisTld ? encryptor($this->id_jenisTld) : null;
    }

    public function jenisTld(){
        return $this->belongsTo(Master_jenistld::class, 'id_jenisTld', 'id_jenisTld');
    }
}
