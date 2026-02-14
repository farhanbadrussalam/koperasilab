<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;

/**
 * @property int $id_kontrak
 * @property int|null $id_layanan
 * @property int|null $id_keuangan
 * @property int|null $jenis_layanan_1
 * @property int|null $jenis_layanan_2
 * @property string|null $tipe_kontrak
 * @property string|null $no_kontrak
 * @property int|null $jenis_tld
 * @property string|null $periode_pemakaian
 * @property array|null $periode_next
 * @property int|null $jumlah_pengguna
 * @property int|null $jumlah_kontrol
 * @property int|null $harga_layanan
 * @property string|null $ttd
 * @property int|null $ttd_by
 * @property int|null $total_harga
 * @property int $status
 * @property string|null $note
 * @property int|null $file_lhu
 * @property int|null $id_pelanggan
 * @property int|null $is_have_tld
 * @property int|null $is_zerocek
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permohonan_dokumen> $dokumen
 * @property-read int|null $dokumen_count
 * @property-read mixed $data_radiasi
 * @property-read mixed $document_kontrak
 * @property-read mixed $kontrak_hash
 * @property-read mixed $periode_all
 * @property-read \App\Models\Keuangan|null $invoice
 * @property-read \App\Models\Master_jenistld|null $jenisTld
 * @property-read \App\Models\Master_jenisLayanan|null $jenis_layanan
 * @property-read \App\Models\Master_jenisLayanan|null $jenis_layanan_parent
 * @property-read \App\Models\Master_layanan_jasa|null $layanan_jasa
 * @property-read \App\Models\User|null $pelanggan
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Kontrak_tld> $pengguna
 * @property-read int|null $pengguna_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pengiriman> $pengiriman
 * @property-read int|null $pengiriman_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Kontrak_periode> $periode
 * @property-read int|null $periode_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Kontrak_tld> $rincian_list_tld
 * @property-read int|null $rincian_list_tld_count
 * @property-read \App\Models\User|null $signature
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Master_tld> $tld_aktif
 * @property-read int|null $tld_aktif_count
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak query()
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereFileLhu($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereHargaLayanan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereIdKeuangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereIdKontrak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereIdLayanan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereIdPelanggan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereIsHaveTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereIsZerocek($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereJenisLayanan1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereJenisLayanan2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereJenisTld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereJumlahKontrol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereJumlahPengguna($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereNoKontrak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak wherePeriodeNext($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak wherePeriodePemakaian($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereTipeKontrak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereTotalHarga($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereTtd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereTtdBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kontrak whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Kontrak extends Model
{
    use HasFactory;

    protected $table = "kontrak";
    protected $primaryKey = 'id_kontrak';

    protected $fillable = [
        'id_layanan',
        'id_keuangan',
        'jenis_layanan_2',
        'jenis_layanan_1',
        'tipe_kontrak',
        'no_kontrak',
        'jenis_tld',
        'periode_next',
        'jumlah_pengguna',
        'jumlah_kontrol',
        'total_harga',
        'harga_layanan',
        'ttd',
        'ttd_by',
        'status',
        'note',
        'file_lhu',
        'id_pelanggan',
        'is_have_tld',
        'is_zerocek',
        'created_by',
        'created_at'
    ];

    protected $hidden = [
        'id_kontrak'
    ];

    protected $appends = [
        'kontrak_hash',
        'document_kontrak',
        'periode_all',
        'data_radiasi',
    ];

    protected $casts = [
        'list_tld' => 'array',
        'periode_next' => 'array',
        'jumlah_pengguna' => 'integer',
        'jumlah_kontrol' => 'integer',
        'total_harga' => 'integer',
        'harga_layanan' => 'integer',
        'status' => 'integer',
        'created_by' => 'integer',
        'id_kontrak' => 'integer',
        'id_layanan' => 'integer',
        'id_keuangan' => 'integer',
        'jenis_layanan_2' => 'integer',
        'jenis_layanan_1' => 'integer',
        'jenis_tld' => 'integer',
        'id_pelanggan' => 'integer',
        'file_lhu' => 'integer',
        'is_have_tld' => 'integer'
    ];

    public function getKontrakHashAttribute()
    {
        return $this->id_kontrak ? encryptor($this->id_kontrak) : null;
    }

    public function getDocumentKontrakAttribute()
    {
        return Permohonan_dokumen::with("usersig")->where('id_kontrak', $this->id_kontrak)->whereIn('jenis', ['kontrak', 'KontrakPengujian'])->get();
    }

    public function getDataRadiasiAttribute()
    {
        $dataPengguna = Kontrak_tld::with('pengguna')->where('id_kontrak', $this->id_kontrak)->whereNotNull('id_pengguna')->get();
        $radiasi = array();
        $radiasi = array_merge(...$dataPengguna->pluck('pengguna.radiasi')->filter()->toArray());
        return $radiasi;
    }

    public function getPeriodeAllAttribute()
    {
        $periode = Kontrak_periode::where('id_kontrak', $this->id_kontrak)->get();
        $jmlBulan = 0;
        $periodeAwal = "";
        $periodeAkhir = "";
        $jmlPeriode = 0;

        foreach ($periode as $key => $item) {
            // mengambil periode awal
            if($item->periode == 1) {
                $periodeAwal = $item->start_date;
            }

            // mengambil periode akhir
            if($key == count($periode) - 1) {
                $periodeAkhir = $item->end_date;
            }

            if($item->periode != 0){
                $jmlPeriode++;
            }
        }

        $jmlBulan = Carbon::parse($periodeAwal)->diffInMonths(Carbon::parse($periodeAkhir));

        $result['jml_all_bulan'] = $jmlBulan + 1;
        $result['periode_awal'] = $periodeAwal;
        $result['periode_akhir'] = $periodeAkhir;
        $result['jml_periode'] = $jmlPeriode;
        return $result;
    }

    public function jenisTld(){
        return $this->belongsTo(Master_jenistld::class,'jenis_tld', 'id_jenisTld');
    }

    public function jenis_layanan(){
        return $this->belongsTo(Master_jenisLayanan::class,'jenis_layanan_2', 'id_jenisLayanan');
    }

    public function jenis_layanan_parent(){
        return $this->belongsTo(Master_jenisLayanan::class,'jenis_layanan_1', 'id_jenisLayanan');
    }

    public function layanan_jasa() {
        return $this->belongsTo(Master_layanan_jasa::class, 'id_layanan', 'id_layanan');
    }

    public function pengguna() {
        return $this->hasMany(Kontrak_tld::class, 'id_kontrak', 'id_kontrak');
    }

    public function kontrak_tld(){
        return $this->hasMany(Kontrak_tld::class, 'id_kontrak', 'id_kontrak');
    }

    public function pelanggan() {
        return $this->belongsTo(User::class, 'id_pelanggan', 'id');
    }

    public function periode(){
        return $this->hasMany(Kontrak_periode::class, 'id_kontrak', 'id_kontrak');
    }

    public function pengiriman(){
        return $this->hasMany(Pengiriman::class, 'id_kontrak', 'id_kontrak');
    }

    public function invoice() {
        return $this->belongsTo(Keuangan::class, 'id_keuangan', 'id_keuangan');
    }

    public function rincian_list_tld(){
        return $this->hasMany(Kontrak_tld::class, 'id_kontrak', 'id_kontrak');
    }

    public function tld_aktif(){
        return $this->hasMany(Master_tld::class, 'digunakan', 'no_kontrak');
    }

    public function signature(){
        return $this->belongsTo(User::class, 'ttd_by', 'id');
    }

    public function dokumen(){
        return $this->hasMany(Permohonan_dokumen::class, 'id_kontrak', 'id_kontrak');
    }

    public function permohonan(){
        return $this->belongsTo(Permohonan::class, 'id_kontrak', 'id_kontrak');
    }

    public function kontrak_detail(){
        return $this->hasMany(Kontrak_detail::class, 'id_kontrak', 'id_kontrak')->where('status', 1);
    }
}
