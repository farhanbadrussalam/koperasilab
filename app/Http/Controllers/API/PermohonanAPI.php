<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Traits\RestApi;

use App\Models\Permohonan;
use App\Models\Permohonan_dokumen;
use App\Models\Permohonan_pengguna;
use App\Models\Permohonan_tandaterima;
use App\Models\Permohonan_tld;
use App\Models\Permohonan_detail;

use App\Models\Documents;
use App\Models\Master_layanan_jasa;
use App\Models\Master_jenisLayanan;
use App\Models\Master_media;
use App\Models\Master_radiasi;
use App\Models\Master_price;
use App\Models\Master_jenistld;
use App\Models\Master_tld;
use App\Models\Master_pengguna;
use App\Models\Kontrak;
use App\Models\Kontrak_pengguna;
use App\Models\Kontrak_periode;
use App\Models\Kontrak_tld;
use App\Models\User;

use App\Http\Controllers\MediaController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\NotifController;
use App\Http\Controllers\API\TldAPI;
use App\Models\Kontrak_detail;
use App\Models\Kontrak_map;
use App\Services\Notifier;

use Auth;
use DB;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Log;

class PermohonanAPI extends Controller
{
    use RestApi;
    protected $media, $log, $tld, $global, $pagination, $notif, $keuangan, $penyelia;

    public function __construct(){
        $this->media = resolve(MediaController::class);
        $this->log = resolve(LogController::class);
        $this->tld = resolve(TldAPI::class);
        $this->notif = resolve(NotifController::class);
        $this->keuangan = resolve(KeuanganAPI::class);
        $this->penyelia = resolve(PenyeliaAPI::class);
        $this->global = config('customvariabel');
    }

