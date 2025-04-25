<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use App\Traits\RestApi;

use App\Models\Master_tld;
use App\Models\Master_media;
use App\Models\Pengiriman;
use App\Models\Pengiriman_detail;
use App\Models\Permohonan;
use App\Models\Permohonan_pengguna;
use App\Models\User;
use App\Models\Keuangan;
use App\Models\Penyelia;
use App\Models\Kontrak;
use App\Models\Kontrak_pengguna;
use App\Models\Kontrak_tld;
use App\Models\Kontrak_periode;

use App\Http\Controllers\MediaController;
use App\Http\Controllers\LogController;

use Auth;
use DB;

class PengirimanAPI extends Controller
{
    use RestApi;

    public function __construct(){
        $this->media = resolve(MediaController::class);
        $this->log = resolve(LogController::class);
        date_default_timezone_set('Asia/Jakarta');
    }

    public function listPermohonan(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 10;
        $page = $request->has('page') ? $request->page : 1;
        $search = $request->has('search') ? $request->search : '';

        DB::beginTransaction();
        try {
            $query = Permohonan::with([
                        'layanan_jasa:id_layanan,nama_layanan',
                        'jenisTld:id_jenisTld,name', 
                        'jenis_layanan:id_jenisLayanan,name,parent',
                        'jenis_layanan_parent',
                        'pelanggan:id,id_perusahaan,name',
                        'pelanggan.perusahaan',
                        'kontrak',
                        'kontrak.periode',
                        'kontrak.pengiriman',
                        'kontrak.pengiriman.detail',
                        'pengiriman',
                        'invoice',
                        'invoice.pengiriman',
                        'lhu',
                        'lhu.media',
                        'lhu.pengiriman',
                        'lhu.penyelia_map',
                        'lhu.penyelia_map.jobs',
                        'lhu.petugas',
                        'file_lhu'
                    ])->when($search, function($q, $search){
                        return $q->where('no_kontrak', 'like', "%$search%");
                    })
                    ->whereIn('status', [2, 3, 4, 5])
                    ->orderBy('verify_at','DESC')
                    ->offset(($page - 1) * $limit)
                    ->limit($limit)
                    ->paginate($limit);
                    
            $arr = $query->toArray();
            DB::commit();
            $this->pagination = Arr::except($arr, 'data');

            return $this->output($query, 200);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return response()->json(array('msg' => $ex->getMessage()), 500);
        }

    }

    public function listPengiriman(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 10;
        $page = $request->has('page') ? $request->page : 1;
        $search = $request->has('search') ? $request->search : '';
        $idPelanggan = $request->has('idPelanggan') ? decryptor($request->idPelanggan) : '';

        DB::beginTransaction();
        try {
            $query = Pengiriman::with(
                        'kontrak',
                        'kontrak.pelanggan',
                        'kontrak.pelanggan.perusahaan',
                        'detail',
                        'alamat'
                    )->orderBy('created_at', 'DESC')
                    ->offset(($page - 1) * $limit)
                    // ->when($status, function($q, $status) {
                    //     return $q->whereIn('status', $status);
                    // })
                    ->when($idPelanggan, function($q, $idPelanggan) {
                        return $q->where('tujuan', $idPelanggan);
                    })
                    ->limit($limit)
                    ->paginate($limit);

            $arr = $query->toArray();
            $this->pagination = Arr::except($arr, 'data');

            DB::commit();

            return $this->output($query, 200);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return response()->json(array('msg' => $ex->getMessage()), 500);
        }
    }

