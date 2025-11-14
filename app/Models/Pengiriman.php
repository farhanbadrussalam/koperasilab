<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id_pengiriman
 * @property string|null $no_resi
 * @property string|null $jenis_pengiriman
 * @property int|null $id_ekspedisi
 * @property int|null $id_permohonan
 * @property int|null $id_kontrak
 * @property \App\Models\User|null $tujuan
 * @property \App\Models\Master_alamat|null $alamat
 * @property string|null $detail_alamat
 * @property int|null $status
 * @property int|null $periode
 * @property array|null $bukti_pengiriman
 * @property array|null $bukti_penerima
 * @property int|null $created_by
 * @property string|null $send_at
 * @property string|null $recived_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pengiriman_detail> $detail
 * @property-read int|null $detail_count
 * @property-read \App\Models\Master_ekspedisi|null $ekspedisi
 * @property-read mixed $permohonan_hash
 * @property-read \App\Models\Kontrak|null $kontrak
 * @property-read \App\Models\Permohonan|null $permohonan
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman query()
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman whereAlamat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman whereBuktiPenerima($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman whereBuktiPengiriman($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman whereDetailAlamat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman whereIdEkspedisi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman whereIdKontrak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman whereIdPengiriman($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman whereIdPermohonan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman whereJenisPengiriman($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman whereNoResi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman wherePeriode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman whereRecivedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman whereSendAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman whereTujuan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pengiriman whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Pengiriman extends Model
{
    use HasFactory;

    protected $table = 'pengiriman';
    protected $primaryKey = 'id_pengiriman';
    protected $keyType = 'string';

    protected $fillable = [
        'id_pengiriman',
        'no_resi',
        'jenis_pengiriman',
        'id_ekspedisi',
        'id_permohonan',
        'id_kontrak',
        'alamat',
        'detail_alamat',
        'status',
        'tujuan',
        'periode',
        'bukti_pengiriman',
        'bukti_penerima',
        'send_at',
        'recived_at',
        'created_by',
        'created_at'
    ];

    protected $hidden = [
        'id_permohonan',
        'bukti_pengiriman',
        'bukti_penerima'
    ];

    protected $appends = [
        'permohonan_hash'
    ];

    protected $casts = [
        'bukti_pengiriman' => 'array',
        'bukti_penerima' => 'array',
        'status' => 'integer',
        'periode' => 'integer',
        'id_ekspedisi' => 'integer',
        'id_permohonan' => 'integer',
        'id_kontrak' => 'integer',
        'tujuan' => 'integer',
        'alamat' => 'integer',
        'created_by' => 'integer'
    ];

    public function getPermohonanHashAttribute()
    {
        return $this->id_permohonan ? encryptor($this->id_permohonan) : null;
    }

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class, 'id_permohonan', 'id_permohonan');
    }

    public function detail(){
        return $this->hasMany(Pengiriman_detail::class, 'id_pengiriman', 'id_pengiriman');
    }

    public function alamat(){
        return $this->belongsTo(Master_alamat::class, 'alamat', 'id_alamat');
    }

    public function kontrak(){
        return $this->belongsTo(Kontrak::class, 'id_kontrak', 'id_kontrak');
    }

    public function ekspedisi(){
        return $this->belongsTo(Master_ekspedisi::class, 'id_ekspedisi', 'id_ekspedisi');
    }

    public function tujuan(){
        return $this->belongsTo(User::class, 'tujuan', 'id');
    }
}
