<?php

namespace App\Http\Controllers;

use App\Models\jadwal;
use App\Models\Layanan_jasa;
use App\Models\User;
use App\Models\tbl_media;
use App\Models\Jadwal_petugas;
use App\Models\Petugas_layanan;

use App\Http\Controllers\MediaController;

use Illuminate\Http\Request;
use Auth;
use DataTables;

class JadwalController extends Controller
{
    public function __construct() {
        $this->mediaController = resolve(MediaController::class);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['token'] = generateToken();
        return view('pages.jadwal.index', $data);
    }

    public function getData() {
        $jadwal = jadwal::with('petugas','layananjasa','user')->where('status', '!=', 99);
        $user = Auth::user();

        if(!$user->hasRole('Super Admin')){
            if($user->hasPermissionTo('Penjadwalan.confirm')){
                $jadwal->whereHas('petugas', function($query) use ($user){
                    $query->where('petugas_id', $user->id);
                });
            }else{
                $jadwal->where('created_by', Auth::user()->id);
            }
        }

        return DataTables::of($jadwal)
                ->addIndexColumn()
                ->addColumn('content', function($data) use ($user){
                    $idHash = "'".$data->jadwal_hash."'";
                    $countPetugas = Jadwal_petugas::where('jadwal_id', $data->id)->count();
                    $countBersedia = Jadwal_petugas::where('jadwal_id', $data->id)->where('status', 2)->count();
                    $dataPetugas = Jadwal_petugas::where('jadwal_id', $data->id)->where('petugas_id', $user->id)->first();

                    $btnEdit = $user->hasPermissionTo('Penjadwalan.edit') ? '<a class="btn btn-outline-warning btn-sm m-1" href="'.route("jadwal.edit", $data->jadwal_hash).'"><i class="bi bi-pencil-square"></i></a>' : false;
                    $btnDelete = $user->hasPermissionTo('Penjadwalan.delete') ? '<button class="btn btn-outline-danger btn-sm m-1" onclick="btnDelete('.$idHash.')"><i class="bi bi-trash3-fill"></i></button>' : false;
                    $btnConfirm = false;
                    $btnInfoPetugas = false;
                    $infoBersedia = '';

                    $btnShowPermohonan = '';
                    if($user->hasPermissionTo('Penjadwalan.confirm')){
                        if($dataPetugas->status == 1) {
                            $btnConfirm .= '<button class="btn btn-outline-success btn-sm m-1" onclick="modalConfirm('.$idHash.')"><i class="bi bi-check-circle"></i> Confirm</button>';
                        } else if($dataPetugas->status == 2){
                            $btnConfirm .= '<button class="btn btn-outline-info btn-sm m-1" onclick="modalConfirm('.$idHash.')"><i class="bi bi-check"></i> Bersedia</button>';
                        } else if($dataPetugas->status == 9){
                            $btnConfirm .= '<button class="btn btn-outline-danger btn-sm m-1" onclick="modalConfirm('.$idHash.')"><i class="bi bi-x"></i> Menolak</button>';
                        }
                    }else{
                        $btnInfoPetugas = '
                            <button role="button" class="btn btn-outline-info btn-sm mb-2" onclick="showPetugas('.$idHash.')">
                                <div><i class="bi bi-people-fill"></i> Petugas</div>
                                <div class="badge text-bg-secondary">'.$countBersedia.' / '.$countPetugas.'</div>
                            </button>
                            ';
                    }

                    if($user->hasRole('manager')){
                        $btnShowPermohonan = '
                            <a class="btn btn-outline-primary btn-sm mb-2" href="'.route("penugasan.show", $data->jadwal_hash).'">
                                <div> Show Permohonan</div>
                            </a>
                        ';
                    }

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
                                        '.$btnEdit.'
                                        '.$btnDelete.'
                                        '.$btnShowPermohonan.'
                                        '.$btnConfirm.'
                                    </div>
                                </div>
                            </div>
                        </div>
                    ';
                })
                ->filter(function ($query) {
                    if(request()->has('search') && request('search')){
                        $query->whereHas('layananjasa', function($layanan){
                            $layanan->where('nama_layanan', 'like', "%" . request('search') . "%");
                        });
                    }

                    if(request()->has('status') && request('status')){
                        $query->whereHas('petugas', function($petugas){
                            $status = decryptor(request('status'));
                            $petugas->where('status', $status);
                        });
                    }

                    if(request()->has('priceMin') && request('priceMin')){
                        $priceMin = (int) unmask(request('priceMin'));
                        $query->where('tarif', '>=', $priceMin);
                    }

                    if(request()->has('priceMax') && request('priceMax')){
                        $priceMax = (int) unmask(request('priceMax'));
                        $query->where('tarif', '<=', $priceMax);
                    }

                    if(request()->has('startDate') && request('startDate')){
                        $split = explode(' to ', request('startDate'));
                        $start = $split[0];
                        $end = $split[1];

                        $query->whereDate('date_mulai', '>=', $start)->whereDate('date_mulai', '<=', $end);
                    }
                })
                ->rawColumns(['content'])
                ->make(true);
    }

