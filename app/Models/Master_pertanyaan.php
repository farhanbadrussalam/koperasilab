<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_pertanyaan
 * @property int|null $id_layananjasa
 * @property string|null $pertanyaan
 * @property int|null $type
 * @property int|null $mandatory
 * @property-read mixed $pertanyaan_hash
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pertanyaan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pertanyaan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pertanyaan query()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pertanyaan whereIdLayananjasa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pertanyaan whereIdPertanyaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pertanyaan whereMandatory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pertanyaan wherePertanyaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_pertanyaan whereType($value)
 * @mixin \Eloquent
 */
class Master_pertanyaan extends Model
{
    use HasFactory;

    protected $table = 'master_pertanyaan';
    protected $primaryKey = 'id_pertanyaan';

    protected $fillable = [
        'id_layananjasa',
        'pertanyaan',
        'type',
        'mandatory'
    ];
    protected $hidden = [
        // 'id_pertanyaan',
    ];
    protected $appends = [
        'pertanyaan_hash'
    ];

    protected $casts = [
        'type' => 'integer',
        'mandatory' => 'integer',
        'id_layananjasa' => 'integer',
        'id_pertanyaan' => 'integer',
    ];

    public function getPertanyaanHashAttribute()
    {
        return $this->id_pertanyaan ? encryptor($this->id_pertanyaan) : null;
    }
}
