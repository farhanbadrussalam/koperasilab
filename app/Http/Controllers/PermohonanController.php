<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\Detail_permohonan;
use App\Models\Layanan_jasa;
use App\Models\jadwal;
use App\Models\tbl_media;
use Illuminate\Http\Request;

use App\Http\Controllers\MediaController;
use App\Http\Controllers\DetailPermohonanController;

use Auth;
use DataTables;

class PermohonanController extends Controller
{
    public function __construct() {
        $this->mediaController = resolve(MediaController::class);
        $this->detail = resolve(DetailPermohonanController::class);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['token'] = generateToken();
        return view('pages.permohonan.index', $data);
    }

    public function getData(){
        $user = Auth::user();
        $status = 0;
        if(request()->has('status') && request('status')){
            $status = request('status');
        }
        $informasi = Permohonan::with(['layananjasa', 'jadwal'])
                        ->where('status', '!=', 99)
                        ->where('created_by', $user->id)
                        ->where('status', $status);

        return DataTables::of($informasi)
                ->addIndexColumn()
                ->addColumn('content', function($data) {
                    $idHash = "'".$data->permohonan_hash."'";
                    $co_rebbon = $data->status == 2 ? '
                    <div class="ribbon-wrapper">
                        <div class="ribbon bg-primary" title="Kuota">
                            '.$data->tag.'
                        </div>
                    </div>
                    ' : '';

                    $co_reason = $data->status == 9 ? '
                        <div id="reason" class="rounded p-2 col-12 mt-2 bg-sm-secondary d-block">
                            <small class="text-danger-emphasis"><b>Reason:</b> '.($data->progress ? $data->progress->note : "").'</small>
                        </div>
                    ' : '';

                    $btn_action = '';

                    if($data->status == 1 || $data->status == 9){
                        $btn_action .= '
                            <li class="my-1 cursoron">
                                <a class="dropdown-item dropdown-item-lab subbody text-warning" href="'.route("permohonan.edit", $data->permohonan_hash).'">
                                    <i class="bi bi-pencil-square"></i>&nbsp;Update
                                </a>
                            </li>
                        ';
                    }

                    $btn_action .= '
                        <li class="my-1 cursoron">
                            <a class="dropdown-item dropdown-item-lab subbody text-success" onclick="modalConfirm('.$idHash.')">
                                <i class="bi bi-info-circle"></i>&nbsp;Rincian
                            </a>
                        </li>
                    ';

                    $btn_action .= $data->status == 1 || $data->status == 9 ? '
                        <li class="my-1 cursoron">
                            <a class="dropdown-item dropdown-item-lab subbody text-danger" onclick="btnDelete('.$idHash.')">
                                <i class="bi bi-trash"></i>&nbsp;Delete
                            </a>
                        </li>
                    ' : '';

                    return '
                    <div class="card m-0 border-0">
                        '.  $co_rebbon .'
                        <div class="card-body d-flex flex-wrap p-3 align-items-center">
                            <div class="col-md-6 col-sm-12 mb-sm-2">
                                <span class="fw-bold">'.$data->layananjasa->nama_layanan.'</span>
                                <div class="text-body-secondary text-start">
                                    <div>
                                        <small><b>Start date</b> : '.convert_date($data->jadwal->date_mulai, 1).'</small>
                                        <small><b>End date</b> : '.convert_date($data->jadwal->date_selesai, 1).'</small>
                                    </div>
                                    <small><b>Created</b> : '.convert_date($data->created_at, 1).'</small>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-5 h5">
                                <span class="badge text-bg-secondary">'.$data->jenis_layanan.'</span>
                            </div>
                            <div class="col-md-2 col-sm-5 h5">
                                <span class="badge text-bg-info">Antrian '.$data->nomor_antrian.'</span>
                            </div>
                            <div class="col-md-2 col-sm-2">
                                <div class="dropdown">
                                    <div class="more-option d-flex align-items-center justify-content-center mx-0 mx-md-4" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </div>
                                    <ul class="dropdown-menu shadow-sm px-2">
                                        '.$btn_action.'
                                    </ul>
                                </div>
                            </div>
                            '. $co_reason .'
                        </div>
                    </div>
                    ';
                })
                ->rawColumns(['content'])
                ->make(true);
    }

    // public function getData() {
    //     $informasi = Permohonan::where('status', '!=', 99);
    //     $user = Auth::user();

    //     if(!$user->hasRole('Super Admin')){
    //         if($user->hasPermissionTo('Permohonan.confirm')){
    //             $informasi->whereHas('jadwal', function($query) use ($user){
    //                 $query->where('petugas_id', $user->id);
    //             });
    //         }else{
    //             $informasi->where('created_by', $user->id);
    //         }
    //     }

