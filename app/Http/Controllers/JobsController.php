<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use Illuminate\Http\Request;

use Auth;
use DataTables;

class JobsController extends Controller
{
    public function indexFrontdesk(){
        $data['token'] = generateToken();
        return view('pages.jobs.frontdesk', $data);
    }

    public function indexPelaksanaKontrak(){
        $data['token'] = generateToken();
        return view('pages.jobs.pelaksana', $data);
    }

    public function indexPenyelia(){
        $data['token'] = generateToken();
        return view('pages.jobs.penyelia', $data);
    }

    public function getData(){
        $user = Auth::user();

        // initialisasi
        $jobs = request('jobs');
        $type = request('type');
        $status = array();
        $flag = false;

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
            }
        }

        $informasi = Permohonan::with(['layananjasa', 'jadwal','user'])
                        ->whereIn('status', $status)
                        ->where('flag', $flag)
                        ->orderBy('jadwal_id', 'desc')
                        ->orderBy('nomor_antrian', 'desc');

        return DataTables::of($informasi)
            ->addIndexColumn()
            ->addColumn('content', function($data) use ($jobs) {
                $idHash = "'".$data->permohonan_hash."'";
                $btnAction = '';
                $co_noted = '';
                $btnRincian = '<button class="btn btn-outline-primary btn-sm" onclick="modalConfirm('.$idHash.')">
                                <i class="bi bi-info-circle"></i> Rincian</button>';

                if($data->status == 1){
                    $btnAction .= '
                        <button class="btn btn-outline-primary btn-sm" onclick="modalConfirm('.$idHash.')"><i
                            class="bi bi-check2-circle"></i> Cek berkas</button>
                    ';
                }else if($data->status == 2){
                    if($data->flag == 1){
                        $btnAction .= '
                            <button class="btn btn-outline-success btn-sm mb-1" onclick="btnVerifikasi('.$idHash.')">
                                <i class="bi bi-check"></i> Verifikasi</button>
                        ';
                    }else if($data->flag == 2 && $jobs == 'pelaksana'){
                        $btnAction .= '
                            <button class="btn btn-outline-primary btn-sm" onclick="modalConfirm('.$idHash.')"><i
                                class="bi bi-check2-circle"></i> Cek berkas</button>
                        ';
                        $co_noted .= '
                            <div id="reason" class="rounded p-2 col-12 mt-2 bg-sm-secondary d-block">
                                <small><b class="text-info-emphasis">Note:</b> '.($data->progress ? $data->progress->note : "").'</small>
                            </div>
                        ';
                    }

                    if($jobs == 'frontdesk'){
                        $btnAction .= $btnRincian;
                    }
                }else if($data->status == 3){
                    if($data->flag == 3){
                        $btnAction .= '
                            <div class="dropdown">
                                <div class="more-option d-flex align-items-center justify-content-center mx-0 mx-md-4" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </div>
                                <ul class="dropdown-menu shadow-sm px-2">
                                    <li class="my-1 cursoron">
                                        <a class="dropdown-item dropdown-item-lab" onclick="createSurat('.$idHash.')">
                                            Buat surat tugas
                                        </a>
                                    </li>
                                    <li class="my-1 cursoron">
                                        <a class="dropdown-item dropdown-item-lab" onclick="modalConfirm('.$idHash.')">
                                            Rincian
                                        </a>
                                    </li>
                                </ul>
                            </div>

                        ';
                    }
                }else if($data->status == 9){
                    if($data->flag == 2){
                        $btnAction .= '
                            <button class="btn btn-outline-danger btn-sm mb-2" onclick="confirmReturn('.$idHash.')">
                                <i class="bi bi-arrow-return-left"></i> Return</button>
                            '.$btnRincian.'
                        ';
                    }
                }

                $co_reason = $data->status == 9 ? '
                        <div id="reason" class="rounded p-2 col-12 mt-2 bg-sm-secondary d-block">
                            <small class="text-danger-emphasis"><b>Reason:</b> '.($data->progress ? $data->progress->note : "").'</small>
                        </div>
                    ' : '';

                $labelTag = '';
                if($data->tag != 'pengajuan'){
                    $labelColor = $data->tag == 'baru' ? 'bg-success' : 'bg-primary';
                    $labelTag = '
                        <div class="ribbon-wrapper">
                            <div class="ribbon '.$labelColor.'" title="Tag">
                                '.$data->tag.'
                            </div>
                        </div>
                    ';
                }

                return '
                <div class="card m-0 border-0">
                    '.$labelTag.'
                    <div class="card-body d-flex flex-wrap p-3 align-items-center">
                        <div class="col-md-6 col-sm-12 mb-sm-2">
                            <span class="fw-bold">'.$data->layananjasa->nama_layanan.'</span>
                            <div><b>No kontrak :</b> '.($data->no_kontrak ? $data->no_kontrak : "-").'</div>
                            <div class="text-body-secondary text-start">
                                <div>
                                    <small><b>Start date</b> : '.convert_date($data->jadwal->date_mulai, 1).'</small>
                                    <small><b>End date</b> : '.convert_date($data->jadwal->date_selesai, 1).'</small>
                                </div>
                                <small><b>Created</b> : '.convert_date($data->created_at, 1).'</small><br>
                                <small><b>Customer</b> : '.$data->user->name.'</small>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-5 h5">
                            <span class="badge text-bg-secondary">'.$data->jenis_layanan.'</span>
                        </div>
                        <div class="col-md-2 col-sm-5 h5">
                            <span class="badge text-bg-info">Antrian '.$data->nomor_antrian.'</span>
                        </div>
                        <div class="col-md-2 col-sm-2" style="z-index: 10;">
                            '.$btnAction.'
                        </div>
                        '.$co_reason.'
                        '.$co_noted.'
                    </div>
                </div>
                ';
            })
            ->rawColumns(['content'])
            ->make(true);
    }
}
