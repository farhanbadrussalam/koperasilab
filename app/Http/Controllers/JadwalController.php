<?php

namespace App\Http\Controllers;

use App\Models\jadwal;
use App\Models\Layanan_jasa;
use App\Models\User;
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

        if(!Auth::user()->hasRole('Super Admin')){
            $jadwal->where('created_by', Auth::user()->id);
        }

        return DataTables::of($jadwal)
                ->addIndexColumn()
                ->addColumn('nama_layanan', function($data) {
                    return "
                        <div>".$data->layananjasa->nama_layanan."</div>
                        <div>$data->jenislayanan</div>
                        <div>".formatCurrency($data->tarif)."</div>
                    ";
                })
                ->addColumn('action', function($data){
                    return '
                        <a class="btn btn-warning btn-sm" href="'.route("jadwal.edit", $data->id).'">Edit</a>
                        <button class="btn btn-danger btn-sm" onclick="btnDelete('.$data->id.')">Delete</a>
                    ';
                })
                ->editColumn('petugas_id', function($data){
                    $status = $this->getStatus($data->status);
                    $petugas = $data->petugas ? $data->petugas->name : '';
                    return "
                        <div>".$petugas."</div>
                        <div>".$status."</div>
                    ";
                })
                ->rawColumns(['action', 'nama_layanan', 'petugas_id'])
                ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        $data['layanan'] = Layanan_jasa::where('satuankerja_id', Auth::user()->satuankerja_id)
                                ->where('status', 1)
                                ->get();
        $data['petugas'] = User::where('satuankerja_id', $user->satuankerja_id)->role('staff')->get();
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
            'petugas' => ['required'],
            'dokumen' => ['mimes:pdf,doc,docx']
        ]);

        // upload dokumen
        $dokumen = $request->file('dokumen');
        $filename = '';
        if($dokumen){
            $filename = 'dokumen_jadwal_'.$dokumen->getClientOriginalName().'.'.$dokumen->getClientOriginalExtension();
            $path = $dokumen->storeAs('public/dokumen/jadwal', $filename);
        }

        $dataJadwal = array(
            'layananjasa_id' => $request->layanan_jasa,
            'jenislayanan' => explode('|', $request->jenis_layanan)[0],
            'tarif' => $request->tarif,
            'date_mulai' => $request->tanggal_mulai,
            'date_selesai' => $request->tanggal_selesai,
            'kuota' => $request->kuota,
            'status' => 1,
            'petugas_id' => $request->petugas,
            'dokumen' => $filename,
            'created_by' => Auth::user()->id
        );

        $saveJadwal = jadwal::create($dataJadwal);

        $sendNotif = notifikasi(array(
            'to_user' => $request->petugas,
            'type' => 'jadwal'
        ), "Anda ditugaskan untuk layanan ".$saveJadwal->layananjasa->nama_layanan." pada tanggal ".$request->tanggal_mulai);

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
    public function edit(jadwal $jadwal)
    {
        // $data['layanan'] = $jadwal->layananjasa;
        $data['jadwal'] = $jadwal;
        $data['petugas'] = $jadwal->petugas;
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
            case 3:
                $hasil = '<span class="badge text-bg-danger">Tidak bersedia</span>';
                break;
            default:
                $hasil = '<span class="badge text-bg-danger">dibatalkan</span>';
                break;
        };

        return $hasil;
    }
}