    //     return DataTables::of($informasi)
    //             ->addIndexColumn()
    //             ->addColumn('nama_layanan', function($data) {
    //                 return "
    //                     <div class='fw-bolder'>".$data->user->name."</div>
    //                     <div>".$data->layananjasa->nama_layanan."</div>
    //                     <div>$data->jenis_layanan</div>
    //                 ";
    //             })
    //             ->addColumn('jadwal', function($data){
    //                 return "
    //                     <div class='text-center'>".$data->jadwal->date_mulai."</div>
    //                     <div class='text-center'>S/D</div>
    //                     <div class='text-center'>".$data->jadwal->date_selesai."</div>
    //                 ";
    //             })
    //             ->editColumn('status', function($data){
    //                 return statusFormat("permohonan",$data->status);
    //             })
    //             ->editColumn('nomor_antrian', function($data){
    //                 return "<span class='badge text-bg-light fs-3 border shadow-sm'>$data->nomor_antrian</span>";
    //             })
    //             ->addColumn('action', function($data){
    //                 $user = Auth::user();

    //                 $btnView = '<button class="btn btn-info btn-sm m-1" onclick="modalConfirm('.$data->id.')" title="View"><i class="bi bi-eye-fill"></i></button>';
    //                 $btnDelete = '<button class="btn btn-danger btn-sm  m-1" onclick="btnDelete('.$data->id.')" title="Batalkan"><i class="bi bi-trash3-fill"></i></a>';
    //                 $btnEdit = '<a class="btn btn-warning btn-sm  m-1" href="'.route("permohonan.edit", $data->id).'" title="Edit"><i class="bi bi-pencil-square"></i></a>';
    //                 $btnNote = '<button class="btn btn-secondary btn-sm m-1" onclick="modalNote('.$data->id.')" title="note"><i class="bi bi-chat-square-dots-fill"></i></button>';
    //                 $btnConfirm = '<button class="btn btn-success btn-sm m-1" onclick="modalConfirm('.$data->id.')">Confirm</button>';

    //                 $btnAction = '<div class="text-center">';
    //                 if($data->status == 1){
    //                     if($user->hasPermissionTo('Permohonan.confirm')){
    //                         $btnAction .= $btnConfirm;
    //                     }else{
    //                         $btnAction .= $btnView;
    //                     }
    //                     $user->hasPermissionTo('Permohonan.delete') && $btnAction .= $btnDelete;
    //                 }else if($data->status == 2) {
    //                     if($user->hasPermissionTo('Permohonan.confirm')) {
    //                         $btnAction .= $btnView;
    //                     }else{
    //                         $btnAction .= $btnNote;
    //                         $btnAction .= $btnView;
    //                     }
    //                 }else if($data->status == 9){
    //                     $btnAction .= $btnNote;
    //                     $user->hasPermissionTo('Permohonan.confirm') && $btnAction .= $btnView;
    //                     $user->hasPermissionTo('Permohonan.edit') && $btnAction .= $btnEdit;
    //                     $user->hasPermissionTo('Permohonan.delete') && $btnAction .= $btnDelete;
    //                 }
    //                 $btnAction .= '</div>';
    //                 return $btnAction;
    //             })
    //             ->rawColumns(['action','nama_layanan', 'jadwal', 'status', 'nomor_antrian'])
    //             ->make(true);
    // }

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
        $dataJadwal = jadwal::with('layananjasa')
                        ->where('status', 2)
                        ->where('kuota', '>', 0)
                        ->orderBy('date_mulai', 'DESC');