    public function getPetugasDT(Request $request){
        $idJadwal = decryptor($request->idJadwal);
        $jadwal = jadwal::findOrFail($idJadwal);
        $dataPetugas = Jadwal_petugas::with('petugas')->where('jadwal_id', $idJadwal);

        return DataTables::of($dataPetugas)
                ->addColumn('content', function($data) use ($jadwal) {
                    $idHash = "'".$data->jadwalpetugas_hash."'";
                    $btnOtorisasi = "";
                    foreach ($data->otorisasi as $key => $value) {
                        $btnOtorisasi .= '<button class="btn btn-outline-dark btn-sm m-1" type="button">'.stringSplit($value->name, "Otorisasi-").'</button>';
                    }

                    $btnAction = '
                    <div class="dropdown">
                        <div class="more-option d-flex align-items-center justify-content-center mx-0 mx-md-4" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-three-dots-vertical"></i>
                        </div>
                        <ul class="dropdown-menu shadow-sm px-2">
                    ';
                    if($data->status == 1 || $data->status == 9){
                        $btnAction .= '
                            <li class="my-1 cursoron">
                                <a class="dropdown-item dropdown-item-lab subbody" onclick="changePetugas('.$idHash.')">
                                    <i class="bi bi-arrow-repeat"></i>&nbsp;Change
                                </a>
                            </li>
                        ';
                    }
                    $btnAction .= '
                            <li class="my-1 cursoron">
                                <a class="dropdown-item dropdown-item-lab subbody text-danger" onclick="deletePetugas('.$idHash.')">
                                    <i class="bi bi-trash"></i>&nbsp;Delete
                                </a>
                            </li>
                        </ul>
                    </div>
                    ';
                    return $data->petugas->id == $jadwal->layananjasa->user_id ? '' : '
                    <div class="card m-0 border-0">
                        <div class="card-body d-flex p-2">
                            <div class="flex-grow-1 d-flex my-auto">
                                <div>
                                    <img src="'.$data->avatar.'" alt="Avatar" onerror="this.src=`'.asset("assets/img/default-avatar.jpg").'`" style="width: 3em;" class="img-circle border shadow-sm">
                                </div>
                                <div class="px-3 my-auto">
                                    <div class="lh-1">'.$data->petugas->name.'</div>
                                    <small class="text-secondary">'.$data->petugas->email.'</small>
                                </div>
                            </div>
                            <div class="p-2 m-auto">
                                <div class="d-flex flex-wrap justify-content-end">
                                    '.$btnOtorisasi.'
                                </div>
                            </div>
                            <div class="p-2 m-auto">
                                '.statusFormat('jadwal', $data->status).'
                            </div>
                            <div class="p-2 m-auto">
                                '.$btnAction.'
                            </div>
                        </div>
                    </div>
                    ';
                })
                ->rawColumns(['content'])
                ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        $data['layanan'] = Layanan_jasa::with('user','satuanKerja')->where('satuankerja_id', Auth::user()->satuankerja_id)
                                ->where('status', 1)
                                ->get();
        $data['petugas'] = User::where('satuankerja_id', $user->satuankerja_id)->role('staff')->get();
        $data['token'] = generateToken();
        return view('pages.jadwal.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = $request->validate([
            'layanan_jasa' => ['required'],
            'jenis_layanan' => ['required'],
            'tanggal_mulai' => ['required'],
            'tanggal_selesai' => ['required'],
            'kuota' => ['required']
        ]);

