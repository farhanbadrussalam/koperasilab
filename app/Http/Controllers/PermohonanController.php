<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\Layanan_jasa;
use App\Models\jadwal;
use App\Models\tbl_media;
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
        $user = Auth::user();

        if(!$user->hasRole('Super Admin')){
            if($user->hasPermissionTo('Permohonan.confirm')){
                $informasi->whereHas('jadwal', function($query) use ($user){
                    $query->where('petugas_id', $user->id);
                });
            }else{
                $informasi->where('created_by', $user->id);
            }
        }

        return DataTables::of($informasi)
                ->addIndexColumn()
                ->addColumn('nama_layanan', function($data) {
                    return "
                        <div class='fw-bolder'>".$data->user->name."</div>
                        <div>".$data->layananjasa->nama_layanan."</div>
                        <div>$data->jenis_layanan</div>
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
                ->editColumn('nomor_antrian', function($data){
                    return "<span class='badge text-bg-light fs-3 border shadow-sm'>$data->nomor_antrian</span>";
                })
                ->addColumn('action', function($data){
                    $user = Auth::user();

                    $btnView = '<button class="btn btn-info btn-sm m-1" onclick="modalConfirm('.$data->id.')" title="View"><i class="bi bi-eye-fill"></i></button>';
                    $btnDelete = '<button class="btn btn-danger btn-sm  m-1" onclick="btnDelete('.$data->id.')" title="Batalkan"><i class="bi bi-trash3-fill"></i></a>';
                    $btnEdit = '<a class="btn btn-warning btn-sm  m-1" href="'.route("permohonan.edit", $data->id).'" title="Edit"><i class="bi bi-pencil-square"></i></a>';
                    $btnNote = '<button class="btn btn-secondary btn-sm m-1" onclick="modalNote('.$data->id.')" title="note"><i class="bi bi-chat-square-dots-fill"></i></button>';
                    $btnConfirm = '<button class="btn btn-success btn-sm m-1" onclick="modalConfirm('.$data->id.')">Confirm</button>';

                    $btnAction = '<div class="text-center">';
                    if($data->status == 1){
                        if($user->hasPermissionTo('Permohonan.confirm')){
                            $btnAction .= $btnConfirm;
                        }else{
                            $btnAction .= $btnView;
                        }
                        $user->hasPermissionTo('Permohonan.delete') && $btnAction .= $btnDelete;
                    }else if($data->status == 2) {
                        if($user->hasPermissionTo('Permohonan.confirm')) {
                            $btnAction .= $btnView;
                        }else{
                            $btnAction .= $btnNote;
                            $btnAction .= $btnView;
                        }
                    }else if($data->status == 9){
                        $btnAction .= $btnNote;
                        $user->hasPermissionTo('Permohonan.confirm') && $btnAction .= $btnView;
                        $user->hasPermissionTo('Permohonan.edit') && $btnAction .= $btnEdit;
                        $user->hasPermissionTo('Permohonan.delete') && $btnAction .= $btnDelete;
                    }
                    $btnAction .= '</div>';
                    return $btnAction;
                })
                ->rawColumns(['action','nama_layanan', 'jadwal', 'status', 'nomor_antrian'])
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

    public function getDTListLayanan(){
        $dataJadwal = jadwal::with('layananjasa')->where('status', 2)->where('kuota', '>', 0);

        return DataTables::of($dataJadwal)
                ->addColumn('content', function($data){
                    return '
                    <div class="card m-0 border-0 cursoron card-hover">
                        <div class="ribbon-wrapper">
                            <div class="ribbon bg-primary" title="Kuota">
                                '.$data->kuota.'
                            </div>
                        </div>
                        <div class="card-body d-flex p-3 align-items-center">
                            <div class="col-6">
                                <h5>'.$data->layananjasa->nama_layanan.'</h5>
                                <div class=" d-flex py-1 flex-column">
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
                            <div class="h5 col-3"><span class="badge bg-secondary">'.$data->jenislayanan.'</span></div>
                            <div class="h3 fw-bolder">
                                '.formatCurrency($data->tarif).'
                            </div>
                        </div>
                    </div>
                    ';
                })
                ->rawColumns(['content'])
                ->make(true);
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

        // upload dokumen pendukung
        $documents = $request->file('dokumen');
        $dokumen_pendukung = "";
        if($documents){
            $arrMedia = array();
            foreach ($documents as $key => $document) {
                $realname =  pathinfo($document->getClientOriginalName(), PATHINFO_FILENAME);
                $filename = 'permohonan_'.md5($realname).'.'.$document->getClientOriginalExtension();
                $path = $document->storeAs('public/dokumen/permohonan', $filename);

                $media = tbl_media::create([
                    'file_hash' => $filename,
                    'file_ori' => $document->getClientOriginalName(),
                    'file_size' => $document->getSize(),
                    'file_type' => $document->getClientMimeType(),
                    'status' => 1
                ]);
                array_push($arrMedia, $media->id);
            }

            $dokumen_pendukung = json_encode($arrMedia);
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
            'dokumen' => $dokumen_pendukung,
            'status' => 1,
            'nomor_antrian' => $ambilAntrian,
            'created_by' => Auth::user()->id
        );

        Permohonan::create($data);

        $sendNotif = notifikasi(array(
            'to_user' => $dataJadwal->petugas_id,
            'type' => 'Permohonan'
        ), Auth::user()->name." baru saja membuat permohonan untuk Pelayanan ".$dataJadwal->layananjasa->nama_layanan." pada tanggal $dataJadwal->date_mulai");

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
        $data['token'] = generateToken();
        $data['permohonan'] = $permohonan;
        $dokumen = json_decode($data['permohonan']->dokumen);
        $media = tbl_media::select('id','file_hash','file_ori','file_size','file_type')
                            ->whereIn('id', $dokumen)
                            ->get();
        $data['permohonan']->media = $media;
        return view('pages.permohonan.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Permohonan $permohonan)
    {
        $validator = $request->validate([
            'noBapeten' => 'required',
            'jenisLimbah' => 'required',
            'radioAktif' => 'required',
            'jumlah' => 'required'
        ]);
        $permohonan->no_bapeten = $request->noBapeten;
        $permohonan->jenis_limbah = $request->jenisLimbah;
        $permohonan->sumber_radioaktif = $request->radioAktif;
        $permohonan->jumlah = $request->jumlah;
        $permohonan->status = 1;
        $permohonan->note = null;
        $permohonan->surat_terbitan = null;

        // Update file pendukung
        $documents = $request->file('dokumen');
        if($documents){
            $arrMedia = array();
            foreach ($documents as $key => $document) {
                $realname =  pathinfo($document->getClientOriginalName(), PATHINFO_FILENAME);
                $filename = 'permohonan_'.md5($realname).'.'.$document->getClientOriginalExtension();
                $path = $document->storeAs('public/dokumen/permohonan', $filename);

                $media = tbl_media::create([
                    'file_hash' => $filename,
                    'file_ori' => $document->getClientOriginalName(),
                    'file_size' => $document->getSize(),
                    'file_type' => $document->getClientMimeType(),
                    'status' => 1
                ]);
                array_push($arrMedia, $media->id);
            }
            $permohonan->dokumen = array_merge(json_decode($permohonan->dokumen), $arrMedia);
        }
        $permohonan->update();

        return redirect()->route('permohonan.index')->with('success', 'Berhasil di update');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permohonan $permohonan)
    {
        //
    }
}