        return DataTables::of($dataJadwal)
                ->addColumn('content', function($data){
                    return '
                    <div class="card m-0 border-0 card-hover">
                        <div class="ribbon-wrapper">
                            <div class="ribbon bg-primary" title="Kuota">
                                '.$data->kuota.'
                            </div>
                        </div>
                        <div class="card-body d-flex flex-wrap p-3 align-items-center">
                            <div class="col-md-5 col-sm-6">
                                <span class="fw-bold">'.$data->layananjasa->nama_layanan.'</span>
                                <div>
                                    <span class="badge bg-secondary">'.$data->jenislayanan.'</span>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <div class="d-flex py-1 flex-column">
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
                            <div class="col-md-3 col-sm-12 text-sm-center d-flex flex-column">
                                <span class="h4 fw-bolder ">'.formatCurrency($data->tarif).'</span>
                                <a class="btn btn-sm btn-outline-success mt-2" href="'.url('permohonan/create/layanan/'.$data->jadwal_hash).'">Pilih layanan</a>
                            </div>
                        </div>
                    </div>
                    ';
                })
                ->rawColumns(['content'])
                ->make(true);
    }

    public function pilihLayanan($idJadwal)
    {
        $idJadwalHash = isset($idJadwal) ? decryptor($idJadwal) : null;
        if($idJadwalHash){
            $data['token'] = generateToken();
            $data['jadwal'] = jadwal::with('layananjasa')->where('id', $idJadwalHash)->first();
            return view('pages.permohonan.formCreate', $data);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = $request->validate([
            'jadwal_id' => 'required',
            'noBapeten' => 'required',
            'jenisLimbah' => 'required',
            'radioAktif' => 'required',
            'jumlah' => 'required'
        ]);

        $jadwal_id = decryptor($request->jadwal_id);

        $dataJadwal = jadwal::with('petugas', 'layananjasa')->where('id', $jadwal_id)->first();

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
                $idMedia = $this->mediaController->upload($document, 'permohonan');
                array_push($arrMedia, $idMedia);
            }

            $dokumen_pendukung = json_encode($arrMedia);
        }

        $data = array(
            'layananjasa_id' => $dataJadwal->layananjasa_id,
            'jadwal_id' => $jadwal_id,
            'jenis_layanan' => $dataJadwal->jenislayanan,
            'tarif' => $dataJadwal->tarif,
            'no_bapeten' => $request->noBapeten,
            'jenis_limbah' => $request->jenisLimbah,
            'sumber_radioaktif' => $request->radioAktif,
            'jumlah' => $request->jumlah,
            'dokumen' => $dokumen_pendukung,
            'status' => 1,
            'flag' => 1,
            'tag' => 'pengajuan',
            'nomor_antrian' => $ambilAntrian,
            'created_by' => Auth::user()->id
        );

        $createPermohonan = Permohonan::create($data);

        // save to detail permohonan
        if(isset($createPermohonan)){
            // reset status detail to 99
            $reset = Detail_permohonan::where('permohonan_id', $createPermohonan->id)->update(['status' => '99']);

            Detail_permohonan::create(array(
                'permohonan_id' => $createPermohonan->id,
                'status' => 1,
                'flag' => 1,
                'created_by' => Auth::user()->id
            ));
        }

        // Send notif ke petugas
        foreach ($dataJadwal->petugas as $key => $value) {
            # code...
            $sendNotif = notifikasi(array(
                'to_user' => $value->petugas_id,
                'type' => 'Permohonan'
            ), Auth::user()->name." baru saja membuat permohonan untuk Pelayanan ".$dataJadwal->layananjasa->nama_layanan." pada tanggal $dataJadwal->date_mulai");
        }

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
    public function edit($id)
    {
        $idPermohonan = decryptor($id);
        $permohonan = Permohonan::with('jadwal','layananjasa')->where('id', $idPermohonan)->first();
        $data['token'] = generateToken();
        $data['permohonan'] = $permohonan;

        $dokumen = json_decode($data['permohonan']->dokumen);
        $media = tbl_media::whereIn('id', $dokumen)
                            ->get();
        $data['permohonan']->media = $media;

        return view('pages.permohonan.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = $request->validate([
            'noBapeten' => 'required',
            'jenisLimbah' => 'required',
            'radioAktif' => 'required',
            'jumlah' => 'required'
        ]);

        // dd($id);
        $idPermohonan = decryptor($id);

        $arrMediaDefault = array();
        if(isset($request->defaultDocumen)){
            foreach ($request->defaultDocumen as $key => $value) {
                array_push($arrMediaDefault, (int) decryptor($value));
            }
        }
        $permohonan = Permohonan::findOrFail($idPermohonan);

        $permohonan->no_bapeten = $request->noBapeten;
        $permohonan->jenis_limbah = $request->jenisLimbah;
        $permohonan->sumber_radioaktif = $request->radioAktif;
        $permohonan->jumlah = $request->jumlah;
        $permohonan->status = 1;

        // Update file pendukung
        $documents = $request->file('dokumen');
        $arrMedia = array();
        if($documents){
            foreach ($documents as $key => $document) {
                $idMedia = $this->mediaController->upload($document, 'permohonan');
                array_push($arrMedia, $idMedia);
            }
        }
        $permohonan->dokumen = array_merge($arrMediaDefault, $arrMedia);
        $permohonan->update();

        $this->detail->reset($idPermohonan);

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
