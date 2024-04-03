<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permohonan;

use Auth;
use DataTables;

class ManagerController extends Controller
{
    public function index()
    {
        $data = [
            'title' => 'LHU & KIP',
            'module' => 'lhukip'
        ];

        return view('pages.manager.lhu-kip', $data);
    }

    public function getData()
    {
        $informasi = Permohonan::with(['layananjasa', 'jadwal', 'tbl_kip', 'tbl_lhu'])
                        ->whereHas('tbl_lhu', function ($query) {
                            $query->where('level', 3);
                        })
                        ->orWhereHas('tbl_kip', function ($query) {
                            $query->where('status', 1);
                        });

        return DataTables::of($informasi)
                ->addIndexColumn()
                ->addColumn('content', function ($data) {
                    $idHash = "'".$data->permohonan_hash."'";
                    $idLHUhash = "'".$data->tbl_lhu->lhu_hash."'";
                    $idKIPhash = "'".$data->tbl_kip->kip_hash."'";

                    $buttonLHU = '';
                    if($data->tbl_lhu->ttd_2){
                        $buttonLHU = '<button class="btn btn-outline-success btn-sm" onclick="ttdDocumentLHU('.$idLHUhash.', 1)"><i class="bi bi-check2-all"></i> Show TTD LHU</button>';
                    }else{
                        $buttonLHU = '<button class="btn btn-outline-primary btn-sm" onclick="ttdDocumentLHU('.$idLHUhash.')"><i class="bi bi-check2"></i> TTD Document LHU</button>';
                    }

                    return '
                        <div class="card m-0 border-0">
                            <div class="card-body d-flex flex-wrap p-3 align-items-center">
                                <div class="col-md-8 col-sm-12 mb-sm-2">
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
                                    '.$buttonLHU.'
                                </div>
                                <div class="col-md-2 col-sm-5 h5">
                                    <button class="btn btn-outline-primary btn-sm" onclick="ttdDocumentKIP('.$idKIPhash.')"><i class="bi bi-check2"></i> TTD Document KIP</button>
                                </div>
                            </div>
                        </div>
                    ';
                })
                ->rawColumns(['content'])
                ->make(true);
    }
}
