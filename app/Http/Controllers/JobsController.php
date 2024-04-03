<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\tbl_media;
use App\Models\tbl_lhu;
use App\Models\jadwal;
use App\Models\pertanyaan_lhu;
use Illuminate\Http\Request;

use Auth;
use DataTables;

class JobsController extends Controller
{
    public function indexFrontdesk(){
        $data = [
            'title' => 'Front desk',
            'module' => 'frontdesk'
        ];

        return view('pages.jobs.frontdesk', $data);
    }

    public function indexPelaksanaKontrak(){
        $data = [
            'title' => 'Pelaksana kontrak',
            'module' => 'pelaksanakontrak'
        ];

        return view('pages.jobs.pelaksana', $data);
    }

    public function indexPenyelia(){
        $data = [
            'title' => 'Penyelia lab',
            'module' => 'penyelialab'
        ];

        return view('pages.jobs.penyelia', $data);
    }

    public function indexPelaksanaLab()
    {
        $data = [
            'title' => 'Pelaksana lab',
            'module' => 'pelaksanalab',
            'pertanyaan' => pertanyaan_lhu::all()
        ];

        return view('pages.jobs.pelaksanaLab', $data);
    }

    public function getData(){
        $user = Auth::user();

        // initialisasi
        $jobs = request('jobs');
        $type = request('type');
        $status = array();
        $flag = false;
        $suratTugas = false;

        $informasi = Permohonan::with(['layananjasa', 'jadwal','user', 'tbl_lhu', 'tbl_kip']);

        // pembagian
        if($jobs == 'frontdesk'){
            if($type == 'layanan'){
                $flag = 1;
                $status = [1, 2];
            }else if($type == 'diteruskan'){
                $flag = 2;
                $status = [2];
            }else if($type == 'return'){
                $flag = 2;
                $status = [9];
            }else if($type == 'lhukip') {
                $flag = 3;
                $status = [3];
                $informasi->whereHas('tbl_lhu', function ($query) {
                    $query->where('level', 4);
                });
                $informasi->whereHas('tbl_kip', function ($query) {
                    $query->where('status', 2);
                });
            }
        }else if($jobs == "pelaksana"){
            if($type == 'layanan'){
                $flag = 2;
                $status = [2];
            }
        }else if($jobs == "penyelia"){
            if($type == 'layanan'){
                $flag = 3;
                $status = [3];
                $suratTugas = 1;
            }
        }else if($jobs == 'pelaksanaLab'){
            if($type == "surat_tugas"){
                $flag = 3;
                $status = [3];
                $surat_tugas = 2;
            }
        }else if($jobs == 'keuangan'){
            if($type == "layanan"){
                $flag = 3;
                $status = [3];
            }
        }

        $informasi->whereIn('status', $status)
                    ->where('flag', $flag)
                    ->orderBy('created_at', 'desc');

        if($suratTugas == 1){
            $informasi->doesntHave('tbl_lhu');
        }else if($suratTugas == 2){
            $informasi->whereHas('tbl_lhu', function ($query) {
                $query->where('level', 1);
            });
        }

        return DataTables::of($informasi)
            ->addIndexColumn()
            ->addColumn('content', function($data) use ($jobs) {
                $idHash = "'".$data->permohonan_hash."'";
                $btnAction = '';
                $co_noted = '';
                $co_progress = '';
                $labelTag = '';
                $co_reason = '';
                $co_status = statusFormat($jobs, $data->status);

                // if($data->status == 2){
                //     if($data->flag == 1){
                //         $btnAction .= '
                //             <button class="btn btn-outline-success btn-sm mb-1" onclick="btnVerifikasi('.$idHash.')">
                //                 <i class="bi bi-check"></i> Verifikasi</button>
                //         ';
                //     }else if($data->flag == 2 && $jobs == 'pelaksana'){
                //         $btnAction .= '
                //             <button class="btn btn-outline-primary btn-sm" onclick="modalConfirm('.$idHash.')"><i
                //                 class="bi bi-check2-circle"></i> Cek berkas</button>
                //         ';
                //         $co_noted .= '
                //             <div id="reason" class="rounded p-2 col-12 mt-2 bg-sm-secondary d-block">
                //                 <small><b class="text-info-emphasis">Note:</b> '.($data->progress ? $data->progress->note : "").'</small>
                //             </div>
                //         ';
                //     }

                //     if($jobs == 'frontdesk'){
                //         $btnAction .= $btnRincian;
                //     }
                // }else if($data->status == 3){
                //     if($data->flag == 3){
                //         $listItem = '';
                //         if($jobs == "keuangan"){
                //             if(isset($data->tbl_kip)){
                //                 if($data->tbl_kip->status == 1){
                //                     $co_status = '
                //                         <div class="col-md-2 col-sm-5 h5">
                //                             Invoice dibuat
                //                         </div>
                //                     ';
                //                 }
                //                 if($data->tbl_kip->bukti_pembayaran){
                //                     $co_progress = '
                //                         <div id="progress" class="rounded p-2 col-12 mt-2 bg-sm-secondary d-block">
                //                             <small><b class="text-info-emphasis">Sudah membayar silahkan cek buktinya <a href="javascript:void(0)" onclick="showBukti('.$idHash.')">Disini</a></b> </small>
                //                         </div>
                //                     ';
                //                 }
                //                 $listItem = '
                //                     <li class="my-1 cursoron">
                //                         <a class="dropdown-item dropdown-item-lab" onclick="createInvoice('.$idHash.', true)">
                //                             Lihat invoice
                //                         </a>
                //                     </li>
                //                 ';
                //             }else{
                //                 $listItem = '
                //                     <li class="my-1 cursoron">
                //                         <a class="dropdown-item dropdown-item-lab" onclick="createInvoice('.$idHash.')">
                //                             Buat invoice
                //                         </a>
                //                     </li>
                //                 ';
                //             }
                //         }else if($jobs == "penyelia"){
                //             $listItem = '
                //                 <li class="my-1 cursoron">
                //                     <a class="dropdown-item dropdown-item-lab" onclick="createSurat('.$idHash.')">
                //                         Kirim surat tugas
                //                     </a>
                //                 </li>
                //             ';
                //         }else if($jobs == "frontdesk"){
                //             $listItem = '
                //                 <li class="my-1 cursoron">
                //                     <a class="dropdown-item dropdown-item-lab" onclick="detailkiplhu('.$idHash.')">
                //                         Lihat KIP / LHU
                //                     </a>
                //                 </li>
                //             ';
                //         }
                //         $btnAction .= '
                //             <div class="dropdown">
                //                 <div class="more-option d-flex align-items-center justify-content-center mx-0 mx-md-4" data-bs-toggle="dropdown" aria-expanded="false">
                //                     <i class="bi bi-three-dots-vertical"></i>
                //                 </div>
                //                 <ul class="dropdown-menu shadow-sm px-2">
                //                     '.$listItem.'
                //                     <li class="my-1 cursoron">
                //                         <a class="dropdown-item dropdown-item-lab" onclick="modalConfirm('.$idHash.')">
                //                             Rincian
                //                         </a>
                //                     </li>
                //                 </ul>
                //             </div>

                //         ';
                //     }
                // }else if($data->status == 9){
                //     if($data->flag == 2){
                //         $btnAction .= '
                //             <button class="btn btn-outline-danger btn-sm mb-2" onclick="confirmReturn('.$idHash.')">
                //                 <i class="bi bi-arrow-return-left"></i> Return</button>
                //             '.$btnRincian.'
                //         ';
                //     }
                // }

                // $co_reason = $data->status == 9 ? '
                //         <div id="reason" class="rounded p-2 col-12 mt-2 bg-sm-secondary d-block">
                //             <small class="text-danger-emphasis"><b>Reason:</b> '.($data->progress ? $data->progress->note : "").'</small>
                //         </div>
                //     ' : '';

                // if($data->tag != 'pengajuan'){
                //     $labelColor = $data->tag == 'baru' ? 'bg-success' : 'bg-primary';
                //     $labelTag = '
                //         <div class="ribbon-wrapper">
                //             <div class="ribbon '.$labelColor.'" title="Tag">
                //                 '.$data->tag.'
                //             </div>
                //         </div>
                //     ';
                // }


                switch ($jobs) {
                    case 'frontdesk':
                        if($data->status == 1){
                            $btnAction .= '<button class="btn btn-outline-primary btn-sm" onclick="show_detail_permohonan('.$idHash.')"><i class="bi bi-check2-circle"></i> Cek berkas</button>';
                        }else if($data->status == 9 && $data->flag == 2){
                            $btnAction .= '
                                <button class="btn btn-outline-danger btn-sm mb-2" onclick="confirmReturn('.$idHash.')">
                                    <i class="bi bi-arrow-return-left"></i> Return</button>
                            ';
                            $co_reason = '
                                <div id="reason" class="rounded p-2 col-12 mt-2 bg-sm-secondary d-block">
                                    <small class="text-danger-emphasis"><b>Reason:</b> '.($data->progress ? $data->progress->note : "").'</small>
                                </div>
                            ';
                        }
                        break;
                    case 'pelaksana':
                        $btnAction .= '
                            <button class="btn btn-outline-primary btn-sm" onclick="show_detail_permohonan('.$idHash.')">
                                <i class="bi bi-check2-circle"></i> Cek berkas</button>
                        ';
                        $co_noted .= '
                            <div id="reason" class="rounded p-2 col-12 mt-2 bg-sm-secondary d-block">
                                <small><b class="text-info-emphasis">Note:</b> '.($data->progress ? $data->progress->note : "").'</small>
                            </div>
                        ';
                        break;
                    case 'penyelia':
                        $permohonanJadwal = jadwal::where('permohonan_id', decryptor($data->permohonan_hash))->first();

                        $btnAction .= '<button class="btn btn-info btn-sm me-2" onclick="show_detail_permohonan('.$idHash.')"><i class="bi bi-info-circle"></i></button>';
                        if(isset($permohonanJadwal)){
                            $btnAction .= '<button class="btn btn-success btn-sm" onclick="showSurat('.$idHash.')"><i class="bi bi-eye-fill"></i> Surat tugas</button>';
                        }else{
                            $btnAction .= '<button class="btn btn-primary btn-sm" onclick="createSurat('.$idHash.')"><i class="bi bi-plus-circle"></i> Surat tugas</button>';
                        }
                        $co_noted .= '
                            <div id="reason" class="rounded p-2 col-12 mt-2 bg-sm-secondary d-block">
                                <small><b class="text-info-emphasis">Note:</b> '.($data->progress ? $data->progress->note : "").'</small>
                            </div>
                        ';
                        break;
                    case 'keuangan':
                        $btnAction .= '<button class="btn btn-info btn-sm me-2" onclick="show_detail_permohonan('.$idHash.')"><i class="bi bi-info-circle"></i></button>';

                        if(isset($data->tbl_kip)){
                            $btnAction .= '<button class="btn btn-outline-primary btn-sm" onclick="createInvoice('.$idHash.', true)"><i class="bi bi-eye-fill"></i> Lihat invoice</button>';
                        }else{
                            $btnAction .= '<button class="btn btn-outline-primary btn-sm" onclick="createInvoice('.$idHash.')"><i class="bi bi-plus-circle"></i> Buat invoice</button>';
                        }
                        break;
                    default:

                        break;
                }

                return '
                <div class="card m-0 border">
                    '.$labelTag.'
                    <div class="card-body d-flex flex-wrap p-3 align-items-center">
                        <div class="col-md-6 col-sm-12 mb-sm-2">
                            <span class="fw-bold">'.$data->layananjasa->nama_layanan.'</span>
                            <div><b>No kontrak :</b> '.($data->no_kontrak ? $data->no_kontrak : "-").'</div>
                            <div class="text-body-secondary text-start">
                                <small><b>Created</b> : '.convert_date($data->created_at, 1).'</small><br>
                                <small><b>Customer</b> : '.$data->user->name.'</small>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-5 h5">
                            <span class="badge text-bg-secondary">'.$data->jenis_layanan.'</span>
                        </div>
                        <div class="col-md-2 col-sm-5">
                            '.$co_status.'
                        </div>
                        <div class="col-md-2 col-sm-2" style="z-index: 10;">
                            '.$btnAction.'
                        </div>
                        '.$co_reason.'
                        '.$co_noted.'
                        '.$co_progress.'
                    </div>
                </div>
                ';
            })
            ->rawColumns(['content'])
            ->make(true);
    }

