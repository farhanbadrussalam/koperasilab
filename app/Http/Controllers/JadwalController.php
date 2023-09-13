<?php

namespace App\Http\Controllers;

use App\Models\jadwal;
use App\Models\Layanan_jasa;
use App\Models\User;
use App\Models\tbl_media;
use App\Models\Jadwal_petugas;
use App\Models\Petugas_layanan;

use Illuminate\Http\Request;
use Auth;
use DataTables;

class JadwalController extends Controller
{
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
                    $btnDelete = $user->hasPermissionTo('Penjadwalan.delete') ? '<button class="btn btn-outline-danger btn-sm m-1" onclick="btnDelete('.$idHash.')"><i class="bi bi-trash3-fill"></i></a>' : false;
                    $btnConfirm = false;
                    $btnInfoPetugas = false;
                    $infoBersedia = '';
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


                    return '
                        <div class="card m-0">
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
                                        '.$btnConfirm.'
                                    </div>
                                    <div>
                                        '.$btnInfoPetugas.'
                                    </div>
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
            'kuota' => ['required'],
            'petugas' => ['required']
        ]);

        $layanan_jasa = Layanan_jasa::where('id', decryptor($request->layanan_jasa))->first();
        $petugas = array($layanan_jasa->user_id);
        foreach ($request->petugas as $key => $value) {
            array_push($petugas, (int) decryptor($value));
        }

        // upload dokumen
        $dokumen = $request->file('dokumen');
        $media = '';
        if($dokumen){
            $realname =  pathinfo($dokumen->getClientOriginalName(), PATHINFO_FILENAME);
            $filename = 'dokumen_jadwal_'.md5($realname).'.'.$dokumen->getClientOriginalExtension();
            $path = $dokumen->storeAs('public/dokumen/jadwal', $filename);

            $media = tbl_media::create([
                'file_hash' => $filename,
                'file_ori' => $dokumen->getClientOriginalName(),
                'file_size' => $dokumen->getSize(),
                'file_type' => $dokumen->getClientMimeType(),
                'status' => 1
            ]);
        }

        $dataJadwal = array(
            'layananjasa_id' => decryptor($request->layanan_jasa),
            'jenislayanan' => explode('|', $request->jenis_layanan)[0],
            'tarif' => $request->tarif,
            'date_mulai' => $request->tanggal_mulai,
            'date_selesai' => $request->tanggal_selesai,
            'kuota' => $request->kuota,
            'status' => 1,
            'dokumen' => $media->id,
            'created_by' => Auth::user()->id
        );

        $saveJadwal = jadwal::create($dataJadwal);

        foreach ($petugas as $key => $value) {
            Jadwal_petugas::create([
                'jadwal_id' => $saveJadwal->id,
                'petugas_id' => $value,
                'status' => 1
            ]);

            # Send notifikasi
            $pjContent = $value == $layanan_jasa->user_id ? "dan menjadi Penanggung jawab" : "";
            $sendNotif = notifikasi(array(
                'to_user' => $value,
                'type' => 'jadwal'
            ), "Anda ditugaskan untuk layanan ".$saveJadwal->layananjasa->nama_layanan." ".$pjContent." pada tanggal ".$request->tanggal_mulai);
        }

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
            $data['pegawai'] = Petugas_layanan::where('satuankerja_id', $jadwal->layananjasa->satuankerja_id)->get();
            $data['petugas'] = Jadwal_petugas::with('petugas')->where('jadwal_id', $idJadwal)->get();
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

        $jadwal = Jadwal_petugas::where('jadwal_id', decryptor($request->idJadwal))->where('petugas_id', $user->id)->first();
        $jadwal->status = $request->answer;

        $jadwal->update();

        $msg = $request->answer == 2 ? 'Anda bersedia' : 'Anda tidak bersedia';

        return response()->json(['message' => $msg, 'status' => $request->answer], 200);
    }
}
