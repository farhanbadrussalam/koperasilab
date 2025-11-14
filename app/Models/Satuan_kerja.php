<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string|null $alias
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $satuan_hash
 * @method static \Illuminate\Database\Eloquent\Builder|Satuan_kerja newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Satuan_kerja newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Satuan_kerja query()
 * @method static \Illuminate\Database\Eloquent\Builder|Satuan_kerja whereAlias($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Satuan_kerja whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Satuan_kerja whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Satuan_kerja whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Satuan_kerja whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Satuan_kerja extends Model
{
    use HasFactory;

    protected $table = 'satuankerja';

    protected $fillable = [
        'name',
        'alias'
    ];

    protected $hidden = [
        'id'
    ];

    protected $appends = [
        'satuan_hash'
    ];

    public function getSatuanHashAttribute()
    {
        return $this->id ? encryptor($this->id) : null;
    }
}
