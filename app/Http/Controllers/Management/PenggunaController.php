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

                $btn2 = '';
                $btn = '<div class="btn-group">';

                $btn2 .= '
                    <li>
                        <a class="dropdown-item small cursor-pointer show-popup-image align-self-center" href="' . $fileKtp. '">
                            <i class="bi bi-file-person-fill me-2"></i>Lihat Ktp
                        </a>
                    </li>
                ';

                $btnEdit = '
                    <li>
                        <a data-id="' . $row->pengguna_hash . '" class="dropdown-item small cursor-pointer btn-edit-pengguna">
                            <i class="bi bi-pencil-square me-2"></i>Edit
                        </a>
                    </li>
                ';

                $status = '';
                if ($type == 'selected') {
                    $btn .= '<button class="btn btn-sm btn-outline-primary align-self-center btn-pilih-user" data-id="' . $row->pengguna_hash . '"> Pilih</button>' ;
                    $btn2 .= $btnEdit;
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
                        $btn2 .= $btnEdit;
                    }

                    if($row->status == 1){
                        $btn2 .= '
                        <li>
                            <a class="dropdown-item small cursor-pointer text-danger" data-id="' . $row->pengguna_hash . '" onclick="btnDelete(this)">
                                <i class="bi bi-trash3-fill me-2"></i>Hapus
                            </a>
                        </li>';
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

                        <div class="d-inline-block ms-2">
                            <button class="btn btn-light btn-sm rounded-circle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-1 overflow-hidden">
                                '. $btn2 .'
                            </ul>
                        </div>
                    </div>
                ';
            })
            ->rawColumns(['html'])
            ->make(true);
    }
}
