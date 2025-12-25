<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Traits\RestApi;

use App\Models\Master_pengguna;
use App\Models\Master_radiasi;
use App\Models\Master_divisi;

use DataTables;
use DB;
use Auth;

class PenggunaController extends Controller
{
    use RestApi;

    public function index()
    {
        $user = Auth::user();
        $data = [
            'title' => 'Data Pengguna',
            'module' => 'pengguna',
            'radiasi' => Master_radiasi::where('status', '1')->get(),
            'divisi' => Master_divisi::where('status', '1')->where('id_perusahaan', $user->id_perusahaan)->get(),
        ];

        return view('pages.management.pengguna.index', $data);
    }

    public function getData(Request $request)
    {
        $filter = $request->has('filter') ? $request->filter : [];
        $role = Auth::user()->getRoleNames()->toArray();
        $pengguna = Master_pengguna::with('media_ktp', 'divisi')
                    ->when($filter, function($q, $filter) {
                        foreach ($filter as $key => $value) {
                            if($key == 'name') {
                                $q->where($key, 'like', "%$value%");
                            } else {
                                $q->where($key, decryptor($value));
                            }
                        }
                    })
                    ->when($role, function($q, $role) {
                        if(Auth::user()->hasRole('Pelanggan')){
                            $q->where('id_perusahaan', Auth::user()->id_perusahaan);
                        }
                    })
                    ->orderBy('id_pengguna', 'desc');

        $type = $request->has('type') ? $request->type : false;

        $type == 'selected' && $pengguna->where('status', 1);

        return DataTables::of($pengguna)
            ->addIndexColumn()
            ->addColumn('html', function ($row) use ($type) {
                $initial = isset($row->name) ? strtoupper(substr($row->name, 0, 1)) : '?';

                $fileKtp = $row->media_ktp ? asset('/storage/'. $row->media_ktp->file_path . '/' . $row->media_ktp->file_hash) : '';
                $btn = '<div class="btn-group">';
                $btn .= '<a class="btn btn-sm btn-outline-secondary show-popup-image align-self-center" href="' . $fileKtp. '"><i class="bi bi-file-person-fill"></i></a>';

                $status = '';
                if ($type == 'selected') {
                    $btn .= '<button class="btn btn-sm btn-outline-primary align-self-center" data-id="' . $row->pengguna_hash . '" onclick="btnPilih(this)"> Pilih</button>' ;
                } else {
                    switch ($row->status) {
                        case 1:
                            $status = '<span class="badge rounded text-bg-danger">Tidak Aktif</span>';
                            break;
                        case 2:
                            $status = '<span class="badge rounded text-bg-secondary">Pengajuan</span>';
                            break;
                        case 3:
                            $status = '<span class="badge rounded text-bg-success">Aktif</span>';
                            break;
                    }

                    if($row->status != 3){
                        $btn .= '<button onclick="editPengguna(this)" data-id="' . $row->pengguna_hash . '" class="btn btn-sm btn-outline-primary align-self-center"><i class="bi bi-pencil-square"></i></button>';
                    }

                    if($row->status == 1){
                        $btn .= '<button class="btn btn-sm btn-outline-danger align-self-center" data-id="' . $row->pengguna_hash . '" onclick="btnDelete(this)"><i class="bi bi-trash3-fill"></i></button>';
                    }
                }

                $btn .= '</div>';

                $radiasi = Master_radiasi::whereIn('id_radiasi', $row->id_radiasi)->get();
                $htmlRadiasi = '<div class="d-flex flex-wrap gap-2 mt-1">';
                if ($radiasi->count() > 3) {
                    foreach ($radiasi->take(3) as $value) {
                        $htmlRadiasi .= '
                            <span class="badge rounded text-bg-secondary">'.$value->nama_radiasi.'</span>
                        ';
                    }
                    $popoverContent = "<ul class='list-unstyled mb-0 dropdown-menu overflow-hidden mt-2'>";
                    foreach ($radiasi->slice(3) as $value) {
                        $popoverContent .= "<li class='dropdown-item'>{$value->nama_radiasi}</li>";
                    }
                    $popoverContent .= "</ul>";
                    $htmlRadiasi .= '
                        <div class="dropup">
                            <span class="badge rounded text-bg-secondary cursor-pointer" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Daftar Radiasi">...</span>
                            '.$popoverContent.'
                        </div>
                    ';
                } else {
                    foreach ($radiasi as $key => $value) {
                        $htmlRadiasi .= '
                            <span class="badge rounded text-bg-secondary">'.$value->nama_radiasi.'</span>
                        ';
                    }
                }
                $htmlRadiasi .= '</div>';

                return '
                    <div class="d-flex align-items-center w-100 p-2 rounded-3 hover-bg-light transition-all border-bottom">

                        <div class="rounded-circle bg-primary-subtle text-primary fw-bold d-flex justify-content-center align-items-center me-3 flex-shrink-0"
                            style="width: 48px; height: 48px; font-size: 1.2rem;">
                            '.$initial.'
                        </div>

                        <div class="me-3 flex-grow-1">
                            <h6 class="mb-0 fw-bold text-dark">'.$row->name.'</h6>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <span class="badge bg-light text-secondary border">KODE: '.$row->kode_lencana.'</span>
                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle" style="font-size: 0.7rem;">
                                    '.($row->divisi?->name ?? '-').'
                                </span>
                            </div>
                            '.$htmlRadiasi.'
                        </div>

                        <div class="me-3">
                            '.$status.'
                        </div>

                        '. $btn .'
                    </div>
                ';
            })
            // ->addColumn('name', function ($row) {
            //     return '
            //         <div>
            //             <div class="fw-bold">' . $row->name . '</div>
            //             <div class="small">' . $row->kode_lencana . '</div>
            //         </div>
            //     ';
            // })
            // ->addColumn('divisi', function ($row) {
            //     return $row->divisi ? $row->divisi->name : '-';
            // })
            // ->addColumn('radiasi', function ($row) {
            //     $radiasi = Master_radiasi::whereIn('id_radiasi', $row->id_radiasi)->get();
            //     $htmlRadiasi = '<div class="d-flex flex-wrap justify-content-center">';
            //     foreach ($radiasi as $key => $value) {
            //         $htmlRadiasi .= '
            //             <span class="badge rounded text-bg-secondary me-1 mb-1">'.$value->nama_radiasi.'</span>
            //         ';
            //     }
            //     $htmlRadiasi .= '</div>';
            //     return $htmlRadiasi;
            // })
            // ->addColumn('status', function ($row) {
            //     $status = '';
            //     switch ($row->status) {
            //         case 1:
            //             $status = '<span class="badge rounded text-bg-danger">Tidak Aktif</span>';
            //             break;
            //         case 2:
            //             $status = '<span class="badge rounded text-bg-secondary">Pengajuan</span>';
            //             break;
            //         case 3:
            //             $status = '<span class="badge rounded text-bg-success">Aktif</span>';
            //             break;
            //     }
            //     return $status;
            // })
            // ->addColumn('action', function ($row) use ($type) {
            //     $fileKtp = $row->media_ktp ? asset('/storage/'. $row->media_ktp->file_path . '/' . $row->media_ktp->file_hash) : '';
            //     $btn = '<div class="btn-group">';
            //     $btn .= '<a class="btn btn-sm btn-outline-secondary show-popup-image" href="' . $fileKtp. '"><i class="bi bi-file-person-fill"></i></a>';


            //     if ($type == 'selected') {
            //         $btn .= '<button class="btn btn-sm btn-outline-primary" data-id="' . $row->pengguna_hash . '" onclick="btnPilih(this)"><i class="bi bi-check"></i> Pilih</button>' ;
            //     } else {
            //         if($row->status != 3){
            //             $btn .= '<button onclick="editPengguna(this)" data-id="' . $row->pengguna_hash . '" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-square"></i></button>';
            //         }

            //         if($row->status == 1){
            //             $btn .= '<button class="btn btn-sm btn-outline-danger" data-id="' . $row->pengguna_hash . '" onclick="btnDelete(this)"><i class="bi bi-trash3-fill"></i></button>';
            //         }
            //     }

            //     $btn .= '</div>';
            //     return $btn;
            // })
            ->rawColumns(['html'])
            ->make(true);
    }
}
