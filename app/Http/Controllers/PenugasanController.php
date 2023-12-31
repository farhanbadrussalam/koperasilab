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

    public function getJadwalPermohonan(){
        $user = Auth::user();
        $jadwalP = Permohonan::with('jadwal', 'layananjasa', 'user')
            ->where('flag', 5)
            ->where('status', '!=', '99')
            ->whereHas('layananjasa', function($query) use ($user) {
                $query->where('satuankerja_id', $user->satuankerja_id);
            })
            ->whereHas('petugas', function($query) use ($user) {
                $query->where('petugas_id', $user->id);
            })
            ->orderBy('nomor_antrian', 'DESC');

        return DataTables::of($jadwalP)
        ->addIndexColumn()
        ->addColumn('content', function($data) use ($user) {
            $btn_action = '';
            $petugas = Jadwal_petugas::where('petugas_id', $user->id)->where('permohonan_id', decryptor($data->permohonan_hash))->first();
            $co_antrian = '
                <div id="reason" class="rounded p-2 col-12 mt-2 bg-sm-secondary d-block">
                    <small class="text-success-emphasis"><b>No Antrian:</b> '.($data->nomor_antrian).'</small><br>
                </div>
            ';
            if($petugas){
                $idHash = "'".$petugas->jadwalpetugas_hash."'";
                switch ($petugas->status) {
                    case 1:
                        $btn_action = '<button class="btn btn-outline-success btn-sm m-1" onclick="modalConfirm('.$idHash.')"><i class="bi bi-check-circle"></i> Confirm</button>';
                        break;
                    case 2:
                        $btn_action = '<button class="btn btn-outline-info btn-sm m-1" onclick="modalConfirm('.$idHash.')"><i class="bi bi-check"></i> Bersedia</button>';
                        break;
                    case 9:
                        $btn_action = '<button class="btn btn-outline-danger btn-sm m-1" onclick="modalConfirm('.$idHash.')"><i class="bi bi-x"></i> Menolak</button>';
                        break;
                }
            }
            return '
            <div class="card m-0 border-0">
                <div class="card-body d-flex flex-wrap p-3 align-items-center">
                    <div class="col-md-5 col-sm-12 mb-sm-2">
                        <span class="fw-bold">'.$data->layananjasa->nama_layanan.'</span>
                        <div class="text-body-secondary text-start">
                            <div>
                                <small><b>Start date</b> : '.convert_date($data->jadwal->date_mulai, 1).'</small>
                                <small><b>End date</b> : '.convert_date($data->jadwal->date_selesai, 1).'</small>
                            </div>
                            <small><b>Customer</b> : '.$data->user->name.'</small>
                        </div>
                    </div>
                    <div class="col-md-5 col-sm-5 h5">
                        <span class="badge text-bg-secondary">'.$data->jenis_layanan.'</span>
                    </div>
                    <div class="col-md-2 col-sm-2 text-end">
                        '.$btn_action.'
                    </div>
                    '.$co_antrian.'
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

        $d_jadwal = jadwal::with('layananjasa', 'layananjasa.user')->where('id', $idJadwal)->first();

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
