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
    public $model = Pengguna::class;

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
                            $q->where($key, decryptor($value));
                        }
                    })
                    ->when($role, function($q, $role) {
                        if(Auth::user()->hasRole('Pelanggan')){
                            $q->where('id_perusahaan', Auth::user()->id_perusahaan);
                        }
                    });

        $type = $request->has('type') ? $request->type : false;

        $type == 'selected' && $pengguna->where('status', 1);

        return DataTables::of($pengguna)
            ->addIndexColumn()
            ->addColumn('name', function ($row) {
                return '
                    <div>
                        <div class="fw-bold">' . $row->name . '</div>
                        <div class="small">' . $row->kode_lencana . '</div>
                    </div>
                ';
            })
            ->addColumn('divisi', function ($row) {
                return $row->divisi ? $row->divisi->name : '-';
            })
            ->addColumn('radiasi', function ($row) {
                $radiasi = Master_radiasi::whereIn('id_radiasi', $row->id_radiasi)->get();
                $htmlRadiasi = '<div class="d-flex flex-wrap justify-content-center">';
                foreach ($radiasi as $key => $value) {
                    $htmlRadiasi .= '
                        <span class="badge rounded text-bg-secondary me-1 mb-1">'.$value->nama_radiasi.'</span>
                    ';
                }
                $htmlRadiasi .= '</div>';
                return $htmlRadiasi;
            })
            ->addColumn('status', function ($row) {
                $status = '';
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
                return $status;
            })
            ->addColumn('action', function ($row) use ($type) {
                $btn = '<div class="btn-group">';
                $btn .= '<a class="btn btn-sm btn-outline-secondary show-popup-image" href="' .asset('/storage/'. $row->media_ktp->file_path . '/' . $row->media_ktp->file_hash). '"><i class="bi bi-file-person-fill"></i></a>';
                $type == 'selected' ? '' : $btn .= '<a href="#" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-square"></i></a>';

                if ($type == 'selected') {
                    $btn .= '<button class="btn btn-sm btn-outline-primary" data-id="' . $row->pengguna_hash . '" onclick="btnPilih(this)"><i class="bi bi-check"></i> Pilih</button>' ;
                } else {
                    $btn .= '<button class="btn btn-sm btn-outline-danger" data-id="' . $row->pengguna_hash . '" onclick="btnDelete(this)"><i class="bi bi-trash3-fill"></i></button>';
                }

                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['name', 'radiasi', 'status', 'action'])
            ->make(true);
    }
}
