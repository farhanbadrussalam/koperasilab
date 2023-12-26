<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\tbl_kip;
use App\Models\Permohonan;
use App\Models\Detail_permohonan;

use Auth;
use DataTables;

class KeuanganController extends Controller
{
    public function index()
    {
        $data['token'] = generateToken();
        return view('pages.keuangan.index', $data);
    }

    public function sendKIP(Request $request)
    {
        $validator = $request->validate([
            'idPermohonan' => 'required',
        ]);

        $idPermohonan = $request->idPermohonan ? decryptor($request->idPermohonan) : null;

        $permohonanD = Permohonan::where('id', $idPermohonan)->first();

        $noInvoice = 'I-'.generate();
        $data = array(
            'no_kontrak' => $idPermohonan,
            'no_invoice' => $noInvoice,
            'harga' => $request->harga,
            'pajak' => $request->pajak,
            'status' => 1,
            'created_by' => Auth::user()->id
        );

        tbl_kip::create($data);

        // set permohonan
        // reset status detail to 99
        Detail_permohonan::where('permohonan_id', $idPermohonan)->update(['status' => '99']);
        // save to detail permohonan
        $flag = 3;
        $tmpDetail = array(
            'permohonan_id' => $idPermohonan,
            'status' => 1,
            'flag' => $flag,
            'note' => 'Melakukan pembayaran',
            'created_by' => Auth::user()->id
        );
        $createDetail = Detail_permohonan::create($tmpDetail);
        if($createDetail){
            $permohonanD->flag = $flag;
            $permohonanD->update();
        }

        // Send notif
        $sendNotifPelanggan = notifikasi(array(
            'to_user' => $permohonanD->created_by,
            'type' => 'Permohonan'
        ), "Silahkan melakukan pembayaran dengan invoice " . $noInvoice);

        return response()->json(['message' => 'Invoice berhasil terkirim'], 200);
    }

    public function getPermohonan(){
        $user = Auth::user();

        $informasi = Permohonan::with('layananjasa', 'layananjasa.satuanKerja', 'jadwal', 'user', 'tbl_kip')
                        ->whereIn('flag', [2, 3, 4])
                        ->where('status', 1);

        return DataTables::of($informasi)
            ->addIndexColumn()
            ->addColumn('content', function($data) {
                $idHash = "'".$data->permohonan_hash."'";
                $co_progress = '';
                $listItem = '';
                $labelTag = '';

                if($data->flag == 3 || $data->flag == 4){
                    if($data->tbl_kip->bukti_pembayaran){
                        $co_progress = '
                            <div id="progress" class="rounded p-2 col-12 mt-2 bg-sm-secondary d-block">
                                <small><b class="text-info-emphasis">Sudah membayar silahkan cek buktinya <a href="javascript:void(0)" onclick="showBukti('.$idHash.')">Disini</a></b> </small>
                            </div>
                        ';
                    }else{
                        $co_progress = '
                            <div id="progress" class="rounded p-2 col-12 mt-2 bg-sm-secondary d-block">
                                <small><b class="text-success-emphasis"><b>Progress:</b> Invoice dibuat</small>
                            </div>
                        ';
                    }
                    if($data->tbl_kip->status == 3){
                        $listItem .= '
                            <li class="my-1 cursoron">
                                <a class="dropdown-item dropdown-item-lab" onclick="">
                                    Cetak kuitansi
                                </a>
                            </li>
                        ';

                        $labelTag = '
                            <div class="ribbon-wrapper">
                                <div class="ribbon bg-success" title="Tag">
                                    Lunas
                                </div>
                            </div>
                        ';
                    }
                    $listItem .= '
                        <li class="my-1 cursoron">
                            <a class="dropdown-item dropdown-item-lab" onclick="createInvoice('.$idHash.', true)">
                                Lihat invoice
                            </a>
                        </li>
                    ';
                }else{
                    $listItem = '
                        <li class="my-1 cursoron">
                            <a class="dropdown-item dropdown-item-lab" onclick="createInvoice('.$idHash.')">
                                Buat invoice
                            </a>
                        </li>
                    ';
                }

                $btnAction = '
                    <div class="dropdown">
                        <div class="more-option d-flex align-items-center justify-content-center mx-0 mx-md-4" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-three-dots-vertical"></i>
                        </div>
                        <ul class="dropdown-menu shadow-sm px-2">
                            '.$listItem.'
                            <li class="my-1 cursoron">
                                <a class="dropdown-item dropdown-item-lab" onclick="modalConfirm('.$idHash.')">
                                    Rincian
                                </a>
                            </li>
                        </ul>
                    </div>

                ';
                return '
                <div class="card m-0 border-0">
                    '.$labelTag.'
                    <div class="card-body d-flex flex-wrap p-3 align-items-center">
                        <div class="col-md-5 col-sm-12 mb-sm-2">
                            <span class="fw-bold">'.$data->layananjasa->nama_layanan.'</span>
                            <div><b>Satuan kerja :</b> '. $data->layananjasa->satuankerja->name .'</div>
                            <div class="text-body-secondary text-start">
                                <div>
                                    <small><b>Start date</b> : '.convert_date($data->jadwal->date_mulai, 1).'</small>
                                    <small><b>End date</b> : '.convert_date($data->jadwal->date_selesai, 1).'</small>
                                </div>
                                <small><b>Created</b> : '.convert_date($data->created_at, 1).'</small><br>
                                <small><b>Customer</b> : '.$data->user->name.'</small>
                            </div>
                        </div>
                        <div class="col-md-5 col-sm-5 h5">
                            <span class="badge text-bg-secondary">'.$data->jenis_layanan.'</span>
                        </div>
                        <div class="col-md-2 col-sm-2" style="z-index: 10;">
                            '.$btnAction.'
                        </div>
                        '.$co_progress.'
                    </div>
                </div>
                ';
            })
            ->rawColumns(['content'])
            ->make(true);
    }