    public function getDataPelaksanaLab()
    {
        $user = Auth::user();

        $informasi = jadwal::with(['permohonan', 'permohonan.user', 'signature_1', 'user', 'tbl_lhu'])
                        ->where('status', 1)
                        ->doesntHave('tbl_lhu')
                        ->orderBy('created_at', 'desc');
        // $informasi = Permohonan::with(['layananjasa', 'jadwal','user', 'tbl_lhu'])
        //                 ->where('status', 3)
        //                 ->where('flag', 3)
        //                 ->whereHas('tbl_lhu', function($query){
        //                     $query->where('level', '1');
        //                 })
        //                 ->orderBy('jadwal_id', 'desc')
        //                 ->orderBy('nomor_antrian', 'desc');

        return DataTables::of($informasi)
            ->addIndexColumn()
            ->addColumn('content', function($data) {
                $permohonanId = "'".encryptor($data->permohonan_id)."'";
                $jadwalHash = "'".$data->jadwal_hash."'";
                $permohonanHash = "'".$data->permohonan->permohonan_hash."'";

                // $media = tbl_media::where('id', $data->tbl_lhu->surat_tugas)->first();
                $labelTag = '';

                if($data->tbl_lhu){
                    $btnCreateLHU = '<button class="btn btn-outline-primary btn-sm" onclick="showLHU('.$jadwalHash.')">
                                    <i class="bi bi-eye-fill"></i> Lihat LHU</button>';
                }else{
                    $btnCreateLHU = '<button class="btn btn-outline-primary btn-sm" onclick="createLHU('.$jadwalHash.')">
                                    <i class="bi bi-plus-circle"></i> Buat LHU</button>';
                }
                $btnShowTugas = '<a class="btn btn-outline-warning btn-sm mt-1" href="'.url('laporan/suratTugas/'.$data->jadwal_hash).'" target="_blank"><i class="bi bi-eye-fill"></i> Surat tugas</a>';
                $btnDetail = '<button class="btn btn-outline-info btn-sm me-2 mt-1" onclick="show_detail_permohonan('.$permohonanHash.')"><i class="bi bi-info-circle"></i> Detail</button>';

                return '
                <div class="card m-0">
                    '.$labelTag.'
                    <div class="card-body d-flex flex-wrap p-3 align-items-center">
                        <div class="col-md-5 col-sm-12 mb-sm-2">
                            <span class="fw-bold">'.$data->permohonan->jenis_layanan.'</span>
                            <div class="text-body-secondary text-start">
                                <small><b>Created</b> : '.convert_date($data->permohonan->created_at, 1).'</small><br>
                                <small><b>Customer</b> : '.$data->permohonan->user->name.'</small>
                            </div>
                        </div>
                        <div class="text-center col-md-5 col-sm-5">
                            <div>
                                <label class="fw-bolder">Start date</label>
                                <div>'.convert_date($data->date_mulai, 1).'</div>
                            </div>
                            <div>
                                <label class="fw-bolder">End date</label>
                                <div>'.convert_date($data->date_selesai, 1).'</div>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-2" style="z-index: 10;">
                            '.$btnCreateLHU.'
                            '.$btnShowTugas.'
                            '.$btnDetail.'
                        </div>
                    </div>
                </div>
                ';
            })
            ->rawColumns(['content'])
            ->make(true);
    }