    public function tambahAdendum(Request $request){
        DB::beginTransaction();
        try {
            $note = $request->note ? $request->note : null;
            $pengguna = $request->pengguna ? json_decode($request->pengguna) : false;
            $kontrol = $request->kontrol ? json_decode($request->kontrol) : false;
            $idPeriode = $request->idPeriode ? decryptor($request->idPeriode) : false;
            $idKontrak = $request->id_kontrak ? decryptor($request->id_kontrak) : false;
            $totalHarga = $request->sub_total ? $request->sub_total : false;
            $isZeroCek = (int) $request->is_zerocek;

            $dataKontrak = Kontrak::find($idKontrak);
            $dataPeriode = Kontrak_periode::find($idPeriode);

            $jumPenggunaBaru = count(array_filter($pengguna, function($item) {
                return $item->status == 'baru';
            }));

            $jumKontrolBaru = count(array_filter($kontrol, function($item) {
                return $item->status == 'baru';
            }));

            $data = array();

            $data['id_layanan'] = $dataKontrak->id_layanan;
            $data['jenis_layanan_1'] = $dataKontrak->jenis_layanan_1;
            $data['jenis_layanan_2'] = $dataKontrak->jenis_layanan_2;
            $data['tipe_kontrak'] = 'adendum';
            $data['id_kontrak'] = $idKontrak;
            $data['periode'] = $dataPeriode->periode;
            $data['jenis_tld'] = $dataKontrak->jenis_tld;
            $data['jumlah_pengguna'] = $jumPenggunaBaru;
            $data['jumlah_kontrol'] = $jumKontrolBaru;
            $data['total_harga'] = $totalHarga;
            $data['harga_layanan'] = $dataKontrak->harga_layanan;
            $data['note'] = $note;
            $data['is_zerocek'] = $isZeroCek;
            $data['is_have_tld'] = $dataKontrak->is_have_tld;
            $data['status'] = 1;
            $data['created_by'] = Auth::user()->id;

            $permohonan = Permohonan::create($data);

            if($permohonan) {
                $this->saveTldAdendum($permohonan->id_permohonan, $pengguna, $kontrol);
            }

            DB::commit();

            return $this->output(array('msg' => 'Adendum berhasil disimpan'));
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function tambahPengajuan(Request $request)
    {
        DB::beginTransaction();
        try {
            $idPermohonan = $request->idPermohonan ? decryptor($request->idPermohonan) : false;
            $idLayanan = $request->idLayanan ? decryptor($request->idLayanan) : false;
            $jenisLayanan1 = $request->jenisLayanan1 ? decryptor($request->jenisLayanan1) : false;
            $jenisLayanan2 = $request->jenisLayanan2 ? decryptor($request->jenisLayanan2) : false;

            $tipeKontrak = $request->tipeKontrak ? $request->tipeKontrak : false;
            $idKontrak = $request->idKontrak ? decryptor($request->idKontrak) : null;
            $jenisTld = $request->jenisTld ? decryptor($request->jenisTld) : null;
            $periode = $request->has('periode') ? $request->periode : false;
            $jumlahPengguna = $request->jumlahPengguna ? $request->jumlahPengguna : false;
            $jumlahKontrol = $request->jumlahKontrol ? $request->jumlahKontrol : false;
            $totalHarga = $request->totalHarga ? $request->totalHarga : false;
            $hargaLayanan = $request->hargaLayanan ? $request->hargaLayanan : false;
            $dataTld = $request->dataTld ? json_decode($request->dataTld) : false;
            $tldKontrol = $request->tldKontrol ? json_decode($request->tldKontrol) : false;
            $createBy = $request->createBy ? decryptor($request->createBy) : false;
            $status = $request->status ? $request->status : 1;
            $periodePemakaian = $request->periodePemakaian ? $request->periodePemakaian : false;
            $periodeNext = $request->periodeNext ? $request->periodeNext : false;
            $pic = $request->pic ? $request->pic : false;
            $noHp = $request->noHp ? unmask($request->noHp) : false;
            $alamat = $request->alamat ? decryptor($request->alamat) : false;
            $tldPengguna = $request->tldPengguna ? json_decode($request->tldPengguna) : false;
            $listTld = $request->listTld ? json_decode($request->listTld) : false;
            $note = $request->has('note') ? $request->note : false;

            $haveTld = $request->has('haveTld') ? $request->haveTld : 0;
            $isUseZeroCek = $request->has('is_zerocek') ? $request->is_zerocek : 1;

            if ($periodePemakaian) {
                if (is_string($periodePemakaian)) {
                    $periodePemakaian = json_decode($periodePemakaian, true); // Use true for associative array
                }
                // Add validation to ensure $periodePemakaian is now an array after decoding if needed
                if (!is_array($periodePemakaian) && $periodePemakaian != false) {
                  throw new \Exception("Invalid periodePemakaian format. Must be a JSON string or an array.");
                }
            } else {
                $periodePemakaian = false;
            }

            $periodeNext ? $periodeNext = json_decode($periodeNext, true) : false;

            $data = array();

            $idLayanan && $data['id_layanan'] = $idLayanan;
            $jenisLayanan1 && $data['jenis_layanan_1'] = $jenisLayanan1;
            $jenisLayanan2 && $data['jenis_layanan_2'] = $jenisLayanan2;

            $tipeKontrak && $data['tipe_kontrak'] = $tipeKontrak;
            $idKontrak && $data['id_kontrak'] = $idKontrak;
            $periodePemakaian && $data['periode_pemakaian'] = $periodePemakaian;
            $periodeNext && $data['periode_next'] = $periodeNext;
            $periode !== false && $data['periode'] = $periode;
            $jenisTld && $data['jenis_tld'] = $jenisTld;
            $jumlahPengguna && $data['jumlah_pengguna'] = $jumlahPengguna;
            $jumlahKontrol && $data['jumlah_kontrol'] = $jumlahKontrol;
            $totalHarga && $data['total_harga'] = unmask($totalHarga);
            $hargaLayanan && $data['harga_layanan'] = $hargaLayanan;
            $createBy && $data['created_by'] = $createBy;
            $pic && $data['pic'] = $pic;
            $noHp && $data['no_hp'] = $noHp;
            $alamat && $data['id_alamat'] = $alamat;
            $note != false && $data['note'] = $note;
            $status == 1 ? $data['note'] = null : false;

            $data['is_zerocek'] = $isUseZeroCek;
            $data['is_have_tld'] = $haveTld;

            $status && $data['status'] = $status;
            $data['flag_read'] = 0;

            // jika tipe kontraknya adalah "kontrak lama" akan mengambil data dari kontrak sebelumnya
            if($tipeKontrak == 'kontrak lama'){
                $kontrak = Kontrak::with('periode')->find($idKontrak);
                if($kontrak){
                    $data['id_layanan'] = $kontrak->id_layanan;
                    $data['jenis_tld'] = $kontrak->jenis_tld;
                    $data['jumlah_pengguna'] = $kontrak->jumlah_pengguna;
                    $data['jumlah_kontrol'] = $kontrak->jumlah_kontrol;
                    $data['total_harga'] = 0;
                    $data['harga_layanan'] = $kontrak->harga_layanan;
                }
            } else if($tipeKontrak == 'kontrak baru'){
                $data['created_at'] = date('Y-m-d H:i:s');
            }
            // Save to db
            $permohonan = Permohonan::updateOrCreate(
                ['id_permohonan' => $idPermohonan],
                $data
            );


            if ($dataTld) {
                $this->saveTld($permohonan->id_permohonan, $dataTld);
            }

            if ($tipeKontrak === 'kontrak lama') {
                $kontrakPeriode = Kontrak_periode::where('id_kontrak', $idKontrak)
                    ->where('periode', $periode)->first();

                if ($kontrakPeriode && !empty($listTld)) {
                    // $isPeriodOne = $kontrakPeriode->count_tld === 1;
                    // $tldField = $isPeriodOne ? 'tld_1' : 'tld_2';
                    // $statusField = $isPeriodOne ? 'status_tld_1' : 'status_tld_2';

                    // foreach ($listTld as $value) {
                    //     $id = decryptor($value);
                    //     $id_tld = null;
                    //     $idKontrakDetail = null;

                    //     if ($request->source === 'map') {
                    //         $data_ = Kontrak_map::find($id);
                    //         $id_tld = $data_?->id_tld;
                    //         $idKontrakDetail = $data_?->id_kontrak_detail;
                    //     } else {
                    //         $data_ = Kontrak_detail::find($id);
                    //         $id_tld = $data_?->{$tldField};
                    //         $idKontrakDetail = $data_?->id;
                    //     }

                    //     if ($id_tld) {
                    //         $kontrakDetail = Kontrak_detail::where('id', $idKontrakDetail)->where('status', 1)->first()
                    //             ?? Kontrak_detail::where($tldField, $id_tld)->where('id_kontrak', $idKontrak)->where('status', 1)->first();

                    //         $kontrakDetail?->update([$statusField => 3]);
                    //     }
                    // }
                    $kontrakPeriode->update(['id_permohonan' => $permohonan->id_permohonan]);
                }
            }

            if($tipeKontrak == 'kontrak baru' && $jenisLayanan2 == 3){ // Evaluasi
                // Simpan dokumen Permohonan
                Permohonan_dokumen::create(array(
                    'id_permohonan' => $permohonan->id_permohonan,
                    'created_by' => Auth::user()->id,
                    'nama' => 'Permohonan Evaluasi TLD',
                    'jenis' => 'permohonan',
                    'status' => 1,
                    'nomer' => null
                ));
            }

            if($haveTld == 0){
                Permohonan_detail::where('id_permohonan', $permohonan->id_permohonan)->get()->each(function ($item) {
                    if($item->id_tld){
                        Master_tld::find($item->id_tld)->update(['status' => 0]);
                    }
                    $item->update(['id_tld' => null]);
                });
            }

            if($request->is_pengembalian){
                $nextPeriode = Kontrak_periode::where('id_kontrak', $idKontrak)->orderBy('periode', 'desc')->first();

                if($nextPeriode){
                    $nextPeriode = $nextPeriode->periode + 1;
                    $countTld = $nextPeriode % 2 == 1 ? 1 : 2;

                    // cek apakah pengembalian dengan countTLd udah ada atau belum
                    $cekPengembalian = Kontrak_periode::where('id_kontrak', $idKontrak)
                        ->where('start_date', $request->pengembalian_start)
                        ->where('end_date', $request->pengembalian_end)
                        ->whereIn('status', [2, 3])
                        ->first();

                    if(!$cekPengembalian){
                        Kontrak_periode::create([
                            'id_kontrak' => $idKontrak,
                            'periode' => $nextPeriode,
                            'count_tld' => $countTld,
                            'start_date' => $request->pengembalian_start,
                            'end_date' => $request->pengembalian_end,
                            'status' => 3,
                            'created_by' => Auth::user()->id
                        ]);
                    }
                }
            }

            if($permohonan->status == 1){
                $userQuery = User::role('Staff Admin');
                $us = Auth::user();
                $dataNotif = array(
                    'pesan' => 'Permohonan baru telah dibuat! Silahkan verifikasi',
                    'url' => '/staff/permohonan/verifikasi/'.$permohonan->permohonan_hash,
                    'event_id' => $permohonan->permohonan_hash,
                    'event' => 'Permohonan',
                    'user_id' => "{$us->id}|{$us->name}",
                    'perusahaan_id' => "{$us->id_perusahaan}|{$us->perusahaan->nama_perusahaan}",
                );
                Notifier::send($userQuery, $dataNotif);
            }

            DB::commit();

            if($permohonan) {
                return $this->output(array('msg' => 'Data berhasil disimpan!', 'id' => $permohonan->permohonan_hash));
            }
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function tambahPengguna(Request $request)
    {
        $validator = $request->validate([
            'idPermohonan' => 'required',
            'idPengguna' => 'required',
        ]);

        DB::beginTransaction();
        try {
            $idPermohonan = decryptor($request->idPermohonan);
            $idPengguna = decryptor($request->idPengguna);
            $idTld = $request->has('idTld') ? decryptor($request->idTld) : null;

            Permohonan_detail::create(array(
                'id_permohonan' => $idPermohonan,
                'jenis' => 'pengguna',
                'type' => 'baru',
                'id_pengguna_divisi' => $idPengguna,
                'status' => 1,
                'created_by' => Auth::user()->id
            ));

            // pengecekan divisi pengguna
            $dataPengguna = Master_pengguna::where('id_pengguna', $idPengguna)->first();
            $permohonanTld = Permohonan_detail::where('id_permohonan', $idPermohonan)
                ->where('jenis', 'kontrol')
                ->where('id_pengguna_divisi', $dataPengguna->id_divisi)
                ->first();

            if(!$permohonanTld) {
                Permohonan_detail::create(array(
                    'id_permohonan' => $idPermohonan,
                    'jenis' => 'kontrol',
                    'type' => 'baru',
                    'id_pengguna_divisi' => $dataPengguna->id_divisi,
                    'status' => 1,
                    'created_by' => Auth::user()->id
                ));
            }

            Master_pengguna::find($idPengguna)?->update(['status' => 2]);
            Master_tld::find($idTld)?->update(['status' => 1]);
            DB::commit();

            return $this->output(array('msg' => 'Pengguna Behasil ditambahkan'));
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function action_tld(Request $request)
    {
        DB::beginTransaction();
        try {
            $id = decryptor($request->id);
            $idTld = $request->id_tld ? (int) decryptor($request->id_tld) : false;
            $idPermohonan = $request->id_permohonan ? decryptor($request->id_permohonan) : false;
            $idDivisi = $request->id_divisi ? $request->id_divisi : false;
            $jenis = $request->jenis ? $request->jenis : false;
            $type = $request->type ? $request->type : false;
            $aksi = $request->aksi;

            $dataTld = Permohonan_detail::find($id);

            if($idTld){
                if($dataTld->id_tld){
                    Master_tld::find($dataTld->id_tld)->update(['status' => 0]);
                }
                $dataTld->update([
                    'id_tld' => $idTld,
                ]);
                Master_tld::find($idTld)->update(['status' => 1]);
            }

            $arr_body = array();
            $idDivisi ? $arr_body['id_pengguna_divisi'] = $idDivisi : false;
            $jenis ? $arr_body['jenis'] = $jenis : false;
            $type ? $arr_body['type'] = $type : false;
            $idPermohonan ? $arr_body['id_permohonan'] = $idPermohonan : false;

            if($aksi == 'tambah'){
                $arr_body['status'] = 1;
                $arr_body['created_by'] = Auth::user()->id;
                $dataTld = Permohonan_detail::create($arr_body);
            } else if($aksi == 'hapus') {
                Permohonan_detail::where('id_permohonan', $idPermohonan)
                    ->where('jenis', $jenis)
                    ->when($idDivisi, function ($query) use ($idDivisi) {
                        return $query->where('id_pengguna_divisi', $idDivisi);
                    }, function ($query) {
                        return $query->whereNull('id_pengguna_divisi');
                    })
                    ->orderBy('id', 'desc')
                    ->limit(1)
                    ->delete();
            }

            DB::commit();
            return $this->output(array('msg' => 'Data berhasil disimpan!'));
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    private function saveTld($idPermohonan, $tldItems)
    {
        $permohonan = Permohonan::with('kontrak')->find($idPermohonan);
        if (!$permohonan) {
            return;
        }

        foreach ($tldItems as $item) {
            $id = (int) decryptor($item->id);
            $newTldId = (int) decryptor($item->tld);
            $idTargetPengguna = isset($item->id_target_pengguna) ? (int) decryptor($item->id_target_pengguna) : null;
            $source = $item->source;

            // Tentukan data source berdasarkan tipe
            $sourceData = ($source === 'map')
                ? Kontrak_map::where('id_map', $id)->first()
                : Kontrak_detail::find($id);

            if (!$sourceData) {
                continue;
            }

            $kontrakPeriode = Kontrak_periode::where('id_kontrak', $sourceData->id_kontrak)
                ->where('periode', $permohonan->periode)
                ->first();

            if (!$kontrakPeriode) {
                continue;
            }

            // Tentukan field berdasarkan ganjil genap periode
            $isPeriodOne = $kontrakPeriode->count_tld == 1;
            $tldField = $isPeriodOne ? 'tld_1' : 'tld_2';
            $periodeField = $isPeriodOne ? 'periode_tld_1' : 'periode_tld_2';
            $statusField = $isPeriodOne ? 'status_tld_1' : 'status_tld_2';

            $updateFields = [
                $tldField => $newTldId,
                $periodeField => $permohonan->periode,
                $statusField => 3
            ];

            if ($source === 'map') {
                if ($sourceData->id_tld === null) {
                    Kontrak_detail::where('id', $sourceData->id_kontrak_detail)->update($updateFields);
                }
            } else {
                // Reset status TLD lama jika ada
                if ($oldTldId = $sourceData->{$tldField}) {
                    Master_tld::find($oldTldId)?->update(['status' => 0, 'digunakan' => null]);
                }

                $sourceData->update($updateFields);

                // Update status TLD baru menjadi aktif
                Master_tld::find($newTldId)?->update([
                    'status' => 1,
                    'digunakan' => $permohonan->kontrak?->no_kontrak
                ]);
            }

            // Buat record detail permohonan
            Permohonan_detail::create([
                'id_permohonan'      => $idPermohonan,
                'id_pengguna_divisi' => $idTargetPengguna ?? $sourceData->id_pengguna_divisi,
                'id_tld'             => $newTldId,
                'jenis'              => $sourceData->jenis,
                'status'             => 1,
                'type'               => $item->type,
                'pengguna_lama'      => $idTargetPengguna ? $sourceData->id_pengguna_divisi : null,
                'created_by'         => Auth::user()->id
            ]);
        }
    }

    private function saveTldAdendum($idPermohonan, $tldPengguna, $tldKontrol){
        foreach ($tldPengguna as $value) {
            $idPengguna = null;
            $idPenggunaLama = null;
            $idTld = null;
            if($value->status == 'baru'){
                $idPengguna = (int) decryptor($value->pengguna);
                $idTld = isset($value->tld) ? (int) decryptor($value->tld) : null;
            } else if($value->status == 'ganti'){
                $permohonan = Permohonan::find($idPermohonan);
                $idPengguna = (int) decryptor($value->pengguna_baru);
                $idPenggunaLama = (int) decryptor($value->pengguna);
                $kontrakDetail = Kontrak_detail::where('id_kontrak', $permohonan->id_kontrak)
                                ->where('id_pengguna_divisi', $idPenggunaLama)
                                ->first();
                $kontrakPeriode = Kontrak_periode::where('id_kontrak', $permohonan->id_kontrak)->where('periode', $permohonan->periode)->first();

                $idTld = $kontrakPeriode->count_tld == 1 ? $kontrakDetail->tld_1 : $kontrakDetail->tld_2;
            }
            $data = array(
                'id_permohonan' => $idPermohonan,
                'id_pengguna_divisi' => $idPengguna,
                'jenis' => 'pengguna',
                'status' => 1,
                'type' => $value->status,
                'pengguna_lama' => $idPenggunaLama,
                'created_by' => Auth::user()->id,
                'id_tld' => $idTld
            );

            Permohonan_detail::create($data);
            Master_pengguna::find($idPengguna)?->update(['status' => 2]);
            Master_tld::find($idTld)?->update(['status' => 1]);
        }

        foreach($tldKontrol as $value){
            $idTld = isset($value->tld) ? (int) decryptor($value->tld) : null;
            $data = array(
                'id_permohonan' => $idPermohonan,
                'id_pengguna_divisi' => null,
                'jenis' => 'kontrol',
                'status' => 1,
                'type' => 'baru',
                'created_by' => Auth::user()->id,
                'id_tld' => $idTld
            );

            Permohonan_detail::create($data);
            Master_tld::find($idTld)?->update(['status' => 1]);
        }

        // buat dokumen surat adendum
        $template = Documents::where('jenis', 'body')
            ->where('name', 'PermohonanAdendum')
            ->where('status', 1)
            ->first();

        $dataParams = array(
            'id_permohonan' => $idPermohonan,
            'created_by' => Auth::user()->id,
            'nama' => 'Permohonan Adendum',
            'jenis' => 'adendum',
            'id_doc_template' => $template->id_doc,
            'status' => 1,
            'nomer' => generateNoDokumen('adendum', $idPermohonan)
        );

        Permohonan_dokumen::create($dataParams);
    }

    public function tambahTandaterima(Request $request)
    {
        $validator = $request->validate([
            'idPermohonan' => 'required'
        ]);

        $tandaterima = $request->tandaterima ? json_decode($request->tandaterima) : [];
        $idPermohonan = decryptor($request->idPermohonan);
        DB::beginTransaction();
        try {
            foreach ($tandaterima as $value) {
                $params = array(
                    'id_permohonan' => $idPermohonan,
                    'id_pertanyaan' => decryptor($value->id),
                    'jawaban' => $value->answer,
                    'note' => $value->note,
                    'created_by' => Auth::user()->id
                );

                Permohonan_tandaterima::create($params);
            }
            $dataTandaterima = Permohonan_tandaterima::where('id_permohonan', $idPermohonan)->get();

            // Simpan dokumen tandaterima
            $template = Documents::with('footer', 'header')
                        ->where('jenis', 'body')
                        ->where('name', 'TandaTerima')
                        ->where('status', '1')
                        ->first();

            $document = Permohonan_dokumen::create(array(
                'id_permohonan' => $idPermohonan,
                'id_kontrak' => Permohonan::find($idPermohonan)->id_kontrak,
                'created_by' => Auth::user()->id,
                'nama' => 'Tanda Terima Pengujian',
                'jenis' => 'tandaterima',
                'id_doc_template' => $template->id_doc,
                'status' => 1,
                'nomer' => generateNoDokumen('tandaterima')
            ));
            DB::commit();

            return $this->output(array('msg' => 'Data berhasil disimpan', 'information' => $dataTandaterima));
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }

    }

    public function destroyPengguna(string $idPengguna, string $idPermohonan)
    {
        $id = decryptor($idPengguna);
        $idPermohonan = decryptor($idPermohonan);

        DB::beginTransaction();
        try {
            $detail = Permohonan_detail::find($id);

            // Ganti where()->update() dengan find()->update() agar Observer terpanggil
            Master_pengguna::find($detail->id_pengguna_divisi)?->update(['status' => 1]);

            $detail->id_tld && Master_tld::find($detail->id_tld)->update(['status' => 0]);
            $detail->delete();

            DB::commit();
            return $this->output(array('msg' => 'Data berhasil dihapus'));
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }

    }

    public function destroyKontrol(string $idPermohonan, string $id)
    {
        $idPermohonan = decryptor($idPermohonan);
        $id = $id == 'default' ? null : $id;

        DB::beginTransaction();
        try {
            $permohonanDetail = Permohonan_detail::where('id_permohonan', $idPermohonan)
                ->when($id, function ($query) use ($id) {
                    return $query->where('id_pengguna_divisi', $id);
                }, function ($query) {
                    return $query->whereNull('id_pengguna_divisi');
                })->where('jenis', 'kontrol')->get();

            foreach ($permohonanDetail as $key => $value) {
                $value->id_tld && Master_tld::find($value->id_tld)->update(['status' => 0]);
                $value->delete();
            }
            DB::commit();
            return $this->output(array('msg' => 'Data berhasil dihapus'));
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function destroyPermohonan(string $id)
    {
        $id = (int) decryptor($id);

        DB::beginTransaction();
        try {
            $dataTld = Permohonan_detail::where('id_permohonan', $id)->get();
            $permohonan = Permohonan::where('id_permohonan', $id)->first();
            if($dataTld && (!$permohonan->id_kontrak || $permohonan->tipe_kontrak == 'adendum')) {
                foreach ($dataTld as $item) {
                    if($item->jenis == 'pengguna') {
                        Master_pengguna::find($item->id_pengguna_divisi)->update(['status' => 1]);
                    }
                    if($item->id_tld){
                        Master_tld::find($item->id_tld)->update(['status' => 0]);
                    }
                }
            }


            if($permohonan){
                Permohonan_pengguna::where('id_permohonan', $id)->get()->each->delete();
                Permohonan_detail::where('id_permohonan', $id)->get()->each->delete();

                if($permohonan->tipe_kontrak == 'kontrak lama') {
                    $kontrakPeriode = Kontrak_periode::where('id_permohonan', $id)->first();
                    if($kontrakPeriode){
                        $kontrakDetail = Kontrak_detail::where('id_kontrak', $permohonan->id_kontrak)->where('status', 1);
                        if($kontrakPeriode->count_tld === 1) {
                            $kontrakDetail->get()->each->update(array('status_tld_1' => 2));
                        } else {
                            $kontrakDetail->get()->each->update(array('status_tld_2' => 2));
                        }
                        $kontrakPeriode->update(array('id_permohonan' => null));
                    }
                } else if($permohonan->tipe_kontrak == 'adendum') {
                    Permohonan_dokumen::where('id_permohonan', $id)->where('jenis', 'adendum')->get()->each->delete();
                }
                $permohonan->delete();

                // hapus notifikasi
                $this->notif->deleteNotification(new Request([
                    'id_event' => $id,
                    'event' => 'Permohonan',
                ]));

                DB::commit();
                return $this->output(array('msg' => 'Data berhasil dihapus!'));
            }

            DB::commit();
            return $this->output(array('msg' => 'Data gagal dihapus'), 'Fail', 400);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function destroyTandaterima(string $id)
    {
        $id = decryptor($id);

        DB::beginTransaction();
        try {
            Permohonan_dokumen::where('id_permohonan', $id)->where('jenis', 'tandaterima')->get()->each->delete();
            $delete = Permohonan_tandaterima::where('id_permohonan', $id)->delete();
            DB::commit();

            if($delete){
                return $this->output(array('msg' => 'Data berhasil dihapus'));
            }

            return $this->output(array('msg' => 'Data gagal dihapus'), 'Fail', 400);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function listPengguna(Request $request)
    {
        $validator = $request->validate([
            'idPermohonan' => 'required'
        ]);

        $idPermohonan = decryptor($request->idPermohonan);
        $limit = $request->has('limit') ? $request->limit : 10;
        $page = $request->has('page') ? $request->page : 1;
        $search = $request->has('search') ? $request->search : '';
        $status = $request->has('status') ? $request->status : 1;

        DB::beginTransaction();
        try {
            // mengambil tipe_kontrak yang ada di tabel permohonan untuk di kondisikan
            // jika kontrak baru akan menggunakan permohonan_pengguna jika kontrak lama akan menggunakan kontrak_pengguna
            $permohonan = Permohonan::where('id_permohonan', $idPermohonan)->first();
            $query = Permohonan_detail::with([
                'entitas' => function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        Master_pengguna::class => ['media_ktp:id,file_hash,file_path', 'divisi']
                    ]);
                },
                'tld',
                'penggunaLama'
            ])
            ->where('id_permohonan', $idPermohonan)
            ->where('jenis', 'pengguna')
            ->get();

            $queryKontrak = false;
            if($permohonan->tipe_kontrak == 'kontrak lama'){
                // $queryKontrak = Kontrak_tld::with(
                //     'pengguna.media_ktp:id,file_hash,file_path',
                //     'pengguna.divisi',
                // )
                // ->where('id_kontrak', $permohonan->id_kontrak)
                // ->where('status', 3)
                // ->whereNotNull('id_pengguna')
                // ->whereNotNull('id_tld')
                // ->get();

                // $query = $query->merge($queryKontrak);
            }

            // $this->pagination = Arr::except($arr, 'data');

            $tld = $this->tld->searchTldNotUsed(new Request(['jenis' => 'pengguna']));
            $resTld = json_decode($tld->getContent(), true);
            $noTld = 0;

            foreach ($query as $item) {
                // mengecek informasi tld
                if($item->tld) {
                    $item->tld_pengguna = $item->tld;
                }else{
                    if($item->type != 'ganti') {
                        $item->tld_pengguna = $resTld['data'][$noTld] ?? null;
                        $noTld++;
                    }
                }
            }
            DB::commit();
            return $this->output($query);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function listKontrol(Request $request)
    {
        $request->validate([
            'idPermohonan' => 'required'
        ]);

        $idPermohonan = decryptor($request->idPermohonan);

        DB::beginTransaction();
        try {
            $permohonan = Permohonan::where('id_permohonan', $idPermohonan)->first();

            // $query = Permohonan_tld::with(
            //     'pengguna',
            //     'divisi'
            // )->where('id_permohonan', $idPermohonan)->get();
            $query = Permohonan_detail::with([
                'entitas',
                'tld'
            ])->where('id_permohonan', $idPermohonan)
            ->where('jenis', 'kontrol')
            ->get()
            ->groupBy(function ($item) {
                return optional($item->entitas)->name ?? 'default';
            });

            if($query){
                $tld = $this->tld->searchTldNotUsed(new Request(['jenis' => 'kontrol']));
                $resTld = json_decode($tld->getContent(), true);
                $noTld = 0;

                foreach ($query as $item) {
                    // mengecek informasi tld
                    foreach ($item as $key => $value) {
                        if($value->tld) {
                            $value->tld_pengguna = $value->tld;
                        }else{
                            $value->tld_pengguna = $resTld['data'][$noTld] ?? null;
                            $noTld++;
                        }
                    }
                }
            }

            $queryKontrak = false;
            if($permohonan->periode){
                // $queryKontrak = Kontrak_tld::with(
                //     'pengguna',
                //     'divisi'
                // )->where('id_kontrak', $permohonan->id_kontrak)
                // ->where('status', 3)
                // ->whereNotNull('id_tld')
                // ->get();
            }

            DB::commit();

            return $this->output(array(
                'tldPermohonan' => $query,
                'tldKontrak' => $queryKontrak
            ));
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function listPengajuan(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 10;
        $page = $request->has('page') ? $request->page : 1;
        $status = $request->has('status') ? $request->status : 1;

        $filter = $request->has('filter') ? $request->filter : [];

        DB::beginTransaction();
        try {
            $user = Auth::user();

            $query = Permohonan::with(
                        'layanan_jasa:id_layanan,nama_layanan',
                        'jenisTld:id_jenisTld,name',
                        'jenis_layanan:id_jenisLayanan,name,parent',
                        'jenis_layanan_parent',
                        'pelanggan:id,name,id_perusahaan',
                        'pelanggan.perusahaan:id_perusahaan,nama_perusahaan',
                        'kontrak',
                        'kontrak.layanan_jasa:id_layanan,nama_layanan',
                        'kontrak.jenisTld:id_jenisTld,name',
                        'kontrak.jenis_layanan:id_jenisLayanan,name,parent',
                        'kontrak.jenis_layanan_parent',
                        'lhu',
                        'lhu.penyelia_map',
                        'lhu.penyelia_map.jobs',
                    )
                    ->when($user, function($q, $user) use ($status) {
                        // Pengecekan role
                        if($user->hasRole('Pelanggan')){
                            // mengambil id dari history_pic
                            $id_pic = array();
                            foreach (Auth::user()->perusahaan->history_pic as $key => $pic) {
                                array_push($id_pic, $pic->id);
                            }
                            $q->whereIn('created_by', $id_pic);
                            $q->whereIn('status', $status);
                        }else{
                            $q->whereNotIn('status', [80]);
                        }

                        return $q;
                    })
                    ->when($filter, function($q, $filter) {
                        foreach ($filter as $key => $value) {
                            if($key == 'id_perusahaan'){
                                $q->whereHas('pelanggan.perusahaan', function ($v) use ($value) {
                                    $v->where('id_perusahaan', decryptor($value));
                                });
                            } else if ($key == 'periode') {
                                $q->where($key, $value);
                            } else if ($key == 'date_range') {
                                $q->whereBetween('created_at', [$value[0], $value[1]]);
                            } else {
                                $q->where($key, decryptor($value));
                            }
                        }
                    })
                    ->where('status', '!=', 11)
                    ->orderBy('created_at','DESC')
                    ->offset(($page - 1) * $limit)
                    ->limit($limit)
                    ->paginate($limit);

            $arr = $query->toArray();
            $this->pagination = Arr::except($arr, 'data');
            DB::commit();

            return $this->output($query);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function countList(Request $request){
        DB::beginTransaction();
        try {
            $arrStatus = [1,2,3,4,5,11,80];
            // jika role nya pelanggan, wherenya sesuai dengan id user
            $isPelanggan = Auth::user()->hasRole('Pelanggan');
            $_status = Permohonan::selectRaw('count(*) as total, status')
                ->when($isPelanggan, function($q) {
                    // mengambil id dari history_pic
                    $id_pic = array();
                    foreach (Auth::user()->perusahaan->history_pic as $key => $pic) {
                        array_push($id_pic, $pic->id);
                    }
                    return $q->whereIn('created_by', $id_pic);
                })
                ->groupBy('status')
                ->get()
                ->map(function ($item) {
                    return [
                        'total' => (int) $item->total,
                        'status' => (int) $item->status
                    ];
                })
                ->toArray();

            $total = 0;

            foreach ($arrStatus as $value) {
                $exist = array_filter($_status, function($item) use ($value) {
                    return $item['status'] == $value;
                });
                if (count($exist) == 0) {
                    $_status[] = [
                        'status' => $value,
                        'total' => 0
                    ];
                }
                if ($value !== 80) {
                    $existStatus = array_filter($_status, function($item) use ($value) {
                        return $item['status'] == $value;
                    });
                    $total += $existStatus ? reset($existStatus)['total'] : 0;
                }
            }

            $_status[] = [
                'status' => 'Semua',
                'name' => 'Semua',
                'total' => $total
            ];

            $query = array_map(function($item) {
                switch($item['status']) {
                    case 1:
                        $item['name'] = 'Pengajuan';
                        break;
                    case 2:
                        $item['name'] = 'Verifikasi';
                        break;
                    case 3:
                        $item['name'] = 'Lab';
                        break;
                    case 4:
                        $item['name'] = 'Pengiriman';
                        break;
                    case 5:
                        $item['name'] = 'Selesai';
                        break;
                    case 11:
                        $item['name'] = 'Sewa';
                        break;
                    case 80:
                        $item['name'] = 'Draft';
                        break;
                }
                return $item;
            }, $_status);

            DB::commit();

            return $this->output($query);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function getPengajuanById($id)
    {
        DB::beginTransaction();
        try {
            $id = decryptor($id);
            $query = Permohonan::with([
                'layanan_jasa:id_layanan,nama_layanan',
                'jenisTld:id_jenisTld,name',
                'jenis_layanan:id_jenisLayanan,name,parent',
                'jenis_layanan_parent',
                'pelanggan:id,name,id_perusahaan',
                'pelanggan.perusahaan',
                'kontrak',
                'kontrak.periode',
                'kontrak.jenis_layanan',
                'kontrak.layanan_jasa:id_layanan,nama_layanan',
                'kontrak.jenisTld:id_jenisTld,name',
                'permohonan_detail',
                'permohonan_detail.tld',
                'permohonan_detail.penggunaLama',
                'permohonan_detail.entitas' => function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        Master_pengguna::class => ['media_ktp:id,file_hash,file_path', 'divisi']
                    ]);
                },
                'tandaterima',
                'dokumen',
                'invoice',
                'logs.causer',
            ])->where('id_permohonan', $id)->first();
            DB::commit();

            if(isset($query->list_tld) && count($query->list_tld) > 0){
                $tldKontrol = Master_tld::whereIn('id_tld', $query->list_tld)->get();
                $query->tld_kontrol = $tldKontrol;
            }

            return $this->output($query);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function getChildJenisLayanan($idParent, Request $request)
    {
        DB::beginTransaction();
        $parent = decryptor($idParent);
        $isFilter = $request->has('isFilter') ? $request->isFilter : false;

        try {
            $data = Master_jenisLayanan::with(['child' => function ($query) use ($isFilter) {
                if(!$isFilter){
                    $query->where('status', 1);
                }
            }])
            ->select('id_jenisLayanan','name')
            ->where('id_jenisLayanan', $parent)
            ->first();

            DB::commit();

            if($data){
                $payload = array(
                    'data' => $data,
                    'msg' => 'Data found'
                );
                return $this->output($payload);
            }else{
                return $this->output(array('msg' => 'Data not found'), 'Fail', 404);
            }

        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function getJenisTld($idJenisLayanan)
    {
        DB::beginTransaction();

        $idJenisLayanan = decryptor($idJenisLayanan);
        try {
            $jenisTld = Master_price::with('jenisTld')
                ->select('id_price', 'id_jenisTld', 'keterangan', 'price', 'qty')
                ->whereHas('jenisTld', function ($query) {
                    $query->where('status', 1);
                })
                ->whereJsonContains('id_jenisLayanan', (int)$idJenisLayanan)
                ->get();
            DB::commit();

            return $this->output($jenisTld);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function getPrice(Request $request)
    {
        DB::beginTransaction();

        $validator = $request->validate([
            'idJenisLayanan' => 'required',
            'idJenisTld' => 'required'
        ]);

        $idJenisLayanan = decryptor($request->idJenisLayanan);
        $idJenisTld = decryptor($request->idJenisTld);
        $qty = $request->qty ? $request->qty : 1;

        try {
            $price = Master_price::select('price')
                ->where('id_jenisTld', $idJenisTld)
                ->whereJsonContains('id_jenisLayanan', (int)$idJenisLayanan)
                ->first();
                DB::commit();

            return $this->output($price);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function verifAdendum(Request $request){
        DB::beginTransaction();
        try {
            $ttd = $request->ttd ? decryptor($request->ttd) : null;
            $ttdBy = $request->ttd_by ? decryptor($request->ttd_by) : null;
            $idPermohonan = $request->idPermohonan ? decryptor($request->idPermohonan) : false;
            $listTld = $request->listTld ? json_decode($request->listTld) : [];
            $tglSelesai = $request->tanggal_selesai ? $request->tanggal_selesai : null;

            $dataPermohonan = Permohonan::with([
                'kontrak:id_kontrak,no_kontrak'
            ])->where('id_permohonan', $idPermohonan)->first();

            if($dataPermohonan){
                foreach($listTld as $item){
                    $idTld = (int) decryptor($item->tld);
                    $id = decryptor($item->id);

                    // update master tld
                    if($idTld){
                        Master_tld::find($idTld)->update([
                            'status' => 1,
                            'digunakan' => $dataPermohonan->kontrak->no_kontrak
                        ]);
                    }

                    Permohonan_detail::find($id)?->update([
                        'id_tld' => $idTld ? $idTld : null,
                        'status' => $dataPermohonan->is_have_tld ? 1 : 5
                    ]);
                }

                $dataPermohonan = null;
            }

            $dataPermohonan = Permohonan::with([
                'permohonan_detail',
                'permohonan_detail.entitas' =>function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        Master_pengguna::class => ['media_ktp:id,file_hash,file_path', 'divisi']
                    ]);
                },
                'permohonan_detail.tld',
                'kontrak:id_kontrak,no_kontrak'
            ])->where('id_permohonan', $idPermohonan)->first();

            if($dataPermohonan){
                // mengambil periode kontrak
                $kontrakperiode = Kontrak_periode::where('id_kontrak', $dataPermohonan->id_kontrak)->where('periode', $dataPermohonan->periode)->first();
                $isPeriodOne = $kontrakperiode->count_tld == 1;

                // menonaktifkan pengguna yang sudah diganti di kontrak
                foreach($dataPermohonan->permohonan_detail as $detail){
                    $dataDetail = array(
                        'status' => 2,
                        'id_pengguna_divisi' => $detail->id_pengguna_divisi,
                        'jenis' => $detail->jenis,
                        'type' => $detail->type,
                        'id_kontrak' => $dataPermohonan->id_kontrak,
                        'created_by' => Auth::user()->id,
                        'periode' => $dataPermohonan->periode
                    );

                    if($detail->type == 'ganti'){
                        $kontrakDetail = kontrak_detail::where('id_kontrak', $dataPermohonan->id_kontrak)
                            ->where('id_pengguna_divisi', $detail->pengguna_lama)
                            ->where('status', 1)
                            ->first();
                        $tld_1 = null;
                        $tld_2 = null;
                        $status_tld_1 = null;
                        $status_tld_2 = null;
                        $periode_tld_1 = null;
                        $periode_tld_2 = null;

                        if($isPeriodOne){
                            $tld_1 = $detail->id_tld;
                            $status_tld_1 = $detail->status;
                            $periode_tld_1 = $dataPermohonan->periode;
                        } else{
                            $tld_2 = $detail->id_tld;
                            $status_tld_2 = $detail->status;
                            $periode_tld_2 = $dataPermohonan->periode;
                        }

                        $dataDetail['tld_1'] = $kontrakDetail->tld_1 ? $kontrakDetail->tld_1 : $tld_1;
                        $dataDetail['status_tld_1'] = $kontrakDetail->status_tld_1 ? $kontrakDetail->status_tld_1 : $status_tld_1;
                        $dataDetail['periode_tld_1'] = $kontrakDetail->periode_tld_1 ? $kontrakDetail->periode_tld_1 : $periode_tld_1;

                        $dataDetail['tld_2'] = $kontrakDetail->tld_2 ? $kontrakDetail->tld_2 : $tld_2;
                        $dataDetail['status_tld_2'] = $kontrakDetail->status_tld_2 ? $kontrakDetail->status_tld_2 : $status_tld_2;
                        $dataDetail['periode_tld_2'] = $kontrakDetail->periode_tld_2 ? $kontrakDetail->periode_tld_2 : $periode_tld_2;

                        $dataDetail['pengguna_lama'] = $detail->pengguna_lama;

                        // Master_pengguna::where('id_pengguna', $detail->pengguna_lama)->update(['status' => 1]);
                    } else if($detail->type == 'baru'){

                        if($isPeriodOne){
                            $dataDetail['tld_1'] = $detail->id_tld;
                            $dataDetail['status_tld_1'] = $dataPermohonan->is_have_tld ? 1 : 5;
                            $dataDetail['periode_tld_1'] = $dataPermohonan->periode;
                        } else {
                            $dataDetail['tld_2'] = $detail->id_tld;
                            $dataDetail['status_tld_2'] = $dataPermohonan->is_have_tld ? 1 : 5;
                            $dataDetail['periode_tld_2'] = $dataPermohonan->periode;
                        }
                    }

                    Kontrak_detail::create($dataDetail);
                    Master_pengguna::find($detail->id_pengguna_divisi)?->update(['status' => 3]);
                }

                $dataPermohonan->update(array(
                    'ttd' => $ttd,
                    'ttd_by' => $ttdBy,
                    'verify_at' => date('Y-m-d H:i:s'),
                    'status' => 2
                ));

                // buatkan invoice jika total harga lebih besar dari 0
                if($dataPermohonan->total_harga > 0){
                    // untuk menghitung total harga kontrak,
                    // di komen karena sementara tidak perlu

                    // $kontrak = Kontrak::where('id_kontrak', $dataPermohonan->id_kontrak)->first();
                    // $totalharga = $kontrak->total_harga + $dataPermohonan->total_harga;

                    // $kontrak->update(array(
                    //     'total_harga' => $totalharga
                    // ));

                    $invoiceData = $this->keuangan->keuanganAction(new Request([
                        'idPermohonan' => $dataPermohonan->permohonan_hash,
                        'status' => 1
                    ]));

                    if($invoiceData->getStatusCode() != 200){
                        $content = json_decode($invoiceData->getContent());
                        Log::error("Invoice creation failed: ".$content->msg);
                        throw new \Exception($content->msg ?? 'Gagal membuat invoice');
                    }
                }

                $penyeliaData = $this->penyelia->actionPenyelia(new Request([
                    'idPermohonan' => $dataPermohonan->permohonan_hash,
                    'status' => 1,
                    'endDate' => $tglSelesai,
                    'startDate' => date('Y-m-d H:i:s')
                ]));

                if($penyeliaData->getStatusCode() != 200){
                    $content = json_decode($penyeliaData->getContent());
                    Log::error("Penyelia creation failed: ".$content->msg);
                    throw new \Exception($content->msg ?? 'Gagal membuat penyelia');
                }

                DB::commit();
                return $this->output(array('msg' => 'Permohonan berhasil diverifikasi'), 'Success', 200);
            }else{
                return $this->output(array('msg' => 'Permohonan tidak ditemukan'), 'Fail', 404);
            }
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }


/**
 * Verifikasi permohonan yang sebelumnya di ajukan oleh front desk
 *
 * @param Request $request
 * @return Response
 * @throws Exception
 */
    public function verifPermohonan(Request $request)
    {
        $request->validate([
            'status' => 'required',
        ]);
        $status = $request->status ? $request->status : 'tidak_lengkap';
        $tglSelesai = $request->tanggal_selesai ? $request->tanggal_selesai : null;

        DB::beginTransaction();
        try {
            $arrayUpdate = array();
            $idPermohonan = $request->idPermohonan ? decryptor($request->idPermohonan) : false;
            $dataPermohonan = Permohonan::with('jenis_layanan', 'jenis_layanan_parent', 'kontrak')->where('id_permohonan', $idPermohonan)->first();
            if($dataPermohonan){
                $JL = jenislayanan($dataPermohonan->jenis_layanan_parent, $dataPermohonan->jenis_layanan);

                if($status == 'lengkap'){
                    $ttd = $request->ttd ? decryptor($request->ttd) : null;
                    $ttdBy = $request->ttd_by ? decryptor($request->ttd_by) : null;
                    $no_kontrak = null;
                    $arrayUpdate['verify_at'] = date('Y-m-d H:i:s');
                    $arrayUpdate['status'] = 2; // pengajuan di setujui oleh front desk
                    $arrayUpdate['ttd'] = $ttd;
                    $arrayUpdate['ttd_by'] = $ttdBy;

                    // simpan ttd di dokumen
                    Permohonan_dokumen::where('id_permohonan', $idPermohonan)
                    ->where('jenis', 'tandaterima')->where('status', 1)
                    ->get()->each(function($doc) use ($ttd, $ttdBy) {
                        $doc->update([
                            'ttd' => $ttd,
                            'ttd_by' => $ttdBy
                        ]);
                    });

                    // menambahkan tld
                    if($dataPermohonan->tipe_kontrak == 'kontrak baru'){
                        $jenis = 'kontrak';
                        if(in_array($JL, $this->global['arr_putus'])){
                            $jenis = 'KontrakPengujian';
                        }
                        $no_kontrak = generateNoDokumen($jenis, $idPermohonan);
                        $listTld = $request->listTld ? json_decode($request->listTld) : [];

                        foreach ($listTld as $item) {
                            $idTld = (int) decryptor($item->tld);
                            $id = decryptor($item->id);

                            // update master tld
                            Master_tld::find($idTld)->update([
                                'status' => 1,
                                'digunakan' => $no_kontrak
                            ]);

                            Permohonan_detail::find($id)?->update([
                                'id_tld' => $idTld,
                                'status' => $dataPermohonan->is_have_tld ? 1 : 5
                            ]);
                        }

                        $this->createdKontrak($request->idPermohonan, $no_kontrak);
                    } else {
                        $no_kontrak = $dataPermohonan->kontrak->no_kontrak;
                    }

                    $dataPermohonan->update($arrayUpdate);

                    $dataPermohonan = Permohonan::with([
                        'kontrak',
                        'jenis_layanan_parent',
                        'jenisTld',
                        'layanan_jasa',
                        'permohonan_detail'
                    ])->find($idPermohonan);

                    // cek periode pengembalian
                    if($no_kontrak){
                        $kontrak =Kontrak::where('no_kontrak', $no_kontrak)->first();
                        $kontrakPeriode = Kontrak_periode::where('id_kontrak', $kontrak->id_kontrak)->where('status', 3)->first();
                        if($kontrakPeriode){
                            $kontrakPeriode->update([
                                'status' => 2
                            ]);
                        }
                    }
                    /*
                        JENIS LAYANAN

                        2 Kontrak - Sewa
                        3 Kontrak - Evaluasi
                        5 Evaluasi - Dengan kontrak
                        6 Evaluasi - Tanpa kontrak
                        8 Zero Check - Dengan kontrak
                        9 Zero Check - Tanpa kontrak
                    */

                    // proses ke invoice
                    $arrValidInvoice = [2, 3, 6, 9];
                    if(in_array($dataPermohonan->jenis_layanan_2, $arrValidInvoice)){
                        if($dataPermohonan->tipe_kontrak == 'kontrak baru'){
                            $invoiceData = $this->keuangan->keuanganAction(new Request([
                                'idPermohonan' => $dataPermohonan->permohonan_hash,
                                'status'    => 1
                            ]));

                            if($invoiceData->getStatusCode() != 200){
                                $content = json_decode($invoiceData->getContent());
                                Log::error("Invoice creation failed: ".$content->msg);
                                throw new \Exception($content->msg ?? 'Gagal membuat invoice');
                            }
                        }
                    }

                    // Proses ke penyelia
                    $arrValidPenyelia = [2, 3, 5, 6, 9];
                    if(in_array($dataPermohonan->jenis_layanan_2, $arrValidPenyelia)){
                        // $JL = jenislayanan($dataPermohonan->jenis_layanan_parent, $dataPermohonan->jenis_layanan);
                        // if(in_array($JL, $this->global['arr_putus'])) {
                        //     if($dataPermohonan->tipe_kontrak == 'kontrak lama'){
                        //         $status = 1;
                        //     } else {
                        //         $status = 1;
                        //     }
                        // } else {
                        //     $status = 1;
                        // }
                        $status = 1;

                        $penyeliaData = $this->penyelia->actionPenyelia(new Request([
                            'idPermohonan' => $dataPermohonan->permohonan_hash,
                            'status'    => $status,
                            'endDate'  => $tglSelesai,
                            'startDate' => date('Y-m-d H:i:s')
                        ]));

                        if($penyeliaData->getStatusCode() != 200){
                            $content = json_decode($penyeliaData->getContent());
                            Log::error("Penyelia creation failed: ".$content->msg);
                            throw new \Exception($content->msg ?? 'Gagal membuat penyelia');
                        }
                    }

                } else {
                    $note = $request->note ? $request->note : '';
                    $arrayUpdate['note'] = $note;
                    $arrayUpdate['status'] = 90; // Pengajuan di tolak oleh front desk
                    $dataPermohonan->update($arrayUpdate);

                    // send notif to pelanggan
                    $dataNotif = [
                        'pesan' => 'Permohonan anda pada tanggal ' . convert_date($dataPermohonan->created_at, 1) . ' telah ditolak. ' . $note,
                        'event' => 'Dikembalikan',
                        'event_id' => $dataPermohonan->permohonan_hash,
                        'url' => 'permohonan/dikembalikan'
                    ];
                    Notifier::send([$dataPermohonan->created_by], $dataNotif);
                }

                DB::commit();

                $this->notif->read(new Request([
                    'event' => 'Permohonan',
                    'event_id' => $dataPermohonan->permohonan_hash
                ]));
            }

            // $fileLhu && $fileLhu->store();
            return $this->output(array('msg' => 'Success'));
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function uploadLhuZeroCek(Request $request)
    {
        $validator = $request->validate([
            'idHash' => 'required',
            'file' => 'required|file'
        ]);

        DB::beginTransaction();
        try {
            $idPermohonan = decryptor($request->idHash);
            $file = $request->file('file');

            $fileUpload = $this->media->upload($file, 'permohonan');
            $dataPermohonan = Permohonan::find($idPermohonan);

            if(isset($dataPermohonan)){
                $dataPermohonan->update(array('file_lhu' => $fileUpload->getIdMedia()));
                DB::commit();

                if($dataPermohonan){
                    $fileUpload->store();
                    // ambil media Document Lhu
                    $mediaDocLhu = $this->media->get($fileUpload->getIdMedia());
                    return $this->output(array('msg' => 'LHU berhasil diupload', 'data' => $mediaDocLhu));
                }

                return $this->output(array('msg' => 'LHU gagal diupload'), 'Fail', 400);
            }

            return $this->output(array('msg' => 'data not found'), 'Fail', 400);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function destroyLhuZero($idPermohonan, $idMedia){
        $idMedia = decryptor($idMedia);
        $idPermohonan = decryptor($idPermohonan);

        DB::beginTransaction();
        try {
            $dataPermohonan = Permohonan::find($idPermohonan);
            if(isset($dataPermohonan)){
                $update = $dataPermohonan->update(array('file_lhu' => null));
                $this->media->destroy($idMedia);

                DB::commit();

                if($update){
                    return $this->output(array('msg' => 'LHU berhasil dihapus'));
                }

                return $this->output(array('msg' => 'LHU gagal dihapus'), 'Fail', 400);
            }

            return $this->output(array('msg' => 'data not found'), 'Fail', 400);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }

    }

    public function createdKontrak($idPermohonan, $no_kontrak)
    {
        $idPermohonan = decryptor($idPermohonan);
        $dataPermohonan = Permohonan::with([
            'jenis_layanan_parent',
            'jenis_layanan',
            'permohonan_detail'
        ])->find($idPermohonan);

        $params = array(
            'id_layanan' => $dataPermohonan->id_layanan,
            'jenis_layanan_1' => $dataPermohonan->jenis_layanan_1,
            'jenis_layanan_2' => $dataPermohonan->jenis_layanan_2,
            'tipe_kontrak' => $dataPermohonan->tipe_kontrak,
            'no_kontrak' => $no_kontrak,
            'jenis_tld' => $dataPermohonan->jenis_tld,
            'periode_next' => $dataPermohonan->periode_next,
            'jumlah_pengguna' => $dataPermohonan->jumlah_pengguna,
            'jumlah_kontrol' => $dataPermohonan->jumlah_kontrol,
            'total_harga' => $dataPermohonan->total_harga,
            'harga_layanan' => $dataPermohonan->harga_layanan,
            'ttd' => $dataPermohonan->ttd,
            'ttd_by' => $dataPermohonan->ttd_by,
            'status' => 1,
            'note' => $dataPermohonan->note,
            'file_lhu' => $dataPermohonan->file_lhu,
            'id_pelanggan' => $dataPermohonan->created_by,
            'is_have_tld' => $dataPermohonan->is_have_tld,
            'is_zerocek' => $dataPermohonan->is_zerocek,
            'created_by' => Auth::user()->id
        );
        $dataKontrak = Kontrak::create($params);

        // Tambah periode
        if($dataPermohonan->periode_pemakaian){
            if($dataPermohonan->is_zerocek == 1 && $dataPermohonan->is_have_tld == 0){
                // zero cek
                Kontrak_periode::create(array(
                    'id_kontrak' => $dataKontrak->id_kontrak,
                    'periode' => 0,
                    'start_date' => null,
                    'end_date' => null,
                    'status' => 1,
                    'id_permohonan' => $dataPermohonan->id_permohonan,
                    'created_by' => Auth::user()->id,
                    'created_at' => date('Y-m-d H:i:s')
                ));
            }

            foreach ($dataPermohonan->periode_pemakaian as $key => $value) {
                $periode = $key + 1;
                $status = 1;

                // cari ganjil genap
                $countTld = null;
                if($periode % 2 == 0){ // genap
                    $countTld = 2;
                } else { // ganjil
                    $countTld = 1;
                }

                $paramsPeriode = array(
                    'id_kontrak' => $dataKontrak->id_kontrak,
                    'periode' => $periode,
                    'start_date' => $value['start_date'],
                    'end_date' => $value['end_date'],
                    'status' => $status,
                    'count_tld' => $countTld,
                    'id_permohonan' => $dataPermohonan->periode == $periode ? $dataPermohonan->id_permohonan : null,
                    'created_by' => Auth::user()->id,
                    'created_at' => date('Y-m-d H:i:s')
                );
                Kontrak_periode::create($paramsPeriode);
            }
        }

        // menambahkan permohonan Detail
        foreach ($dataPermohonan->permohonan_detail as $key => $value) {
            if($value->jenis == 'pengguna') {
                Master_pengguna::find($value->id_pengguna_divisi)?->update(array('status' => 3));
            }

            $dataPermohonanDetail = array(
                'id_kontrak' => $dataKontrak->id_kontrak,
                'id_pengguna_divisi' => $value->id_pengguna_divisi,
                'jenis' => $value->jenis,
                'status' => 1,
                'type' => $value->type,
                'created_by' => Auth::user()->id,
                'tld_1' => $value->id_tld,
                'status_tld_1' => $dataPermohonan->is_have_tld == 1 ? 3 : 5,
                'periode_tld_1' => 1,
            );

            Kontrak_detail::create($dataPermohonanDetail);
        }

        // Menambahkan id_kontrak ke table permohonan
        $dataPermohonan->update(array('id_kontrak' => $dataKontrak->id_kontrak));

        $JL = jenislayanan($dataPermohonan->jenis_layanan_parent, $dataPermohonan->jenis_layanan);
        if(!in_array($JL, $this->global['arr_putus'])){ // jika bukan Evaluasi putus
            // menambahkan dokumen perjanjian kontrak
            $template = Documents::with(['footer', 'header'])
                        ->where('jenis', 'body')
                        ->where('name', 'Kontrak')
                        ->where('status', 1)
                        ->first();

            $data = array(
                'id_kontrak' => $dataKontrak->id_kontrak,
                'created_by' => Auth::user()->id,
                'nama' => 'Surat kontrak ('.convert_date($dataPermohonan->verify_at, 6).')',
                'jenis' => 'kontrak',
                'id_doc_template' => $template->id_doc,
                'status' => 1,
                'nomer' => $no_kontrak
            );
            Permohonan_dokumen::create($data);
        }
    }

    private function generateNoKontrak($idPermohonan)
    {
        $permohonan = Permohonan::with('jenis_layanan')->where('id_permohonan', $idPermohonan)->first();
        // Menentukan tipe kontrak
        if($permohonan) {
            $jenisLayanan = substr($permohonan->jenis_layanan->name, 0, 1);
            $type = strtoupper($jenisLayanan);

            // Nama aplikasi
            $appName = 'JKRL';

            // Mengambil bulan sekarang dan mengubah ke dalam format Romawi
            $bulanSekarang = date('n'); // n = format angka bulan tanpa nol
            $romawiBulan = getRomawiBulan($bulanSekarang);

            // Tahun saat ini
            $tahunSekarang = date('Y');

            // Incremental number
            $lastContractNumber = Kontrak::whereNotNull('no_kontrak')
                                    ->whereMonth('created_at', $bulanSekarang)
                                    ->whereYear('created_at', $tahunSekarang)
                                    ->count(); // Ubah dengan pengambilan nomor terakhir dari database
            $increment = str_pad($lastContractNumber + 1, 4, '0', STR_PAD_LEFT);

            // Format nomor kontrak
            $noKontrak = "{$type}-{$increment}/{$appName}/{$romawiBulan}/{$tahunSekarang}";

            return $noKontrak;
        }
    }
}
