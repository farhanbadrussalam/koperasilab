<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Traits\RestApi;

use App\Models\Permohonan;
use App\Models\Permohonan_dokumen;
use App\Models\Kontrak;
use App\Models\Keuangan;
use App\Models\Keuangan_diskon;
use App\Models\Documents;
use App\Models\User;

use App\Models\Jenis_pembayaran;

use App\Http\Controllers\MediaController;
use App\Http\Controllers\NotifController;

use App\Services\Notifier;

use Auth;
use DB;

class KeuanganAPI extends Controller
{
    use RestApi;
    protected $media, $log, $pagination, $notif;

    public function __construct()
    {
        $this->media = resolve(MediaController::class);
        $this->notif = resolve(NotifController::class);
    }

    public function listKeuangan(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 10;
        $page = $request->has('page') ? $request->page : 1;
        $menu = $request->has('menu') ? $request->menu : '';
        $filter = $request->has('filter') ? $request->filter : [];

        switch ($menu) {
            case 'pengajuan':
                $status = [1];
                break;
            case 'pembayaran':
                $status = [3];
                break;
            case 'verifikasi':
                $status = [4];
                break;
            case 'diterima':
                $status = [5];
                break;
            case 'ditolak':
                $status = [90];
                break;
            case 'faktur':
                $status = [2,7];
                break;
            default:
                $status = false;
                break;
        }

        DB::beginTransaction();
        try {
            // Menampilkan data keuangan berdasarkan created_by jika rolenya pelanggan
            $createBy = false;
            if(Auth::user()->hasRole('Pelanggan')){
                $createBy = Auth::user()->id;
            }

            $query = Keuangan::with(
                            'permohonan',
                            'diskon',
                            'usersig',
                            'permohonan.layanan_jasa:id_layanan,nama_layanan',
                            'permohonan.jenisTld:id_jenisTld,name',
                            'permohonan.jenis_layanan:id_jenisLayanan,name,parent',
                            'permohonan.jenis_layanan_parent',
                            'permohonan.pelanggan',
                            'permohonan.pelanggan.perusahaan',
                            'permohonan.kontrak',
                            'permohonan.kontrak.periode'
                        )
                        ->orderBy('created_at','DESC')
                        ->offset(($page - 1) * $limit)
                        ->when($status, function($q, $status) {
                            return $q->whereIn('status', $status);
                        })
                        ->when($createBy, function($q, $createBy) {
                            return $q->whereHas('permohonan', function($q) use ($createBy) {
                                $q->where('created_by', $createBy);
                            })->whereNotIn('status', [1, 2, 7, 91]);
                        })
                        ->when($filter, function($q, $filter) {
                            return $q->whereHas('permohonan', function($p) use ($filter, $q) {
                                foreach ($filter as $key => $value) {
                                    if($key == 'status') {
                                        $q->where($key, decryptor($value));
                                    } else if ($key == 'periode') {
                                        $p->where($key, $value);
                                    } else if ($key == 'date_range') {
                                        
                                    } else {
                                        $p->where($key, decryptor($value));
                                    }
                                }
                            });
                        })
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

    public function listJenisPembayaran(Request $request){
        DB::beginTransaction();
        try {
            $query = Jenis_pembayaran::where('status',1)->get();
            DB::commit();
            return $this->output($query);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function actionJenisPembayaran(Request $request){
        DB::beginTransaction();
        try {
            $idJenisPembayaran = $request->has('id_jenis_pembayaran') ? decryptor($request->id_jenis_pembayaran) : null;
            $idSatuanKerja = $request->has('id_satuan_kerja') ? decryptor($request->id_satuan_kerja) : null;
            $name = $request->has('name') ? $request->name : null;
            $content = $request->has('content') ? $request->content : null;
            $status = $request->has('status') ? (int) $request->status : null;
            $variables = $request->has('variables') ? json_decode($request->variables) : null;

            $data = [];

            $idSatuanKerja && $data['id_satuankerja'] = $idSatuanKerja;
            $name && $data['name'] = $name;
            $status && $data['status'] = $status;
            $content && $data['content'] = $content;
            $variables && $data['variables'] = $variables;

            if($idJenisPembayaran){
                $data['updated_by'] = Auth::user()->id;
            } else {
                $data['created_by'] = Auth::user()->id;
                $data['created_at'] = date('Y-m-d H:i:s');
            }

            $query = Jenis_pembayaran::updateOrCreate(['id_jenis_pembayaran' => $idJenisPembayaran], $data);
            DB::commit();
            return $this->output($query);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function destroyJenisPembayaran($id){
        DB::beginTransaction();
        try {
            $idJenisPembayaran = decryptor($id);
            $query = Jenis_pembayaran::where('id_jenis_pembayaran', $idJenisPembayaran)->get()->each->delete();
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
            $arrStatus = [1,2,3,4,5,6,7];
            $_status = Keuangan::selectRaw('count(*) as total, status')
                ->groupBy('status')
                ->get()
                ->map(function ($item) {
                    return [
                        'total' => (int) $item->total,
                        'status' => (int) $item->status,
                    ];
                })
                ->toArray();
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
            }

            $query = array_map(function($item) {
                switch ($item['status']) {
                    case 1:
                        $item['name'] = 'Pengajuan';
                        break;
                    case 3:
                        $item['name'] = 'Pembayaran';
                        break;
                    case 4:
                        $item['name'] = 'Verifikasi';
                        break;
                    case 5:
                        $item['name'] = 'Diterima';
                        break;
                    case 6:
                        $item['name'] = 'Ditolak';
                        break;
                    case 2:
                    case 7:
                        $item['name'] = 'Faktur';
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

    public function getKeuangan($idKeuangan)
    {
        $idKeuangan = $idKeuangan ? decryptor($idKeuangan) : false;
        DB::beginTransaction();
        try {
            $query = Keuangan::with(
                'permohonan',
                'diskon',
                'usersig',
                'permohonan.layanan_jasa:id_layanan,nama_layanan',
                'permohonan.jenisTld:id_jenisTld,name',
                'permohonan.jenis_layanan:id_jenisLayanan,name,parent',
                'permohonan.jenis_layanan_parent',
                'permohonan.pelanggan',
                'permohonan.pelanggan.perusahaan',
                'permohonan.kontrak',
                'permohonan.kontrak.periode',
            )->find($idKeuangan);

            DB::commit();

            return $this->output($query);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }

    }

    public function keuanganAction(Request $request)
    {
        DB::beginTransaction();
        try {
            $idKeuangan = $request->idKeuangan ? decryptor($request->idKeuangan) : false;
            $idPermohonan = $request->idPermohonan ? decryptor($request->idPermohonan) : false;
            $diskon = $request->diskon ? json_decode($request->diskon) : array();
            $status = $request->status ? $request->status : false;
            $totalHarga = $request->totalHarga ?? false;
            $ppn = $request->ppn ?? false;
            $pph = $request->pph ?? false;
            $ttd = $request->ttd ? decryptor($request->ttd) : false;
            $ttd_by = $request->ttd_by ? decryptor($request->ttd_by) : false;
            $textNote = $request->note ?? '';
            $plt = $request->has('plt') ? $request->plt : false;
            $metodePembayaran = $request->has('metodePembayaran') ? decryptor($request->metodePembayaran) : false;
            $variabelPembayaran = $request->has('variabel_pembayaran') ? json_decode($request->variabel_pembayaran) : false;

            $result = array();
            $data = [];

            $totalHarga && $data['total_harga'] = $totalHarga;
            $ppn && $data['ppn'] = $ppn;
            $pph && $data['pph'] = $pph;
            $idPermohonan && $data['id_permohonan'] = $idPermohonan;
            $plt && $data['plt'] = $plt;
            $metodePembayaran && $data['id_jenis_pembayaran'] = $metodePembayaran;
            $variabelPembayaran && $data['variabel_jenis_pembayaran'] = $variabelPembayaran;

            $data['status'] = $status;

            $invoice = Keuangan::where('id_keuangan', $idKeuangan)->with('permohonan:id_permohonan,created_by')->first();
            if($invoice){
                !$invoice->no_invoice && $data['no_invoice'] = generateNoDokumen('invoice', $idPermohonan);
                !$invoice->created_by && $data['created_by'] = Auth::user()->id;
            }else{
                $data['no_invoice'] = generateNoDokumen('invoice', $idPermohonan);
                $data['created_by'] = Auth::user()->id;
            }

            if($status == 4) { // sudah di bayar perlu verifikasi
                $data['paid_at'] = date('Y-m-d H:i:s');

                // notification perlu di verifikasi oleh admin
                $userQuery = User::role("Staff keuangan");
                $us = Auth::user();
                $dataNotif = array(
                    'pesan' => "Invoice <b>" . $invoice->no_invoice . "</b> telah di bayar, silahkan untuk melakukan verifikasi",
                    'url' => '/staff/keuangan',
                    "event_id" => $invoice->keuangan_hash,
                    'event' => 'Keuangan'
                );
                Notifier::send($userQuery, $dataNotif);
            }

            if($status == 3) { // sudah di verifikasi
                $dataDocument = array();
                $ttd && $dataDocument['ttd'] = $ttd;
                $ttd_by && $dataDocument['ttd_by'] = $ttd_by;

                Permohonan_dokumen::updateOrCreate(
                    ["nomer" => $invoice->no_invoice],
                    $dataDocument
                );

                $data['ttd'] = $ttd;
                $data['ttd_by'] = $ttd_by;
                $data['verif_at'] = date('Y-m-d H:i:s');

                // notification perlu di bayar oleh user
                $userQuery = User::where('id', $invoice->permohonan->created_by);
                $us = Auth::user();
                $dataNotif = array(
                    'pesan' => "Invoice <b>" . $invoice->no_invoice . "</b> telah di buat, silahkan untuk melakukan pembayaran",
                    'url' => '/permohonan/pembayaran/bayar/' . $invoice->keuangan_hash,
                    "event_id" => $invoice->keuangan_hash,
                    'event' => 'Keuangan'
                );
                Notifier::send($userQuery, $dataNotif);
            }

            if($status == 1) {
                $data['ppn'] = null;
                $data['pph'] = null;
                $data['id_jenis_pembayaran'] = null;

                if($invoice){
                    Keuangan_diskon::where('id_keuangan', $invoice->id_keuangan)->delete();
                }
            }

            $keuangan = Keuangan::updateOrCreate(
                ["id_keuangan" => $idKeuangan],
                $data
            );

            foreach ($diskon as $key => $value) {
                Keuangan_diskon::create(array(
                    'id_keuangan' => decryptor($keuangan->keuangan_hash),
                    'name' => $value->name,
                    'diskon' => $value->diskon
                ));
            }

            if($status == 7){ // Invoice di buatkan
                // Simpan dokumen Invoice
                $template = Documents::select('id_doc')->with('footer', 'header')
                            ->where('jenis', 'body')
                            ->where('name', 'Invoice')
                            ->where('status', '1')
                            ->first();

                Permohonan_dokumen::create(array(
                    'id_kontrak' => Permohonan::find($keuangan->id_permohonan)->id_kontrak,
                    'id_permohonan' => $keuangan->id_permohonan,
                    'id_doc_template' => $template->id_doc,
                    'created_by' => Auth::user()->id,
                    'nama' => 'Invoice',
                    'jenis' => 'invoice',
                    'status' => 1,
                    'nomer' => $keuangan->no_invoice
                ));

                // update notifikasi sudah di read
                $this->notif->read(new Request(array(
                    'event' => 'Keuangan',
                    'event_id' => $keuangan->keuangan_hash
                )));
            }

            if($status == 5){ // Diterima
                // Buat dokumen kwitansi
                $template = Documents::select('id_doc')->with('footer', 'header')
                            ->where('jenis', 'body')
                            ->where('name', 'Kwitansi')
                            ->where('status', '1')
                            ->first();
                // ambil ttd invoice
                $invoice = Permohonan_dokumen::select('ttd', 'ttd_by')->where('nomer', $keuangan->no_invoice)->first();
                $no_kwitansi = generateNoDokumen('kwitansi', $keuangan->id_permohonan);
                Permohonan_dokumen::create(array(
                    'id_kontrak' => Permohonan::find($keuangan->id_permohonan)->id_kontrak,
                    'id_doc_template' => $template->id_doc,
                    'created_by' => Auth::user()->id,
                    'nama' => 'Kwitansi',
                    'jenis' => 'kwitansi',
                    'status' => 1,
                    'nomer' => $no_kwitansi,
                    'ttd' => $invoice->ttd,
                    'ttd_by' => $invoice->ttd_by
                ));
            }

            if($status == 2) { // faktur pajak sudah di upload
                // buat notifikasi untuk di tanda tangan oleh manager
                $userQuery = User::role('Manager Keuangan');
                $us = Auth::user();
                $dataNotif = array(
                    'pesan' => 'Invoice <b>'.$keuangan->no_invoice.'</b> telah di buat oleh <b>'.$us->name . '</b>, silahkan tanda tangan',
                    'event' => 'Keuangan',
                    'event_id' => $keuangan->keuangan_hash,
                    'url' => '/manager/pengajuan'
                );
                Notifier::send($userQuery, $dataNotif);
            }

            DB::commit();

            $result['id_keuangan'] = $keuangan->keuangan_hash;

            if ($keuangan->wasRecentlyCreated) {
                $result['status'] = "created";
                $result['msg'] = "Invoice berhasil dibuat.";

                // menambahkan id keuangan ke kontrak
                // $idKontrak = Permohonan::find($idPermohonan)->id_kontrak;
                // if($idKontrak){
                //     $kontrak = Kontrak::find($idKontrak);
                //     $kontrak->update(array('id_keuangan' => $keuangan->id_keuangan));
                // }

                // send notifikasi
                $userQuery = User::role("Staff keuangan");
                $us = Auth::user();
                $dataNotif = array(
                    'pesan' => "Invoice telah diajukan oleh <b>" . $us->name . "</b>.",
                    'url' => "/staff/keuangan",
                    "event_id" => $keuangan->keuangan_hash,
                    "event" => "Keuangan"
                );
                Notifier::send($userQuery, $dataNotif);
            } elseif ($keuangan->wasChanged()) {
                $result['status'] = "updated";
                $result['msg'] = "Invoice berhasil diedit.";
            } else {
                $result['status'] = "none";
                $result['msg'] = "Nothing has changed.";
            }

            return $this->output($result);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }

    }

    public function uploadBuktiBayar(Request $request)
    {
        $validate = $request->validate([
            'idHash' => 'required'
        ]);

        DB::beginTransaction();
        try {
            $idKeuangan = decryptor($request->idHash);
            $file = $request->file('file');

            $fileUpload = $this->media->upload($file, 'keuangan');
            $dataKeuangan = Keuangan::find($idKeuangan);

            if(isset($dataKeuangan)){
                $buktiBayar = is_array($dataKeuangan->bukti_bayar) ? $dataKeuangan->bukti_bayar : [];

                array_push($buktiBayar, $fileUpload->getIdMedia());
                $update = $dataKeuangan->update(array('bukti_bayar' => $buktiBayar));

                DB::commit();

                if($update){
                    $fileUpload->store();
                    // ambil media bukti bayar
                    $mediaBuktiBayar = $this->media->get($fileUpload->getIdMedia());
                    return $this->output(array('msg' => 'Bukti bayar berhasil diupload', 'data' => $mediaBuktiBayar));
                }

                return $this->output(array('msg' => 'Bukti bayar gagal diupload'), 'Fail', 400);
            }

            return $this->output(array('msg' => 'data not found'), 'Fail', 400);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function uploadBuktiBayarPph(Request $request)
    {
        $validate = $request->validate([
            'idHash' => 'required'
        ]);

        DB::beginTransaction();
        try {
            $idKeuangan = decryptor($request->idHash);
            $file = $request->file('file');

            $fileUpload = $this->media->upload($file, 'keuangan');
            $dataKeuangan = Keuangan::find($idKeuangan);

            if(isset($dataKeuangan)){
                $buktiBayarPph = is_array($dataKeuangan->bukti_bayar_pph) ? $dataKeuangan->bukti_bayar_pph : [];

                array_push($buktiBayarPph, $fileUpload->getIdMedia());
                $update = $dataKeuangan->update(array('bukti_bayar_pph' => $buktiBayarPph));

                DB::commit();

                if($update){
                    $fileUpload->store();
                    // ambil media bukti bayar pph
                    $mediaBuktiBayarPph = $this->media->get($fileUpload->getIdMedia());
                    return $this->output(array('msg' => 'Bukti bayar PPH berhasil diupload', 'data' => $mediaBuktiBayarPph));
                }

                return $this->output(array('msg' => 'Bukti bayar PPH gagal diupload'), 'Fail', 400);
            }

            return $this->output(array('msg' => 'data not found'), 'Fail', 400);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function uploadDocumentFaktur(Request $request)
    {
        $validate = $request->validate([
            'idHash' => 'required'
        ]);

        DB::beginTransaction();
        try {
            $idKeuangan = decryptor($request->idHash);
            $file = $request->file('file');

            $fileUpload = $this->media->upload($file, 'keuangan');
            $dataKeuangan = Keuangan::find($idKeuangan);

            if(isset($dataKeuangan)){
                $documentFaktur = is_array($dataKeuangan->document_faktur) ? $dataKeuangan->document_faktur : [];

                array_push($documentFaktur, $fileUpload->getIdMedia());
                $update = $dataKeuangan->update(array('document_faktur' => $documentFaktur));

                DB::commit();

                if($update){
                    $fileUpload->store();
                    // ambil media faktur
                    $mediaFaktur = $this->media->get($fileUpload->getIdMedia());
                    return $this->output(array('msg' => 'Faktur berhasil diupload', 'data' => $mediaFaktur));
                }

                return $this->output(array('msg' => 'Faktur gagal diupload'), 'Fail', 400);
            }

            return $this->output(array('msg' => 'data not found'), 'Fail', 400);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function uploadFaktur(Request $request)
    {
        $validate = $request->validate([
            'idHash' => 'required'
        ]);

        DB::beginTransaction();
        try {
            $idKeuangan = decryptor($request->idHash);
            $file = $request->file('file');

            $fileUpload = $this->media->upload($file, 'keuangan');
            $dataKeuangan = Keuangan::find($idKeuangan);

            if(isset($dataKeuangan)){
                $documentFaktur = is_array($dataKeuangan->document_faktur) ? $dataKeuangan->document_faktur : [];

                array_push($documentFaktur, $fileUpload->getIdMedia());
                $update = $dataKeuangan->update(array('document_faktur' => $documentFaktur));

                DB::commit();

                if($update){
                    $fileUpload->store();
                    // ambil media faktur
                    $mediaFaktur = $this->media->get($fileUpload->getIdMedia());
                    return $this->output(array('msg' => 'Faktur berhasil diupload', 'data' => $mediaFaktur));
                }

                return $this->output(array('msg' => 'Faktur gagal diupload'), 'Fail', 400);
            }

            return $this->output(array('msg' => 'data not found'), 'Fail', 400);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function destroyBuktiBayar($idKeuangan, $idMedia){
        $idMedia = decryptor($idMedia);
        $idKeuangan = decryptor($idKeuangan);

        DB::beginTransaction();
        try {
            $dataKeuangan = Keuangan::find($idKeuangan);
            $buktiBayar = is_array($dataKeuangan->bukti_bayar) ? $dataKeuangan->bukti_bayar : [];

            if(($key = array_search($idMedia, $buktiBayar)) !== false) {
                unset($buktiBayar[$key]);
            }
            // atur menjadi array biasa jangan array object
            $buktiBayar = array_values($buktiBayar);

            $update = $dataKeuangan->update(array('bukti_bayar' => $buktiBayar));
            $this->media->destroy($idMedia);

            DB::commit();

            if($update){
                return $this->output(array('msg' => 'Bukti bayar berhasil dihapus'));
            }

            return $this->output(array('msg' => 'Bukti bayar gagal dihapus'), 'Fail', 400);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function destroyBuktiBayarPph($idKeuangan, $idMedia){
        $idMedia = decryptor($idMedia);
        $idKeuangan = decryptor($idKeuangan);

        DB::beginTransaction();
        try {
            $dataKeuangan = Keuangan::find($idKeuangan);
            $buktiBayarPph = is_array($dataKeuangan->bukti_bayar_pph) ? $dataKeuangan->bukti_bayar_pph : [];

            if(($key = array_search($idMedia, $buktiBayarPph)) !== false) {
                unset($buktiBayarPph[$key]);
            }
            // atur menjadi array biasa jangan array object
            $buktiBayarPph = array_values($buktiBayarPph);

            $update = $dataKeuangan->update(array('bukti_bayar_pph' => $buktiBayarPph));
            $this->media->destroy($idMedia);

            DB::commit();

            if($update){
                return $this->output(array('msg' => 'Bukti bayar PPH berhasil dihapus'));
            }

            return $this->output(array('msg' => 'Bukti bayar PPH gagal dihapus'), 'Fail', 400);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }


    public function destroyFaktur($idKeuangan, $idMedia){
        $idMedia = decryptor($idMedia);
        $idKeuangan = decryptor($idKeuangan);

        DB::beginTransaction();
        try {
            $dataKeuangan = Keuangan::find($idKeuangan);
            $documentFaktur = is_array($dataKeuangan->document_faktur) ? $dataKeuangan->document_faktur : [];

            if(($key = array_search($idMedia, $documentFaktur)) !== false) {
                unset($documentFaktur[$key]);
            }
            $documentFaktur = array_values($documentFaktur);

            $update = $dataKeuangan->update(array('document_faktur' => $documentFaktur));
            $this->media->destroy($idMedia);

            DB::commit();

            if($update){
                return $this->output(array('msg' => 'Faktur berhasil dihapus'));
            }

            return $this->output(array('msg' => 'Faktur gagal dihapus'), 'Fail', 400);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }

    }

    // PRIVATE FUNCTION
}
