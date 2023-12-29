<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permohonan;
use App\Models\jadwal;
use App\Models\User;
use App\Models\Jadwal_petugas;
use App\Models\Petugas_layanan;
use Auth;
use DataTables;

class PenugasanController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $data['token'] = generateToken();
        $data['petugas'] = User::where('satuankerja_id', $user->satuankerja_id)->role('staff')->get();
        return view('pages.penugasan.index', $data);
    }

    public function getWaktuJadwal(){
        $user = Auth::user();
        $jadwal = jadwal::with('petugas','layananjasa','user')->where('status', '!=', 99)->where('created_by', $user->id);

        return DataTables::of($jadwal)
                ->addIndexColumn()
                ->addColumn('content', function($data) use ($user){
                    $idHash = "'".$data->jadwal_hash."'";
                    $dataPetugas = Jadwal_petugas::where('jadwal_id', $data->id)->where('petugas_id', $user->id)->first();

                    $btnAddPetugas = '
                        <a class="btn btn-outline-primary btn-sm mb-2" href="'.route("penugasan.show", $data->jadwal_hash).'">
                            <div> Show Permohonan</div>
                        </a>
                    ';
                    $btnInfoPetugas = false;
                    $infoBersedia = '';


                    return '
                        <div class="card m-0 border-0">
                            <div class="card-body row d-flex p-3 align-items-center">
                                <div class="col-3">
                                    <div class="fw-bold text-wrap">'.$data->layananjasa->nama_layanan.'</div>
                                    <small class="text-body-secondary text-wrap">'.$data->jenislayanan.'</small>
                                </div>
                                <div class="col-4">
                                    <div class=" d-flex p-2 flex-column">
                                        <div>
                                            <div class="fw-bold">Start date</div>
                                            <small class="text-body-secondary">'.convert_date($data->date_mulai).'</small>
                                        </div>
                                        <div>
                                            <div class="fw-bold">End date</div>
                                            <small class="text-body-secondary">'.convert_date($data->date_selesai).'</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-2">
                                    <div class="fw-bold">Price</div>
                                    <div>'.formatCurrency($data->tarif).'</div>
                                </div>
                                <div class="col-1">
                                    '.$infoBersedia.'
                                    <div class="fw-bold">Kuota</div>
                                    <div>'.$data->kuota.'</div>
                                </div>
                                <div class="col-2 text-center">
                                    <div>
                                        '.$btnAddPetugas.'
                                    </div>
                                </div>
                            </div>
                        </div>
                    ';
                })

                ->rawColumns(['content'])
                ->make(true);
    }

    public function addPetugas($id)
    {
        $idJadwal = decryptor($id);
        $jadwal = jadwal::findOrFail($idJadwal);
        $data = array();
        if($jadwal){
            $data['jadwal'] = $jadwal;
            $data['pegawai'] = Petugas_layanan::with('petugas')->where('satuankerja_id', $jadwal->layananjasa->satuankerja_id)->get();
            foreach ($data['pegawai'] as $key => $value) {
                $petugas = User::where('id', $value->petugas->id)->first();
                $value['otorisasi'] = $petugas->getDirectPermissions();
            }
        }
        $data['token'] = generateToken();
        return view('pages.penugasan.tambahPetugas', $data);
    }

    public function showPermohonan($id)
    {
        $idJadwal = decryptor($id);

        $d_jadwal = jadwal::with('layananjasa')->where('id', $idJadwal)->first();

        if($d_jadwal){
            $data['jadwal'] = $d_jadwal;
            $data['pegawai'] = Petugas_layanan::with('petugas')->where('satuankerja_id', $d_jadwal->layananjasa->satuankerja_id)->get();
            foreach ($data['pegawai'] as $key => $value) {
                $petugas = User::where('id', $value->petugas->id)->first();
                $value['otorisasi'] = $petugas->getDirectPermissions();
            }
        }

        $data['token'] = generateToken();

        return view('pages.penugasan.listPermohonan', $data);
    }

    public function dataPermohonan()
    {
        $idJadwal = decryptor(request('idJadwal'));

        $d_permohonan = Permohonan::with('user')
            ->where('jadwal_id', $idJadwal)
            ->where('status', '!=', 99)
            ->where('flag', 5)
            ->orderBy('nomor_antrian');

        return DataTables::of($d_permohonan)
            ->addIndexColumn()
            ->addColumn('customer', function($data) {
                return $data->user->name;
            })
            ->addColumn('action', function($data){
                $idHash = "'".$data->permohonan_hash."'";
                $idJadwal = "'".encryptor($data->jadwal_id)."'";

                $id = decryptor($data->permohonan_hash);

                $countPetugas = Jadwal_petugas::where('permohonan_id', $id)->count();
                $countBersedia = Jadwal_petugas::where('permohonan_id', $id)->where('status', 2)->count();

                return '
                    <button class="btn btn-outline-primary btn-sm mb-2" onclick="modalConfirm('.$idHash.')" title="Rincian">
                        <i class="bi bi-info-circle"></i></button>
                    <button class="btn btn-outline-warning btn-sm mb-2" onclick="btnDetailPayment('.$idHash.')" title="Invoice">
                        <i class="bi bi-credit-card-2-back-fill"></i></button>
                    <button role="button" class="btn btn-outline-info btn-sm mb-2" onclick="showPetugas('.$idHash.')">
                        <div><i class="bi bi-people-fill"></i> Petugas</div>
                        <div class="badge text-bg-secondary">'.$countBersedia.' / '.$countPetugas.'</div>
                    </button>
                ';
            })

            ->rawColumns(['customer','action'])
            ->make(true);
    }
}