    public function getDataLhu()
    {
        $informasi = tbl_lhu::with(['jadwal', 'jadwal.permohonan.layananjasa', 'signature_1:id,name', 'user:id,name'])->where('level', 2)->where('active', 2);

        return DataTables::of($informasi)
                ->addIndexColumn()
                ->addColumn('content', function($data) {
                    $idHash = "'".$data->lhu_hash."'";
                    $permohonanHash = "'".encryptor($data->jadwal->permohonan_id)."'";

                    $btnConfirm = '<button class="btn btn-outline-primary btn-sm" id="btn-confirm-lhu" data-id="'.$idHash.'" onclick="btnConfirm('.$idHash.')">
                                    <i class="bi bi-info-circle"></i> Confirm</button>';
                    $btnDetail = '<button class="btn btn-outline-info btn-sm me-2 mt-1" onclick="show_detail_permohonan('.$permohonanHash.')"><i class="bi bi-info-circle"></i> Detail</button>';

                    return '
                    <div class="card m-0">
                        <div class="card-body d-flex flex-wrap p-3 align-items-center">
                            <div class="col-md-6 col-sm-12 mb-sm-2">
                                <span class="fw-bold">'.$data->jadwal->permohonan->layananjasa->nama_layanan.'</span>
                                <div class="text-body-secondary text-start">
                                    <small><b>Created</b> : '.convert_date($data->created_at, 1).'</small><br>
                                    <small><b>Customer</b> : '.$data->user->name.'</small>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-5 h5">
                                <span class="badge text-bg-secondary">'.$data->jadwal->permohonan->jenis_layanan.'</span>
                            </div>
                            <div class="col-md-2 col-sm-2" style="z-index: 10;">
                                '.$btnConfirm.'
                                '.$btnDetail.'
                            </div>
                            <div class="col-12">
                            </div>
                        </div>
                    </div>
                    ';
                })
                ->rawColumns(['content'])
                ->make(true);
    }
}
