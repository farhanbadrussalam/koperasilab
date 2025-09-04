<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;

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
        'data_radiasi'
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
        return encryptor($this->id_kontrak);
    }

    public function getDocumentKontrakAttribute()
    {
        return Permohonan_dokumen::with("usersig")->where('id_kontrak', $this->id_kontrak)->where('jenis', 'kontrak')->first();
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
}
