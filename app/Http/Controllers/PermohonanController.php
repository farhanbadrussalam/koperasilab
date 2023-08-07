<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\Layanan_jasa;
use App\Models\jadwal;
use Illuminate\Http\Request;
use Auth;
use DataTables;

class PermohonanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['token'] = generateToken();
        return view('pages.permohonan.index', $data);
    }

    public function getData() {
        $informasi = Permohonan::where('status', '!=', 99);

        if(!Auth::user()->hasRole('Super Admin')){
            $informasi->where('created_by', Auth::user()->id);
        }

        return DataTables::of($informasi)
                ->addIndexColumn()
                ->addColumn('nama_layanan', function($data) {
                    return "
                        <div>".$data->layananjasa->nama_layanan."</div>
                        <div>$data->jenis_layanan</div>
                        <div>Rp. <span class='rupiah'>".$data->tarif."</span></div>
                    ";
                })
                ->addColumn('jadwal', function($data){
                    return "
                        <div class='text-center'>".$data->jadwal->date_mulai."</div>
                        <div class='text-center'>S/D</div>
                        <div class='text-center'>".$data->jadwal->date_selesai."</div>
                    ";
                })
                ->editColumn('status', function($data){
                    return statusFormat("permohonan",$data->status);
                })
                ->addColumn('action', function($data){
                    $user = Auth::user();
                    $btnAction = '<div class="text-center">';
                    $user->hasPermissionTo('Permohonan.edit') && $btnAction .= '<a class="btn btn-warning btn-sm  m-1" href="#"><i class="bi bi-pencil-square"></i></a>';
                    $user->hasPermissionTo('Permohonan.delete') && $btnAction .= '<button class="btn btn-danger btn-sm  m-1" onclick="btnDelete('.$data->id.')"><i class="bi bi-trash3-fill"></i></a>';
                    $btnAction .= '</div>';
                    return $btnAction;
                })
                ->rawColumns(['action','nama_layanan', 'jadwal', 'status'])
                ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data['token'] = generateToken();
        $data['layanan'] = Layanan_jasa::where('status', '!=', '99')->get();
        return view('pages.permohonan.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = $request->validate([
            'layanan_jasa' => 'required',
            'jenis_layanan' => 'required',
            'tarif' => 'required',
            'jadwal' => 'required',
            'noBapeten' => 'required',
            'jenisLimbah' => 'required',
            'radioAktif' => 'required',
            'jumlah' => 'required'
        ]);

        $jadwal_id = explode('|', $request->jadwal)[0];

        $dataJadwal = jadwal::findOrFail($jadwal_id);

        // Mengurangi kuota jadwal
        $dataJadwal->kuota = $dataJadwal->kuota-1;
        $dataJadwal->update();

        $ambilAntrian = Permohonan::where('jadwal_id', $jadwal_id)
                        ->where('status', '!=', '99')
                        ->select('nomor_antrian')
                        ->orderBy('nomor_antrian', 'DESC')
                        ->first();

        if(!$ambilAntrian){
            $ambilAntrian = 1;
        }else{
            $ambilAntrian = (int)$ambilAntrian->nomor_antrian + 1;
        }

        $data = array(
            'layananjasa_id' => $request->layanan_jasa,
            'jadwal_id' => $jadwal_id,
            'jenis_layanan' => explode('|', $request->jenis_layanan)[0],
            'tarif' => $request->tarif,
            'no_bapeten' => $request->noBapeten,
            'jenis_limbah' => $request->jenisLimbah,
            'sumber_radioaktif' => $request->radioAktif,
            'jumlah' => $request->jumlah,
            'dokumen' => '',
            'status' => 1,
            'nomor_antrian' => $ambilAntrian,
            'created_by' => Auth::user()->id
        );

        Permohonan::create($data);

        // $sendNotif = notifikasi(array(
        //     'to_user' => $dataJadwal->petugas_id,
        //     'type' => 'Permohonan'
        // ), Auth::user()->name." baru saja membuat permohonan untuk Pelayanan ".$dataJadwal->layananjasa->nama_layanan." pada tanggal $dataJadwal->date_mulai");

        return redirect()->route('permohonan.index')->with('success', 'Permohonan berhasil di buat');
    }

    /**
     * Display the specified resource.
     */
    public function show(Permohonan $permohonan)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Permohonan $permohonan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Permohonan $permohonan)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permohonan $permohonan)
    {
        //
    }
}
