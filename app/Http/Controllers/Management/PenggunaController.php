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
        ];

        return view('pages.management.pengguna.index', $data);
    }

    public function getData(Request $request)
    {
        $filter = $request->has('filter') ? $request->filter : [];
        $selected = $request->has('selected') ? $request->selected : [];
        $role = Auth::user()->getRoleNames()->toArray();
        $pengguna = Master_pengguna::with('media_ktp', 'divisi')
            ->when($filter, function ($q, $filter) {
                foreach ($filter as $key => $value) {
                    if ($key == 'name') {
                        $q->where($key, 'like', "%$value%");
                    } else {
                        $q->where($key, decryptor($value));
                    }
                }
            })
            ->when($role, function ($q, $role) {
                if (Auth::user()->hasRole('Pelanggan')) {
                    $q->where('id_perusahaan', Auth::user()->id_perusahaan);
                }
            })
            ->orderBy('id_pengguna', 'desc');

        $type = $request->has('type') ? $request->type : false;

        $type == 'selected' && $pengguna->where('status', 1);

        return DataTables::of($pengguna)
            ->addIndexColumn()
            ->addColumn('pengguna_info', function ($row) {
                $divList = $row->divisi_list_detail;
                $kodes = [];
                if (!empty($divList)) {
                    foreach ($divList as $dItem) {
                        if (!empty($dItem['kode_lencana'])) {
                            $kodes[] = $dItem['kode_lencana'];
                        }
                    }
                }
                $kodeStr = !empty($kodes) ? implode(', ', array_unique($kodes)) : ($row->kode_lencana ?? '-');

                return '<div class="fw-bold text-dark">' . $row->name . '</div>
                        <div class="text-muted small">KODE: ' . $kodeStr . '</div>';
            })
            ->addColumn('divisi_info', function ($row) {
                $divList = $row->divisi_list_detail;
                if (!empty($divList)) {
                    $names = array_column($divList, 'name');
                    return implode(', ', array_unique($names));
                }
                return $row->divisi ? $row->divisi->name : '-';
            })
            ->addColumn('radiasi_info', function ($row) {
                $htmlRadiasi = '';
                if ($row->id_radiasi) {
                    $htmlRadiasi = '<div class="d-flex flex-wrap gap-1">';
                    $radiasi = Master_radiasi::whereIn('id_radiasi', $row->id_radiasi)->get();
                    if ($radiasi) {
                        if ($radiasi->count() > 2) {
                            foreach ($radiasi->take(2) as $value) {
                                $htmlRadiasi .= '<span class="badge rounded bg-secondary">' . $value->nama_radiasi . '</span>';
                            }
                            $popoverContent = "<ul class='list-unstyled mb-0 dropdown-menu overflow-hidden mt-2'>";
                            foreach ($radiasi->slice(2) as $value) {
                                $popoverContent .= "<li class='dropdown-item'>{$value->nama_radiasi}</li>";
                            }
                            $popoverContent .= "</ul>";
                            $htmlRadiasi .= '
                                <div class="dropup">
                                    <span class="badge rounded bg-secondary cursor-pointer" type="button" data-bs-toggle="dropdown">...</span>
                                    ' . $popoverContent . '
                                </div>
                            ';
                        } else {
                            foreach ($radiasi as $value) {
                                $htmlRadiasi .= '<span class="badge rounded bg-secondary">' . $value->nama_radiasi . '</span>';
                            }
                        }
                    }
                    $htmlRadiasi .= '</div>';
                }
                return $htmlRadiasi;
            })
            ->editColumn('status', function ($row) {
                switch ($row->status) {
                    case 1:
                        return '<span class="badge rounded text-bg-danger">Tidak Aktif</span>';
                    case 2:
                        return '<span class="badge rounded text-bg-secondary">Pengajuan</span>';
                    case 3:
                        return '<span class="badge rounded text-bg-success">Aktif</span>';
                    default:
                        return '';
                }
            })
            ->addColumn('action', function ($row) use ($type, $selected) {
                $fileKtp = $row->media_ktp ? asset('/storage/'. $row->media_ktp->file_path . '/' . $row->media_ktp->file_hash) : asset('/images/not-found.png');
                $btn = '<div class="btn-group">';
                $btn .= '<a class="btn btn-sm btn-outline-secondary show-popup-image" href="' . $fileKtp. '"><i class="bi bi-file-person-fill"></i></a>';

                if ($type == 'selected') {
                    $btn .= '<button class="btn btn-sm btn-outline-primary" data-id="' . $row->pengguna_hash . '" onclick="btnPilih(this)"><i class="bi bi-check"></i> Pilih</button>' ;
                } else {
                    if($row->status != 3){
                        $btn .= '<button onclick="editPengguna(this)" data-id="' . $row->pengguna_hash . '" class="btn btn-sm btn-outline-primary btn-edit-pengguna"><i class="bi bi-pencil-square"></i></button>';
                    }

                    if($row->status == 1){
                        $btn .= '<button class="btn btn-sm btn-outline-danger" data-id="' . $row->pengguna_hash . '" onclick="btnDelete(this)"><i class="bi bi-trash3-fill"></i></button>';
                    }
                }

                $btn .= '</div>';
                return $btn;
            })
            ->addColumn('html', function ($row) use ($type, $selected) {
                $fileKtp = $row->media_ktp ? asset('/storage/' . $row->media_ktp->file_path . '/' . $row->media_ktp->file_hash) : asset('/images/not-found.png');

                $btn2 = '';
                $btn = '<div class="btn-group">';

                $btn2 .= '
                    <li>
                        <a class="dropdown-item small cursor-pointer show-popup-image align-self-center" href="' . $fileKtp . '">
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
                    $find = Arr::first($selected, function ($value, $key) use ($row) {
                        return $value == $row->pengguna_hash;
                    });
                    if ($find) {
                        $btn .= '<span class="text-success"><i class="bi bi-check"></i> Terpilih</span>';
                    } else {
                        $btn .= '<button class="btn btn-sm btn-outline-primary align-self-center btn-pilih-user" data-id="' . $row->pengguna_hash . '"> Pilih</button>';
                    }
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

                    if ($row->status != 3) {
                        $btn2 .= $btnEdit;
                    }

                    if ($row->status == 1) {
                        $btn2 .= '
                        <li>
                            <a class="dropdown-item small cursor-pointer text-danger" data-id="' . $row->pengguna_hash . '" onclick="btnDelete(this)">
                                <i class="bi bi-trash3-fill me-2"></i>Hapus
                            </a>
                        </li>';
                    }
                }

                $btn .= '</div>';

                $htmlRadiasi = '';
                if ($row->id_radiasi) {
                    $htmlRadiasi = '<div class="d-flex flex-wrap gap-2 mt-1">';
                    $radiasi = Master_radiasi::whereIn('id_radiasi', $row->id_radiasi)->get();
                    if ($radiasi) {
                        if ($radiasi->count() > 3) {
                            foreach ($radiasi->take(3) as $value) {
                                $htmlRadiasi .= '
                                    <span class="badge rounded text-bg-secondary">' . $value->nama_radiasi . '</span>
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
                                    ' . $popoverContent . '
                                </div>
                            ';
                        } else {
                            foreach ($radiasi as $key => $value) {
                                $htmlRadiasi .= '
                                    <span class="badge rounded text-bg-secondary">' . $value->nama_radiasi . '</span>
                                ';
                            }
                        }
                    }
                    $htmlRadiasi .= '</div>';
                }

                // Render multi-divisi list dengan indikator kontrak aktif
                $htmlDivisiKode = '<div class="d-flex flex-wrap align-items-center gap-2 mt-1">';
                $divList = $row->divisi_list_detail;
                if (!empty($divList)) {
                    foreach ($divList as $dItem) {
                        $divName = $dItem['name'] ?? '-';
                        $kLencana = $dItem['kode_lencana'] ?? '-';
                        
                        $isKontrakAktif = false;
                        if ($dItem['id_divisi']) {
                            $isKontrakAktif = \App\Models\Kontrak_detail::where('jenis', 'pengguna')
                                ->where('id_pengguna_divisi', $row->id_pengguna)
                                ->where('status', 1)
                                ->where(function ($q) use ($dItem) {
                                    $q->where('id_divisi_selected', $dItem['id_divisi'])
                                      ->orWhereNull('id_divisi_selected');
                                })
                                ->whereHas('kontrak', fn($q) => $q->where('status', 1))
                                ->exists();
                        }

                        $activeBadge = $isKontrakAktif ? ' <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size: 0.65rem;" title="Divisi ini masih terikat pada kontrak aktif"><i class="bi bi-lock-fill me-1"></i>Kontrak Aktif</span>' : '';

                        $htmlDivisiKode .= '
                            <div class="border rounded px-2 py-1 bg-light d-inline-flex align-items-center gap-1">
                                <span class="badge bg-secondary" style="font-size: 0.7rem;">KODE: ' . $kLencana . '</span>
                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle" style="font-size: 0.7rem;">' . $divName . '</span>
                                ' . $activeBadge . '
                            </div>
                        ';
                    }
                } else {
                    $htmlDivisiKode .= '
                        <span class="badge bg-light text-secondary border">KODE: ' . ($row->kode_lencana ?? '-') . '</span>
                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle" style="font-size: 0.7rem;">' . ($row->divisi?->name ?? '-') . '</span>
                    ';
                }
                $htmlDivisiKode .= '</div>';

                return '
                    <div class="d-flex align-items-center w-100 p-2 rounded-3 hover-bg-light transition-all border-bottom">
                        <div class="me-3 flex-grow-1">
                            <h6 class="mb-0 fw-bold text-dark">' . $row->name . '</h6>
                            ' . $htmlDivisiKode . '
                            ' . $htmlRadiasi . '
                        </div>

                        <div class="me-3">
                            ' . $status . '
                        </div>

                        ' . $btn . '

                        <div class="d-inline-block ms-2">
                            <button class="btn btn-light btn-sm rounded-circle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-1 overflow-hidden">
                                ' . $btn2 . '
                            </ul>
                        </div>
                    </div>
                ';
            })
            ->rawColumns(['action', 'pengguna_info', 'divisi_info', 'radiasi_info', 'status', 'html'])
            ->make(true);
    }
}
