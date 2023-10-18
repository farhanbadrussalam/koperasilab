<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use Illuminate\Http\Request;

use Auth;
use DataTables;

class FrontdeskController extends Controller
{
    public function index(){
        $data['token'] = generateToken();
        return view('pages.frontdesk.index', $data);
    }

    public function getData(){
        $user = Auth::user();

        $flag = request('flag');
        // if(request()->has('flag') && request('flag')){
        // }
        $informasi = Permohonan::with(['layananjasa', 'jadwal','user'])
                        ->where('status', '!=', 99)
                        ->where('flag', $flag)
                        ->orderBy('jadwal_id', 'desc')
                        ->orderBy('nomor_antrian', 'desc');
        // dd($informasi);
        return DataTables::of($informasi)
                ->addIndexColumn()
                ->addColumn('content', function($data) {
                    $idHash = "'".$data->permohonan_hash."'";
                    $btnAction = '';
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
                        }
                        $btnAction .= '
                            <button class="btn btn-outline-primary btn-sm" onclick="modalConfirm('.$idHash.')">
                                <i class="bi bi-info-circle"></i> Rincian</button>
                        ';
                    }

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
                            <div class="col-md-2 col-sm-2">
                                '.$btnAction.'
                            </div>
                        </div>
                    </div>
                    ';
                })
                ->rawColumns(['content'])
                ->make(true);
    }
}
