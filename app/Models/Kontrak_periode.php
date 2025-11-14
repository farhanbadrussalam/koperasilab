<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_periode
 * @property int|null $id_kontrak
 * @property int|null $periode
 * @property string|null $start_date
 * @property string|null $end_date
 * @property int|null $id_permohonan Untuk permohonan evaluasi
 * @property string|null $nomer_surpeng
 * @property int|null $status
 * @property int|null $selesai
 * @property int|null $count_tld
 * @property int|null $created_by
 * @property string|null $created_surpeng_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $periode_hash
 * @property-read mixed $permohonan_hash
 * @property-read mixed $tld_in_periode
 * @property-read \App\Models\Kontrak|null $kontrak
 * @property-read \App\Models\Penyelia|null $penyelia
 * @property-read \App\Models\Permohonan|null $permohonan
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_periode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_periode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_periode query()
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_periode whereCountTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_periode whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_periode whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_periode whereCreatedSurpengAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_periode whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_periode whereIdKontrak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_periode whereIdPeriode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_periode whereIdPermohonan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_periode whereNomerSurpeng($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_periode wherePeriode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_periode whereSelesai($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_periode whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_periode whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak_periode whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Kontrak_periode extends Model
{
    use HasFactory;

    protected $table = "kontrak_periode";
    protected $primaryKey = 'id_periode';

    protected $fillable = [
        'id_kontrak',
        'periode',
        'id_permohonan',
        'start_date',
        'end_date',
        'nomer_surpeng',
        'created_surpeng_at',
        'status',
        'selesai',
        'count_tld',
        'created_by',
        'created_at'
    ];

    protected $hidden = [
        'id_periode',
        'id_permohonan'
    ];

    protected $appends = [
        'periode_hash',
        'permohonan_hash',
        'tld_in_periode'
    ];

    protected $casts = [
        'periode' => 'integer',
        'status' => 'integer',
        'created_by' => 'integer',
        'id_permohonan' => 'integer',
        'id_kontrak' => 'integer',
        'count_tld' => 'integer',
        'selesai' => 'integer'
    ];

    public function getPeriodeHashAttribute()
    {
        return $this->id_periode ? encryptor($this->id_periode) : null;
    }

    public function getPermohonanHashAttribute()
    {
        return $this->id_permohonan ? encryptor($this->id_permohonan) : null;
    }

    public function getTldInPeriodeAttribute(){
        $idKontrak = $this->id_kontrak;
        $countTld = $this->count_tld;
        $get = Kontrak_tld::with('pengguna')->where('id_kontrak', $idKontrak)->where('count_tld', $countTld)->get();

        return count($get) > 0 ? $get : null;
    }

    public function kontrak(){
        return $this->belongsTo(Kontrak::class,'id_kontrak', 'id_kontrak');
    }

    public function permohonan(){
        return $this->belongsTo(Permohonan::class,'id_permohonan', 'id_permohonan');
    }

    public function penyelia() {
        return $this->belongsTo(Penyelia::class, 'id_permohonan', 'id_permohonan');
    }

    public function getTldInPeriode(){
        return $this->hasMany(Kontrak_tld::class, 'id_periode', 'id_periode');
    }
}
