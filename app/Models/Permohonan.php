<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_permohonan
 * @property int|null $id_layanan
 * @property int|null $id_kontrak
 * @property string|null $id_pengiriman
 * @property int|null $id_alamat
 * @property int|null $jenis_layanan_1
 * @property int|null $jenis_layanan_2
 * @property string|null $tipe_kontrak
 * @property int|null $jenis_tld
 * @property array|null $periode_pemakaian
 * @property array|null $periode_next
 * @property int|null $periode Di ambil dari kontrak_periode
 * @property int|null $jumlah_pengguna
 * @property int|null $jumlah_kontrol
 * @property int|null $harga_layanan
 * @property string|null $pic
 * @property string|null $no_hp
 * @property string|null $ttd
 * @property int|null $ttd_by
 * @property int|null $total_harga
 * @property int $status
 * @property string|null $note
 * @property \App\Models\Master_media|null $file_lhu
 * @property int|null $flag_read
 * @property int|null $is_have_tld
 * @property int|null $is_zerocek
 * @property int|null $created_by
 * @property string|null $verify_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permohonan_dokumen> $dokumen
 * @property-read int|null $dokumen_count
 * @property-read mixed $kontrak_hash
 * @property-read mixed $permohonan_hash
 * @property-read \App\Models\Keuangan|null $invoice
 * @property-read \App\Models\Master_jenistld|null $jenisTld
 * @property-read \App\Models\Master_jenisLayanan|null $jenis_layanan
 * @property-read \App\Models\Master_jenisLayanan|null $jenis_layanan_parent
 * @property-read \App\Models\Kontrak|null $kontrak
 * @property-read \App\Models\Master_layanan_jasa|null $layanan_jasa
 * @property-read \App\Models\Penyelia|null $lhu
 * @property-read \App\Models\User|null $pelanggan
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permohonan_pengguna> $pengguna
 * @property-read int|null $pengguna_count
 * @property-read \App\Models\Pengiriman|null $pengiriman
 * @property-read \App\Models\Kontrak_periode|null $periodenow
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permohonan_tld> $rincian_list_tld
 * @property-read int|null $rincian_list_tld_count
 * @property-read \App\Models\User|null $signature
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permohonan_tandaterima> $tandaterima
 * @property-read int|null $tandaterima_count
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan query()
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereFileLhu($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereFlagRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereHargaLayanan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereIdAlamat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereIdKontrak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereIdLayanan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereIdPengiriman($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereIdPermohonan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereIsHaveTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereIsZerocek($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereJenisLayanan1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereJenisLayanan2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereJenisTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereJumlahKontrol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereJumlahPengguna($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereNoHp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan wherePeriode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan wherePeriodeNext($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan wherePeriodePemakaian($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan wherePic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereTipeKontrak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereTotalHarga($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereTtd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereTtdBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan whereVerifyAt($value)
 * @property-read mixed $ttd_image
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Log_proses> $logs
 * @property-read int|null $logs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permohonan_detail> $permohonan_detail
 * @property-read int|null $permohonan_detail_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permohonan_pengguna> $permohonan_pengguna
 * @property-read int|null $permohonan_pengguna_count
 * @mixin \Eloquent
 */
class Permohonan extends Model
{
    use HasFactory;

    protected $table = "permohonan";
    protected $primaryKey = 'id_permohonan';

    protected $fillable = [
        'id_layanan',
        'jenis_layanan_2',
        'jenis_layanan_1',
        'id_kontrak',
        'id_pengiriman',
        'id_alamat',
        'tipe_kontrak',
        'no_kontrak',
        'jenis_tld',
        'periode_pemakaian',
        'periode_next',
        'periode',
        'jumlah_pengguna',
        'jumlah_kontrol',
        'total_harga',
        'harga_layanan',
        'pic',
        'no_hp',
        'ttd',
        'ttd_by',
        'status',
        'note',
        'is_have_tld',
        'is_zerocek',
        'file_lhu',
        'flag_read',
        'created_by',
        'created_at',
        'verify_at'
    ];

    protected $hidden = [
        'jenis_layanan_2',
        'jenis_layanan_1',
        'id_layanan',
        'id_permohonan',
        'list_tld',
        'id_kontrak',
    ];

    protected $appends = [
        'permohonan_hash',
        'kontrak_hash',
        'ttd_image',
        'periodenow'
    ];

    protected $casts = [
        'periode_pemakaian' => 'array',
        'periode_next' => 'array',
        'list_tld' => 'array',
        'tld_kontrol' => 'array',
        'periode' => 'integer',
        'jumlah_pengguna' => 'integer',
        'jumlah_kontrol' => 'integer',
        'total_harga' => 'integer',
        'harga_layanan' => 'integer',
        'status' => 'integer',
        'flag_read' => 'integer',
        'id_permohonan' => 'integer',
        'id_layanan' => 'integer',
        'id_kontrak' => 'integer',
        'id_alamat' => 'integer',
        'jenis_layanan_1' => 'integer',
        'jenis_layanan_2' => 'integer',
        'jenis_tld' => 'integer',
        'ttd_by' => 'integer',
        'file_lhu' => 'integer',
        'created_by' => 'integer',
        'is_have_tld' => 'integer',
        'is_zerocek' => 'integer'
    ];

    public function getPermohonanHashAttribute()
    {
        return $this->id_permohonan ? encryptor($this->id_permohonan) : null;
    }

    public function getKontrakHashAttribute()
    {
        return $this->id_kontrak ? encryptor($this->id_kontrak) : null;
    }

    public function getTtdImageAttribute()
    {
        if ($this->ttd) {
            $ttd = Master_ttd::withTrashed()->where('id', $this->ttd)->first();
            if ($ttd) {
                $base64 = $ttd->image_blob;
                return "data:image/png;base64,{$base64}";
            }
        }

        return null;
    }

    public function jenisTld()
    {
        return $this->belongsTo(Master_jenistld::class, 'jenis_tld', 'id_jenisTld');
    }

    public function jenis_layanan()
    {
        return $this->belongsTo(Master_jenisLayanan::class, 'jenis_layanan_2', 'id_jenisLayanan');
    }

    public function jenis_layanan_parent()
    {
        return $this->belongsTo(Master_jenisLayanan::class, 'jenis_layanan_1', 'id_jenisLayanan');
    }

    public function layanan_jasa()
    {
        return $this->belongsTo(Master_layanan_jasa::class, 'id_layanan', 'id_layanan');
    }

    public function permohonan_pengguna()
    {
        return $this->hasMany(Permohonan_pengguna::class, 'id_permohonan', 'id_permohonan');
    }

    public function pelanggan()
    {
        return $this->belongsTo(User::class, 'created_by', 'id')->withTrashed()->withTrashed();
    }

    public function tandaterima()
    {
        return $this->hasMany(Permohonan_tandaterima::class, 'id_permohonan', 'id_permohonan');
    }

    public function kontrak()
    {
        return $this->belongsTo(Kontrak::class, 'id_kontrak', 'id_kontrak');
    }

    public function invoice()
    {
        return $this->hasOne(Keuangan::class, 'id_permohonan', 'id_permohonan');
    }

    public function lhu()
    {
        return $this->hasOne(Penyelia::class, 'id_permohonan', 'id_permohonan');
    }

    public function pengiriman()
    {
        return $this->belongsTo(Pengiriman::class, 'id_pengiriman', 'id_pengiriman');
    }

    public function file_lhu()
    {
        return $this->hasOne(Master_media::class, 'id', 'file_lhu');
    }

    public function dokumen()
    {
        return $this->hasMany(Permohonan_dokumen::class, 'id_permohonan', 'id_permohonan');
    }

    public function signature()
    {
        return $this->belongsTo(User::class, 'ttd_by', 'id')->withTrashed()->withTrashed();
    }

    /**
     * Accessor untuk mendapatkan data Kontrak_periode yang sesuai dengan id_kontrak dan periode dari permohonan ini.
     * Ini menggantikan relasi 'periodenow' untuk memastikan nilai `$this->periode` selalu terambil dengan benar
     * saat diakses pada satu instance model (lazy loading).
     *
     * @return \App\Models\Kontrak_periode|null
     */
    public function getPeriodenowAttribute()
    {
        return Kontrak_periode::where('id_kontrak', $this->id_kontrak)
            ->where('periode', $this->periode)
            ->first();
    }

    public function rincian_list_tld()
    {
        return $this->hasMany(Permohonan_tld::class, 'id_permohonan', 'id_permohonan');
    }

    public function logs()
    {
        return $this->morphMany(Log_proses::class, 'subject')->orderBy('created_at', 'desc');
    }

    public function permohonan_detail()
    {
        return $this->hasMany(Permohonan_detail::class, 'id_permohonan', 'id_permohonan');
    }

    public function alamat()
    {
        return $this->belongsTo(Master_alamat::class, 'id_alamat', 'id_alamat');
    }
}
