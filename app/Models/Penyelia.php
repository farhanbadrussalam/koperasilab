<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_penyelia
 * @property int|null $id_permohonan
 * @property string|null $id_pengiriman
 * @property \Illuminate\Support\Carbon|null $start_date
 * @property \Illuminate\Support\Carbon|null $end_date
 * @property int|null $periode
 * @property int|null $status
 * @property string|null $ttd
 * @property int|null $ttd_by
 * @property array|null $document
 * @property string|null $list_tld
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $createBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permohonan_dokumen> $dokumen
 * @property-read int|null $dokumen_count
 * @property-read mixed $media
 * @property-read mixed $penyelia_hash
 * @property-read mixed $permohonan_hash
 * @property-read mixed $status_hash
 * @property-read mixed $template_surat
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Log_penyelia> $log
 * @property-read int|null $log_count
 * @property-read \App\Models\Pengiriman|null $pengiriman
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Penyelia_map> $penyelia_map
 * @property-read int|null $penyelia_map_count
 * @property-read \App\Models\Kontrak_periode|null $periodenow
 * @property-read \App\Models\Permohonan|null $permohonan
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Penyelia_petugas> $petugas
 * @property-read int|null $petugas_count
 * @property-read \App\Models\User|null $usersig
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia query()
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia whereDocument($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia whereIdPengiriman($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia whereIdPenyelia($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia whereIdPermohonan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia whereListTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia wherePeriode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia whereTtd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia whereTtdBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Penyelia whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Penyelia extends Model
{
    use HasFactory;

    protected $table = 'penyelia';
    protected $primaryKey = 'id_penyelia';

    protected $fillable = [
        'id_permohonan',
        'id_pengiriman',
        'start_date',
        'end_date',
        'periode',
        'status',
        'ttd',
        'ttd_by',
        'petugas',
        'document',
        'created_by',
        'created_at'
    ];

    protected $hidden = [
        'id_penyelia',
        'id_permohonan',
        'document'
    ];

    protected $appends = [
        'penyelia_hash',
        'permohonan_hash',
        'status_hash',
        'media',
        'template_surat'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'periode' => 'integer',
        'status' => 'integer',
        'id_penyelia' => 'integer',
        'id_permohonan' => 'integer',
        'ttd_by' => 'integer',
        'created_by' => 'integer',
        'document' => 'json'
    ];

    public function getPermohonanHashAttribute()
    {
        return $this->id_permohonan ? encryptor($this->id_permohonan) : null;
    }

    public function getPenyeliaHashAttribute()
    {
        return $this->id_penyelia ? encryptor($this->id_penyelia) : null;
    }

    public function getStatusHashAttribute()
    {
        return $this->status ? encryptor($this->status) : null;
    }

    public function getMediaAttribute()
    {
        $decodedIds = $this->document;
        $decodedIds = is_array($decodedIds) ? $decodedIds : [];

        return Master_media::whereIn('id', $decodedIds)->get();
    }

    public function getTemplateSuratAttribute(){
        return Documents::whereIn('name', ['SuratPengujian', 'SuratTugas', 'KontrakPengujian'])->where('status', 1)->get();
    }

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class, 'id_permohonan', 'id_permohonan');
    }

    public function usersig(){
        return $this->belongsTo(User::class, 'ttd_by', 'id');
    }

    public function log(){
        return $this->hasMany(Log_penyelia::class, 'id_penyelia', 'id_penyelia')->orderBy('created_at', 'desc')->orderBy('id', 'desc');
    }

    // public function media(){
    //     return $this->belongsTo(Master_media::class, 'document', 'id');
    // }

    public function petugas(){
        return $this->hasMany(Penyelia_petugas::class, 'id_penyelia', 'id_penyelia');
    }

    public function penyelia_map(){
        return $this->hasMany(Penyelia_map::class, 'id_penyelia', 'id_penyelia')->orderBy('order', 'asc');
    }

    public function pengiriman(){
        return $this->belongsTo(Pengiriman::class, 'id_pengiriman', 'id_pengiriman');
    }

    public function createBy(){
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function dokumen(){
        return $this->hasMany(Permohonan_dokumen::class, 'id_permohonan', 'id_permohonan');
    }

    public function periodenow(){
        return $this->belongsTo(Kontrak_periode::class, 'id_permohonan', 'id_permohonan');
    }
}