    public function getPengirimanById(string $idPengiriman)
    {
        $id = $idPengiriman;

        DB::beginTransaction();
        try {
            $query = Pengiriman::with([
                'ekspedisi',
                'kontrak',
                'detail',
                'alamat',
                'tujuan:id,id_perusahaan,name',
                'tujuan.perusahaan:id_perusahaan,nama_perusahaan',
                'permohonan:id_permohonan,periode_pemakaian,jumlah_pengguna,jumlah_kontrol,created_by',
                'permohonan.pelanggan',
                'permohonan.pelanggan.perusahaan',
                'permohonan.invoice',
                'permohonan.lhu',
                'permohonan.lhu.media'
            ])->where('id_pengiriman', $id)->first();

            // mengambil media pengiriman
            if($query->bukti_pengiriman){
                $query->media_pengiriman = Master_media::whereIn('id', $query->bukti_pengiriman)->get();
            }

            // mengambil media penerima
            if($query->bukti_penerima){
                $query->media_penerima = Master_media::whereIn('id', $query->bukti_penerima)->get();
            }
            
            DB::commit();

            return $this->output($query);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function getPermohonan(Request $request)
    {
        $tipe = $request->has('tipe') ? $request->tipe : null;
        $search = $request->has('search') ? $request->search : '';
        $limit = $request->has('limit') ? $request->limit : 10;
        $page = $request->has('page') ? $request->page : 1;

        $idPermohonan = $request->has('idPermohonan') ? $request->idPermohonan : false;

        DB::beginTransaction();
        try {
            if($idPermohonan){
                $query = Permohonan::with(
                    'pelanggan',
                    'pelanggan.perusahaan',
                    'pelanggan.perusahaan.alamat',
                    'invoice',
                    'invoice.usersig',
                    'invoice.diskon',
                    'lhu',
                    'lhu.media',
                    'lhu.log',
                    'kontrak',
                    'jenis_layanan',
                    'jenisTld',
                    'layanan_jasa'
                )->whereHas('lhu.log', function ($q) {
                    $q->whereColumn('log_penyelia.status', 'penyelia.status');
                })
                ->where('id_permohonan', decryptor($idPermohonan))->first();
            }else{
                $query = Permohonan::with(
                    'layanan_jasa:id_layanan,nama_layanan',
                    'pelanggan',
                    'pelanggan.perusahaan',
                    'jenis_layanan_parent',
                    'kontrak'
                )->when($search, function($q, $search){
                    return $q->where('no_kontrak', 'like', "%$search%");
                })
                ->whereNotIn('status', ['80','99'])
                ->orderBy('created_at','DESC')
                ->offset(($page - 1) * $limit)
                ->limit($limit)
                ->paginate($limit);
                
                $arr = $query->toArray();
                $this->pagination = Arr::except($arr, 'data');
            }
            DB::commit();

            return $this->output($query);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function actionPengiriman(Request $request)
    {
        DB::beginTransaction();
        try {
            $idPengiriman = $request->idPengiriman ? $request->idPengiriman : false;
            $idPermohonan = $request->idPermohonan ? decryptor($request->idPermohonan) : false;
            $idEkspedisi = $request->has('idEkspedisi') ? decryptor($request->idEkspedisi) : false;
            $noResi = $request->has('noResi') ? $request->noResi : false;
            $jenisPengiriman = $request->jenisPengiriman ? $request->jenisPengiriman : false;
            $idKontrak = $request->idKontrak ? decryptor($request->idKontrak) : false;
            $alamat = $request->alamat ? decryptor($request->alamat) : false;
            $tujuan = $request->tujuan ? $request->tujuan : false;
            $periode = $request->periode ? $request->periode : false;
            $status = $request->status ? $request->status : false;
            $detail = $request->detail ? $request->detail : false;
            $sendAt = $request->sendAt ? $request->sendAt : false;
            $recivedAt = $request->dateRecived ? $request->dateRecived : false;
            $statusPermohonan = $request->statusPermohonan ? $request->statusPermohonan : false;
            $buktiPengiriman = $request->file('buktiPengiriman') ? $request->file('buktiPengiriman') : array();
            $buktiPenerima = $request->file('buktiPenerima') ? $request->file('buktiPenerima') : array();
            
            $params = array();
            $request->has('noResi') && $params['no_resi'] = $noResi;
            $request->has('idEkspedisi') && $params['id_ekspedisi'] = $idEkspedisi;
            $idPermohonan && $params['id_permohonan'] = $idPermohonan;
            $jenisPengiriman && $params['jenis_pengiriman'] = $jenisPengiriman;
            $idKontrak && $params['id_kontrak'] = $idKontrak;
            $alamat && $params['alamat'] = $alamat;
            $tujuan && $params['tujuan'] = $tujuan;
            $periode && $params['periode'] = $periode;
            $status && $params['status'] = $status;
            $recivedAt && $params['recived_at'] = $recivedAt;
            $sendAt && $params['send_at'] = Carbon::parse($sendAt)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s');

            // upload file
            $bukti = array();
            $tmpFileBukti = array();
            if(count($buktiPengiriman) != 0){
                foreach ($buktiPengiriman as $key => $file) {
                    $fileBukti = $this->media->upload($file, 'pengiriman');
                    array_push($bukti, $fileBukti->getIdMedia());
                    array_push($tmpFileBukti, $fileBukti);
                }

                $params['bukti_pengiriman'] = $bukti;
            }

            $pengiriman = Pengiriman::with('detail','kontrak', 'kontrak.pengguna')->where('id_pengiriman', $idPengiriman)->first();
            if(!$pengiriman){
                $params['created_by'] = Auth::user()->id;
            }
            $query = Pengiriman::updateOrCreate(
                ["id_pengiriman" => $idPengiriman],
                $params
            );

            // update status
            if($statusPermohonan){
                Permohonan::where('id_permohonan', $query->id_permohonan)
                            ->update(array('status' => $statusPermohonan));
            }

            // jika status 2 = pengiriman selesai maka mengganti list_tld di tabel kontrak
            if ($status == 2 && $pengiriman->kontrak) {
                $listTld = [];
                
                $listTld = array_map('intval', $pengiriman->detail->where('jenis', 'tld')->pluck('list_tld')->flatten()->toArray());
            
                if (!empty($listTld)) {
                    // mengganti status di kontrak_tld menjadi 2 artinya sudah diterima oleh pelanggan
                    Kontrak_tld::where('id_kontrak', $pengiriman->id_kontrak)
                        ->where('status', 1)
                        ->whereIn('id_tld', $listTld)
                        ->update(['status' => 2]);
                }

                // Mengecek semua proses dan pengiriman selesai semua di periode terakhir

                // Mengambil last periode
                $kontrakPeriode = Kontrak_periode::where('id_kontrak', $pengiriman->id_kontrak)->orderBy('periode', 'desc')->first();
                if ($kontrakPeriode) {
                    $isLast = $kontrakPeriode->periode == $pengiriman->periode ? true : false;
                    if($isLast){
                        Kontrak::where('id_kontrak', $pengiriman->id_kontrak)->update(['status' => 2]);
                    }
                }
            } else if ($status == 3 && isset($pengiriman->kontrak)) {
                // menghapus bukti pengiriman
                foreach ($pengiriman->bukti_pengiriman as $item) {
                    $this->media->destroy($item);
                }
                $pengiriman->update(['bukti_pengiriman' => null]);
            }

            $result['id_pengiriman'] = $query->pengiriman_hash;

            if ($query->wasRecentlyCreated) {
                $result['status'] = "created";
                $result['msg'] = "Pengiriman berhasil dibuat.";
            } elseif ($query->wasChanged()) {
                $result['status'] = "updated";
                $result['msg'] = "Pengiriman berhasil diedit.";
            } else {
                $result['status'] = "none";
                $result['msg'] = "Nothing has changed.";
            }

            if(count($tmpFileBukti) != 0){
                foreach ($tmpFileBukti as $key => $file) {
                    $file->store();
                }
            }

            DB::commit();
            
            return $this->output($result);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function diterima(Request $request)
    {
        DB::beginTransaction();
        try {
            $recivedAt = $request->dateRecived ? $request->dateRecived : false;
            $idPengiriman = $request->idPengiriman ? $request->idPengiriman : false;
            $status = $request->status;
            $buktiPenerima = $request->file('buktiPenerima') ? $request->file('buktiPenerima') : array();
            $statusPermohonan = $request->statusPermohonan ? $request->statusPermohonan : false;

            $params = array();
            $params['recived_at'] = $recivedAt;
            $params['status'] = $status;

            $tmpBuktiPenerima = array();
            $tmpFilePenerima = array();
            if(count($buktiPenerima) != 0){
                foreach ($buktiPenerima as $key => $file) {
                    $fileBukti = $this->media->upload($file, 'pengiriman');
                    array_push($tmpBuktiPenerima, $fileBukti->getIdMedia());
                    array_push($tmpFilePenerima, $fileBukti);
                }

                $params['bukti_penerima'] = $tmpBuktiPenerima;
            }

            $query = Pengiriman::with('detail', 'kontrak')->where('id_pengiriman', $idPengiriman)->first();
            $query->update($params);

            // jika LHU sudah dikirim
            if($statusPermohonan){
                Permohonan::where('id_permohonan', $query->id_permohonan)
                            ->update(array('status' => $statusPermohonan));
            }

            $listTld = array_map('intval', $query->detail->where('jenis', 'tld')->pluck('list_tld')->flatten()->toArray());
            if (!empty($listTld)) {
                // mengganti status di kontrak_tld menjadi 2 artinya sudah diterima oleh pelanggan
                Kontrak_tld::where('id_kontrak', $query->id_kontrak)
                    ->where('status', 1)
                    ->whereIn('id_tld', $listTld)
                    ->update(['status' => 2]);
                    
                // Mengganti status di master_tld menjadi 1 artinya tld sedang digunakan
                // Master_tld::whereIn('id_tld', $listTld)->update(['status' => 1]);
            }

            // Mengecek semua proses dan pengiriman selesai semua di periode terakhir
            // Mengambil last periode
            $kontrakPeriode = Kontrak_periode::where('id_kontrak', $query->id_kontrak)->orderBy('periode', 'desc')->first();
            if ($kontrakPeriode) {
                $isLast = $kontrakPeriode->periode == $query->periode ? true : false;
                if($isLast){
                    Kontrak::where('id_kontrak', $query->id_kontrak)->update(['status' => 2]);
                }
            }

            if(count($tmpFilePenerima) != 0){
                foreach ($tmpFilePenerima as $key => $file) {
                    $file->store();
                }
            }

            DB::commit();

            $result = array(
                'id_pengiriman' => $query->pengiriman_hash,
                'status' => 'Success',
                'msg' => 'Pengiriman berhasil diterima'
            );

            return $this->output($result);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function buatPengiriman(Request $request){
        DB::beginTransaction();
        try {
            $idPengiriman = $request->idPengiriman ? $request->idPengiriman : false;
            $idPermohonan = $request->idPermohonan ? decryptor($request->idPermohonan) : false;
            $alamat = $request->alamat ? decryptor($request->alamat) : false;
            $tujuan = $request->tujuan ? $request->tujuan : false;
            $status = $request->status ? $request->status : false;
            $detail = $request->detail ? $request->detail : false;
            $periode = $request->periode ? $request->periode : false;
            $idKontrak = $request->idKontrak ? decryptor($request->idKontrak) : false;

            $params = array();
            $idPermohonan && $params['id_permohonan'] = $idPermohonan;
            $alamat && $params['alamat'] = $alamat;
            $tujuan && $params['tujuan'] = $tujuan;
            $status && $params['status'] = $status;
            $periode && $params['periode'] = $periode;
            $idKontrak && $params['id_kontrak'] = $idKontrak;
            $params['created_by'] = Auth::user()->id;
            $params['id_pengiriman'] = $idPengiriman;

            $query = Pengiriman::create($params);

            // Add to detail
            if($detail){
                // Remove all detail
                Pengiriman_detail::where('id_pengiriman', $idPengiriman)->delete();

                foreach (json_decode($detail) as $key => $value) {
                    $params = array(
                        'id_pengiriman' => $idPengiriman,
                        'jenis' => $value->jenis,
                        'periode' => $value->periode ?? null,
                    );

                    if($value->listTld){
                        $params['list_tld'] = [];
                        foreach ($value->listTld as $val) {
                            // mengambil data kontrak_tld
                            $kontrakTld = Kontrak_tld::with('pengguna', 'kontrak:id_kontrak,no_kontrak')->where('id_kontrak_tld', decryptor($val->id))->first();
                            $idTld = decryptor($val->tld);
                            $kontrakTld->update(array('status' => 1, 'id_tld' => $idTld));
                            Master_tld::where('id_tld', $idTld)->update(['status' => 1, 'digunakan' => $kontrakTld->kontrak->no_kontrak]);
                            
                            $params['list_tld'][] = (int) $idTld;
                        }
                    }

                    if($value->jenis == 'tld') {
                        $params['nomer_surpeng'] = generateNoDokumen('surpeng');
                        Kontrak_periode::where('id_kontrak', $idKontrak)
                        ->where('periode', $value->periode)
                        ->update([
                            'nomer_surpeng' => $params['nomer_surpeng'],
                            'created_surpeng_at' => Carbon::now()
                        ]);
                    }
                    
                    Pengiriman_detail::create($params);
                    
                    // menambahkan id_pengiriman ke invoice
                    if($value->jenis == 'invoice'){
                        $invoice = Keuangan::where('id_keuangan', decryptor($value->id))->update(['id_pengiriman' => $idPengiriman]);
                    } else if($value->jenis == 'lhu'){
                        $penyelia = Penyelia::where('id_penyelia', decryptor($value->id))->first();
                        if($penyelia){
                            $penyelia->update(['id_pengiriman' => $idPengiriman]);
                            Permohonan::where('id_permohonan', $penyelia->id_permohonan)->update(['id_pengiriman' => $idPengiriman]);
                        }
                    } else if($value->jenis == 'tld'){
                        if($value->id){
                            $tld = Permohonan::where('id_permohonan', decryptor($value->id))->update(['id_pengiriman' => $idPengiriman]);
                        }
                    }
                }
            }

            DB::commit();

            $result = array(
                'id_pengiriman' => $query->pengiriman_hash,
                'status' => 'Success',
                'msg' => 'Pengiriman berhasil dibuat'
            );

            return $this->output($result);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function destroy(string $idPengiriman)
    {
        $id = $idPengiriman;

        DB::beginTransaction();
        try {
            $fileBukti = Pengiriman::select('bukti_pengiriman','bukti_penerima', 'id_kontrak', 'periode')->where('id_pengiriman', $id)->first();
            $delete = Pengiriman::where('id_pengiriman', $id)->delete();
            $detail = Pengiriman_detail::where('id_pengiriman', $id)->delete();

            // update id_pengiriman di invoice, lhu, dan tld
            Keuangan::where('id_pengiriman', $id)->update(['id_pengiriman' => null]);
            Penyelia::where('id_pengiriman', $id)->update(['id_pengiriman' => null]);
            Permohonan::where('id_pengiriman', $id)->update(['id_pengiriman' => null]);
            Kontrak_periode::where('id_kontrak', $fileBukti->id_kontrak)
            ->where('periode', $fileBukti->periode)
            ->update([
                'nomer_surpeng' => null,
                'created_surpeng_at' => null
            ]);

            DB::commit();

            if($fileBukti && $delete){
                $buktiPengiriman = $fileBukti->bukti_pengiriman;
                $buktiPenerima = $fileBukti->bukti_penerima;

                if($buktiPengiriman){
                    foreach ($buktiPengiriman as $key => $value) {
                        $this->media->destroy($value);
                    }
                }

                if($buktiPenerima){
                    foreach ($buktiPenerima as $key => $value) {
                        $this->media->destroy($value);
                    }
                }

                return $this->output(array('msg' => 'Data berhasil dihapus'));
            }

            return $this->output(array('msg' => 'Data gagal dihapus'), 'Fail', 400);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }
}