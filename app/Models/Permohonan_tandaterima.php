<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_permohonan
 * @property int $id_pertanyaan
 * @property string|null $jawaban
 * @property string|null $note
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $permohonan_hash
 * @property-read mixed $pertanyaan_hash
 * @property-read \App\Models\Master_pertanyaan $pertanyaan
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tandaterima newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tandaterima newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tandaterima query()
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tandaterima whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tandaterima whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tandaterima whereIdPermohonan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tandaterima whereIdPertanyaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tandaterima whereJawaban($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tandaterima whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_tandaterima whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Permohonan_tandaterima extends Model
{
    use HasFactory;

    protected $table = "permohonan_tandaterima";

    protected $fillable = [
        'id_permohonan',
        'id_pertanyaan',
        'jawaban',
        'note',
        'created_by'
    ];

    protected $hidden = [
        'id_permohonan',
        'id_pertanyaan'
    ];

    protected $appends = [
        'permohonan_hash',
        'pertanyaan_hash'
    ];

    protected $casts = [
        'id_permohonan' => 'integer',
        'id_pertanyaan' => 'integer',
        'created_by' => 'integer'
    ];

    public function getPermohonanHashAttribute()
    {
        return $this->id_permohonan ? encryptor($this->id_permohonan) : null;
    }

    public function getPertanyaanHashAttribute()
    {
        return $this->id_pertanyaan ? encryptor($this->id_pertanyaan) : null;
    }

    public function pertanyaan(){
        return $this->belongsTo(Master_pertanyaan::class, 'id_pertanyaan', 'id_pertanyaan');
    }
}