    public function tolakBuktiPembayaran(Request $request){
        $validator = $request->validate([
            'note' => 'required',
            'idPermohonan' => 'required'
        ]);

        $idPermohonan = $request->idPermohonan ? decryptor($request->idPermohonan) : null;
        $note = $request->note;

        $kip = tbl_kip::where('no_kontrak', $idPermohonan)->first();
        $kip->status = 9;
        $kip->bukti_pembayaran = null;
        $kip->update();

        // set permohonan
        // reset status detail to 99
        Detail_permohonan::where('permohonan_id', $idPermohonan)->update(['status' => '99']);
        // save to detail permohonan
        $tmpDetail = array(
            'permohonan_id' => $idPermohonan,
            'status' => 1,
            'flag' => 3,
            'note' => $note,
            'created_by' => Auth::user()->id
        );
        $createDetail = Detail_permohonan::create($tmpDetail);

        return response()->json(['message' => 'Berhasil dikirim'], 200);
    }

    public function setujuBuktiPembayaran(Request $request){
        $validator = $request->validate([
            'idPermohonan' => 'required'
        ]);

        $idPermohonan = decryptor($request->idPermohonan);

        $kip = tbl_kip::with('permohonan')->where('no_kontrak', $idPermohonan)->first();
        $kip->status = 3;
        $kip->update();

        // update permohonan
        $permohonanD = Permohonan::where('id', $idPermohonan)->first();
        $permohonanD->status = 4;
        $permohonanD->update();

        // set permohonan
        // reset status detail to 99
        Detail_permohonan::where('permohonan_id', $idPermohonan)->update(['status' => '99']);
        // save to detail permohonan
        $tmpDetail = array(
            'permohonan_id' => $idPermohonan,
            'status' => 1,
            'flag' => 3,
            'note' => 'Pembayaran berhasil',
            'created_by' => Auth::user()->id
        );
        $createDetail = Detail_permohonan::create($tmpDetail);

        // Send notif
        $sendNotifPelanggan = notifikasi(array(
            'to_user' => $kip->permohonan->created_by,
            'type' => 'Keuangan'
        ), " Pembayaran selesai no invoice : " . $kip->no_invoice);

        return response()->json(['message' => 'Pembayaran selesai'], 200);
    }
}
