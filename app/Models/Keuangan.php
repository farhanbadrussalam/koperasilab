<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_keuangan
 * @property int|null $id_permohonan
 * @property string|null $id_pengiriman
 * @property int|null $id_jenis_pembayaran
 * @property array|null $variabel_jenis_pembayaran
 * @property string|null $no_invoice
 * @property int|null $status
 * @property int|null $ppn
 * @property int|null $pph
 * @property array|null $document_faktur
 * @property array|null $bukti_bayar
 * @property array|null $bukti_bayar_pph
 * @property string|null $ttd
 * @property int|null $ttd_by
 * @property int|null $plt
 * @property int|null $total_harga
 * @property string|null $paid_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Keuangan_diskon> $diskon
 * @property-read int|null $diskon_count
 * @property-read mixed $keuangan_hash
 * @property-read mixed $media
 * @property-read mixed $media_bukti_bayar
 * @property-read mixed $media_bukti_bayar_pph
 * @property-read mixed $permohonan_hash
 * @property-read \App\Models\Jenis_pembayaran|null $metode_pembayaran
 * @property-read \App\Models\Pengiriman|null $pengiriman
 * @property-read \App\Models\Permohonan|null $permohonan
 * @property-read \App\Models\User|null $usersig
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan query()
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan whereBuktiBayar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan whereBuktiBayarPph($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan whereDocumentFaktur($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan whereIdJenisPembayaran($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan whereIdKeuangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan whereIdPengiriman($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan whereIdPermohonan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan whereNoInvoice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan wherePaidAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan wherePlt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan wherePph($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan wherePpn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan whereTotalHarga($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan whereTtd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan whereTtdBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Keuangan whereVariabelJenisPembayaran($value)
 * @mixin \Eloquent
 */
class Keuangan extends Model
{
    use HasFactory;

    protected $table = "keuangan";
    protected $primaryKey = 'id_keuangan';

    protected $fillable = [
        'id_keuangan',
        'id_permohonan',
        'id_jenis_pembayaran',
        'variabel_jenis_pembayaran',
        'no_invoice',
        'status',
        'ppn',
        'pph',
        'document_faktur',
        'bukti_bayar',
        'bukti_bayar_pph',
        'ttd',
        'ttd_by',
        'plt',
        'total_harga',
        'created_at',
        'paid_at',
        'created_by'
    ];

    // Casting kolom sebagai array
    protected $casts = [
        'document_faktur' => 'array',
        'bukti_bayar' => 'array',
        'bukti_bayar_pph' => 'array',
        'status' => 'integer',
        'id_keuangan' => 'integer',
        'id_permohonan' => 'integer',
        'id_jenis_pembayaran' => 'integer',
        'variabel_jenis_pembayaran' => 'array',
        'ppn' => 'integer',
        'pph' => 'integer',
        'ttd_by' => 'integer',
        'total_harga' => 'integer',
        'created_by' => 'integer'
    ];

    protected $hidden = [
        'id_keuangan',
        'id_permohonan',
        'bukti_bayar',
        'bukti_bayar_pph',
        'document_faktur'
    ];

    protected $appends = [
        'keuangan_hash',
        'permohonan_hash',
        'media',
        'media_bukti_bayar',
        'media_bukti_bayar_pph'
    ];

    public function getKeuanganHashAttribute()
    {
        return $this->id_keuangan ? encryptor($this->id_keuangan) : null;
    }

    public function getPermohonanHashAttribute()
    {
        return encryptor($this->id_permohonan);
    }

    public function getMediaAttribute()
    {
        $decodedIds = $this->document_faktur;
        $decodedIds = is_array($decodedIds) ? $decodedIds : [];

        return Master_media::whereIn('id', $decodedIds)->get();
    }

    public function getMediaBuktiBayarAttribute()
    {
        $decodedIds = $this->bukti_bayar;
        $decodedIds = is_array($decodedIds) ? $decodedIds : [];

        return Master_media::whereIn('id', $decodedIds)->get();
    }

    public function getMediaBuktiBayarPphAttribute()
    {
        $decodedIds = $this->bukti_bayar_pph;
        $decodedIds = is_array($decodedIds) ? $decodedIds : [];

        return Master_media::whereIn('id', $decodedIds)->get();
    }

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class, 'id_permohonan', 'id_permohonan');
    }

    public function diskon()
    {
        return $this->hasMany(Keuangan_diskon::class, 'id_keuangan', 'id_keuangan');
    }

    // public function media_bayar(){
    //     return $this->belongsTo(Master_media::class, 'bukti_bayar', 'id');
    // }

    // public function media_bayar_pph(){
    //     return $this->belongsTo(Master_media::class, 'bukti_bayar_pph', 'id');
    // }

    public function usersig(){
        return $this->belongsTo(user::class, 'ttd_by', 'id');
    }

    public function pengiriman(){
        return $this->belongsTo(Pengiriman::class, 'id_pengiriman', 'id_pengiriman');
    }

    public function metode_pembayaran(){
        return $this->belongsTo(Jenis_pembayaran::class, 'id_jenis_pembayaran', 'id_jenis_pembayaran');
    }
}
