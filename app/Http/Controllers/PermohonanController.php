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
use App\Http\Controllers\API\PermohonanAPI;

use Auth;
use DataTables;

class PermohonanController extends Controller
{
    public function __construct() {
        $this->mediaController = resolve(MediaController::class);
        $this->detail = resolve(DetailPermohonanController::class);
        $this->permohonanAPI = resolve(PermohonanAPI::class);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = [
            'title' => 'Permohonan',
            'module' => 'permohonan'
        ];

        return view('pages.permohonan.index', $data);
    }

    public function getData(){
        $user = Auth::user();
        $status = 0;
        if(request()->has('status') && request('status')){
            $status = request('status');
        }
        $informasi = Permohonan::with(['layananjasa', 'jadwal.tbl_lhu', 'tbl_kip'])
                        ->where('status', '!=', 99)
                        ->where('created_by', $user->id)
                        ->where('status', $status)
                        ->orderBy('created_at', 'DESC');

        if($status == 3){
            $informasi->whereHas('jadwal.tbl_lhu', function ($query) {
                $query->where('level', 5);
            });

            $informasi->whereHas('tbl_kip', function ($query) {
                $query->where('status', 3);
            });
        }
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

                    $btn_list_action = '';

                    if($data->status == 1 || $data->status == 9){
                        $btn_list_action .= '
                            <li class="my-1 cursoron">
                                <button class="dropdown-item dropdown-item-lab subbody text-warning" onclick="edit_permohonan('.$idHash.')">
                                    <i class="bi bi-pencil-square"></i>&nbsp;Update
                                </button>
                            </li>
                        ';
                    }

                    $btn_list_action .= '
                        <li class="my-1 cursoron">
                            <a class="dropdown-item dropdown-item-lab subbody text-success" onclick="show_detail_permohonan('.$idHash.')">
                                <i class="bi bi-info-circle"></i>&nbsp;Rincian
                            </a>
                        </li>
                    ';

                    $btn_list_action .= $data->status == 1 || $data->status == 9 ? '
                        <li class="my-1 cursoron">
                            <a class="dropdown-item dropdown-item-lab subbody text-danger" onclick="btnDelete('.$idHash.')">
                                <i class="bi bi-trash"></i>&nbsp;Delete
                            </a>
                        </li>
                    ' : '';

                    if($data->status == 3){
                        if($data->tbl_kip->bukti_pembayaran){
                            $btn_action = '
                                <button class="btn btn-outline-success btn-sm" onclick="btnDetailPayment('.$idHash.')">
                                    <i class="bi bi-credit-card-2-back-fill"></i> Lunas</button>
                            ';
                        }else{
                            $btn_action = '
                                <a class="btn btn-outline-primary btn-sm" href="'.url('permohonan/payment/'.$data->permohonan_hash).'">
                                    <i class="bi bi-credit-card-2-back-fill"></i> Proses payment</a>
                            ';
                        }
                    }else{
                        $btn_action = '
                            <div class="dropdown">
                                <div class="more-option d-flex align-items-center justify-content-center mx-0 mx-md-4" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </div>
                                <ul class="dropdown-menu shadow-sm px-2">
                                    '.$btn_list_action.'
                                </ul>
                            </div>
                        ';
                    }

                    return '
                    <div class="row border m-0 rounded">
                        <div class="d-flex flex-wrap p-3 align-items-center">
                            <div class="col-md-8 col-sm-10 mb-sm-2">
                                <span class="fw-bold">'.$data->layananjasa->nama_layanan.'</span>
                                <div class="text-body-secondary text-start">
                                    <small><b>Created</b> : '.convert_date($data->created_at, 1).'</small>
                                    <div>
                                        <span class="badge text-bg-secondary">'.$data->jenis_layanan.'</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 align-items-center">
                                '.statusFormat('permohonan', $data->status).'
                            </div>
                            <div class="col-md-2 col-sm-12 text-end">
                                '.$btn_action.'
                            </div>
                            '. $co_reason .'
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
        $data = [
            'title' => 'Create permohonan',
            'module' => 'permohonan',
            'layanan' => Layanan_jasa::where('status', '!=', '99')->get()
        ];

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

    public function payment($idPermohonan)
    {
        $idHash = decryptor($idPermohonan);

        $data = [
            'title' => 'Permohonan',
            'module' => 'permohonan'
        ];

        $data['permohonan'] = Permohonan::with(
            'layananjasa:id,nama_layanan',
            'jadwal:id,date_mulai,date_selesai',
            'user:id,email,name',
            'jadwal.tbl_lhu', 'tbl_kip')
        ->where('id', $idHash)
        ->first();

        return view('pages.permohonan.payment', $data);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permohonan $permohonan)
    {
        //
    }
}