        $idLayananjasa = decryptor($request->layanan_jasa);

        $dataJadwal = array(
            'layananjasa_id' => $idLayananjasa,
            'jenislayanan' => explode('|', $request->jenis_layanan)[0],
            'tarif' => $request->tarif,
            'date_mulai' => $request->tanggal_mulai,
            'date_selesai' => $request->tanggal_selesai,
            'kuota' => $request->kuota,
            'status' => 1,
            'created_by' => Auth::user()->id
        );

        $saveJadwal = jadwal::create($dataJadwal);

        // menambahkan petugas
        $layanan = Layanan_jasa::where('id', $idLayananjasa)->first();
        $jadwalPetugas = Jadwal_petugas::create([
            'jadwal_id' => $saveJadwal->id,
            'petugas_id' => $layanan->user_id,
            'status' => 1
        ]);

        # Send notifikasi
        // $pjContent = $value == $layanan_jasa->user_id ? "dan menjadi Penanggung jawab" : "";
        $sendNotif = notifikasi(array(
            'to_user' => $layanan->user_id,
            'type' => 'jadwal'
        ), "Anda ditugaskan untuk layanan ".$layanan->nama_layanan." pada tanggal ".$request->tanggal_mulai);

        return redirect()->route('jadwal.index')->with('success', 'Berhasil di tambah');
    }

    /**
     * Display the specified resource.
     */
    public function show(jadwal $jadwal)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
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
        return view('pages.jadwal.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, jadwal $jadwal)
    {
        $validator = $request->validate([
            'layanan_jasa' => ['required'],
            'jenis_layanan' => ['required'],
            'tanggal_mulai' => ['required'],
            'tanggal_selesai' => ['required'],
            'kuota' => ['required']
        ]);

        $jadwal->kuota = $request->kuota;
        $jadwal->date_mulai = $request->tanggal_mulai;
        $jadwal->date_selesai = $request->tanggal_selesai;

        $jadwal->update();

        return redirect()->route('jadwal.index')->with('success', 'Berhasil di update');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(jadwal $jadwal)
    {
        //
    }

    private function getStatus($status){
        $hasil = '';
        switch ($status) {
            case 0:
                $hasil = '<span class="badge text-bg-secondary">Belum ditugaskan</span>';
                break;
            case 1:
                $hasil = '<span class="badge text-bg-info">Diajukan</span>';
                break;
            case 2:
                $hasil = '<span class="badge text-bg-success">Bersedia</span>';
                break;
            case 4:
                $hasil = '<span class="badge text-bg-danger">Tidak bersedia</span>';
                break;
            default:
                $hasil = '<span class="badge text-bg-danger">dibatalkan</span>';
                break;
        };

        return $hasil;
    }

    public function confirm(Request $request){
        $validator = $request->validate([
            'idJadwal' => ['required'],
            'answer' => ['required']
        ]);

        $user = Auth::user();
        $idJadwal = decryptor($request->idJadwal);

        $jadwal = Jadwal_petugas::where('jadwal_id', $idJadwal)->where('petugas_id', $user->id)->first();
        $jadwal->status = $request->answer;

        $jadwal->update();

        // cek status petugas
        $status = Jadwal_petugas::where('jadwal_id', $idJadwal)->whereIn('status', [1, 9])->count();
        if($status == 0){
            $dataJadwal = jadwal::where('id', $idJadwal)->first();
            $dataJadwal->status = 2;
            $dataJadwal->update();
        }

        $msg = $request->answer == 2 ? 'Anda bersedia' : 'Anda tidak bersedia';

        return response()->json(['message' => $msg, 'status' => $request->answer], 200);
    }
}
