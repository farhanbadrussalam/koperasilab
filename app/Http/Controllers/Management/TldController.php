<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Traits\RestApi;

use App\Models\Master_tld;
use App\Models\Log_activity;
use App\Models\Permohonan_detail;

use DataTables;
use DB;
use Auth;
use Log;

class TldController extends Controller
{
    use RestApi;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = [
            'title' => 'Management',
            'module' => 'tld',
        ];

        return view('pages.management.tld.index', $data);
    }

    public function getData(Request $request)
    {
        $filter = $request->has('filter') ? $request->filter : [];

        $tld = Master_tld::where('status', '!=', '99')
            ->with('pemilik')
            ->when($filter, function($q, $filter) {
                foreach ($filter as $key => $value) {
                    switch ($key) {
                        case 'status':
                            $q->where('status', decryptor($value));
                            break;
                        case 'search':
                            $q->where('no_seri_tld', 'like', '%' . $value . '%');
                            break;
                        case 'jenis':
                            $q->where('jenis', $value);
                            break;
                        case 'no_kontrak':
                            $q->where('digunakan', $value);
                            break;
                        default:
                            break;
                    }
                }
            })
            ->orderBy('kepemilikan', 'asc')
            ->orderBy('status', 'asc')
            ->orderBy('jenis', 'asc');

        // mengambil role
        if(Auth::user()->hasRole('Pelanggan')) {
            $tld->whereNotNull('kepemilikan');
            $tld->where('kepemilikan', Auth::user()->id_perusahaan);
        } else {
            $tld->whereNull('kepemilikan');
        }

        return DataTables::of($tld)
            ->addIndexColumn()
            ->addColumn('no_seri_tld', function ($tld) {
                $htmlKepemilikan = '';
                if($tld->pemilik != null && !Auth::user()->hasRole('Pelanggan')){
                    $htmlKepemilikan = '<small class="text-body-tertiary">' . $tld->pemilik->nama_perusahaan . '</small>';
                }

                return '
                    <div class="d-flex align-items-center">
                        <div class="flex-fill">
                            <div>' . $tld->no_seri_tld . '</div>
                            '. $htmlKepemilikan .'
                        </div>
                    </div>
                ';
            })
            ->addColumn('status', function ($tld) {
                return $tld->status == 1 || $tld->digunakan ? '<span class="badge bg-success">Digunakan</span><br><small class="text-body-tertiary">' . $tld->digunakan . '</small>' : '<span class="badge bg-secondary">Tidak Digunakan</span>';
            })
            ->addColumn('action', function ($tld) {
                $btnDetail = '<button data-id="' . $tld->tld_hash . '" class="btn btn-outline-info btn-sm detail rounded-pill" onclick="btnDetail(this)"><i class="bi bi-info-circle"></i></button>';
                $btn = '<button data-id="' . $tld->tld_hash . '" class="btn btn-outline-warning btn-sm edit rounded-pill" onclick="btnEdit(this)"><i class="bi bi-pencil-square"></i></button>';
                $btnRemove = $tld->status == 0 || !$tld->digunakan ? '<button data-id="'. $tld->tld_hash .'" class="btn btn-outline-danger btn-sm delete rounded-pill" onclick="btnDelete(this)"><i class="bi bi-trash3-fill"></i></button>' : '';
                return '
                    <div class="d-flex justify-content-center gap-2">
                        '.$btnDetail . $btn . $btnRemove.'
                    </div>
                ';
            })
            ->rawColumns(['no_seri_tld','action', 'status'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        $validator = $request->validate([
            'nomer_seri' => 'required',
            'jenis' => 'required',
        ]);
        try {
            $exists = Master_tld::where('no_seri_tld', $request->nomer_seri)
                ->where('jenis', $request->jenis)
                ->where('status', '!=', '99')
                ->exists();

            if ($exists) {
                return $this->output(array('msg' => 'Nomer seri dan jenis sudah ada'), 'Fail', 422);
            }

            // mengambil role
            if(Auth::user()->hasRole('Pelanggan')){
                $kepemilikan = Auth::user()->id_perusahaan;
            }

            Master_tld::create([
                'no_seri_tld' => $request->nomer_seri,
                'jenis' => $request->jenis,
                'merk' => $request->merk,
                'tanggal_pengadaan' => date('Y-m-d H:i:s'),
                'kepemilikan' => isset($kepemilikan) ? $kepemilikan : null,
                'status' => 0
            ]);

            DB::commit();

            return $this->output(array('msg' => 'TLD Behasil ditambahkan'));
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        DB::beginTransaction();
        try {
            $tldId = decryptor($id);
            $tld = Master_tld::with(['pemilik'])->findOrFail($tldId);

            // Check if user has role 'Pelanggan'
            $isPelanggan = Auth::user()->hasRole('Pelanggan');

            // Fetch log activity for this TLD only if the user is NOT Pelanggan
            $logs = [];
            if (!$isPelanggan) {
                $logs = Log_activity::where('subject_type', 'App\Models\Master_tld')
                    ->where('subject_id', $tldId)
                    ->with(['causer'])
                    ->orderBy('created_at', 'desc')
                    ->get();
            }

            // Fetch permohonan details (user assignments)
            $assignments = Permohonan_detail::where('id_tld', $tldId)
                ->with([
                    'entitas',
                    'permohonan.kontrak',
                    'creator'
                ])
                ->orderBy('created_at', 'desc')
                ->get();

            // Active assignment is the first/latest active assignment
            $currentAssignment = $assignments->where('status', 1)->first();
            if (!$currentAssignment) {
                $currentAssignment = $assignments->first();
            }

            // Combine and format logs into a unified timeline
            $combinedLogs = [];

            // 1. Process Master_tld log activities (only for staff, not Pelanggan)
            if (!$isPelanggan) {
                foreach ($logs as $log) {
                    $message = $log->description;
                    $props = $log->properties;

                    if ($log->log_name === 'UPDATE' && is_array($props)) {
                        $changes = [];
                        if (isset($props['perubahan'])) {
                            $perubahan = $props['perubahan'];
                            $sebelumnya = $props['sebelumnya'] ?? [];

                            if (isset($perubahan['no_seri_tld'])) {
                                $oldVal = $sebelumnya['no_seri_tld'] ?? 'Kosong';
                                $newVal = $perubahan['no_seri_tld'];
                                $changes[] = "Nomor Seri diubah dari <strong>{$oldVal}</strong> menjadi <strong>{$newVal}</strong>";
                            }
                            if (isset($perubahan['jenis'])) {
                                $oldVal = $sebelumnya['jenis'] ?? 'Kosong';
                                $newVal = $perubahan['jenis'];
                                $changes[] = "Jenis diubah dari <strong>{$oldVal}</strong> menjadi <strong>{$newVal}</strong>";
                            }
                            if (isset($perubahan['merk'])) {
                                $oldVal = $sebelumnya['merk'] ?? 'Kosong';
                                $newVal = $perubahan['merk'];
                                $changes[] = "Merk diubah dari <strong>{$oldVal}</strong> menjadi <strong>{$newVal}</strong>";
                            }
                            if (isset($perubahan['status'])) {
                                $newVal = (int)$perubahan['status'] === 1 ? 'Digunakan' : 'Tidak Digunakan';
                                $changes[] = "Status TLD berubah menjadi <strong>{$newVal}</strong>";
                            }
                            if (isset($perubahan['digunakan'])) {
                                $newVal = $perubahan['digunakan'] ?? 'Tidak ada';
                                if ($newVal === 'Tidak ada' || is_null($newVal)) {
                                    $changes[] = "TLD dilepas dari penggunaan Kontrak";
                                } else {
                                    $changes[] = "TLD digunakan untuk Kontrak <strong>{$newVal}</strong>";
                                }
                            }
                        }

                        if (!empty($changes)) {
                            $message = implode(', ', $changes);
                        }
                    } elseif ($log->log_name === 'CREATE') {
                        $message = "TLD didaftarkan ke sistem dengan nomor seri <strong>{$tld->no_seri_tld}</strong>";
                    }

                    $combinedLogs[] = [
                        'message' => $message,
                        'created_at' => $log->created_at->toIso8601String(),
                        'user' => $log->causer?->name ?? 'System',
                        'note' => null
                    ];
                }
            }

            // 2. Process assignments (user changes)
            foreach ($assignments as $assign) {
                $userName = $assign->entitas?->name ?? 'Tidak diketahui';
                $userType = $assign->jenis; // 'pengguna' / 'kontrol'
                $contractNo = $assign->permohonan?->kontrak?->no_kontrak ?? $assign->permohonan?->no_kontrak ?? 'Tidak ada';
                $type = $assign->type ?? 'baru'; // baru / ganti

                $roleText = $userType === 'kontrol' ? 'sebagai TLD Kontrol' : 'oleh pengguna';

                $message = "TLD ditugaskan {$roleText} <strong>{$userName}</strong> (Kontrak: <strong>{$contractNo}</strong>)";

                $combinedLogs[] = [
                    'message' => $message,
                    'created_at' => $assign->created_at->toIso8601String(),
                    'user' => $assign->creator?->name ?? 'System',
                    'note' => $type === 'ganti' ? "Menggantikan: " . ($assign->penggunaLama?->name ?? 'Tidak ada') : null
                ];
            }

            // Sort all logs by created_at desc
            usort($combinedLogs, function ($a, $b) {
                return strcmp($b['created_at'], $a['created_at']);
            });

            // Pagination parameters
            $page = (int) request()->get('page', 1);
            $limit = (int) request()->get('limit', 10);
            $offset = ($page - 1) * $limit;
            $slicedLogs = array_slice($combinedLogs, $offset, $limit);
            $hasMoreLogs = count($combinedLogs) > ($offset + $limit);

            DB::commit();

            // If it is just a paginated log request (page > 1)
            if (request()->has('page') && (int)request()->get('page') > 1) {
                return $this->output([
                    'combined_logs' => $slicedLogs,
                    'has_more_logs' => $hasMoreLogs
                ]);
            }

            // Prepare initial payload
            $tldData = $tld->toArray();
            $tldData['current_assignment'] = $currentAssignment;
            $tldData['combined_logs'] = $slicedLogs;
            $tldData['has_more_logs'] = $hasMoreLogs;

            return $this->output($tldData);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        DB::beginTransaction();
        try {
            $exists = Master_tld::where('no_seri_tld', $request->nomer_seri)
                ->where('jenis', $request->jenis)
                ->exists();

            if ($exists) {
                return $this->output(array('msg' => 'No seri dan jenis sudah ada'), 'Fail', 422);
            }

            $tld = Master_tld::findOrFail(decryptor($id));
            $tld->update([
                'no_seri_tld' => $request->nomer_seri,
                'jenis' => $request->jenis,
                'merk' => $request->merk
            ]);

            DB::commit();

            return $this->output(array('msg' => 'TLD Behasil diubah'));
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();
        try {
            $tld = Master_tld::findOrFail(decryptor($id));
            $tld->update([
                'status' => 99
            ]);

            DB::commit();

            return $this->output(array('msg' => 'TLD Behasil dihapus'));
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function searchTld(Request $request)
    {
        $operator = $request->has('operator') ? $request->operator : '=';
        $text = $request->has('q') ? $request->q : '';
        $isPelanggan = Auth::user()->hasRole('Pelanggan');
        $tld = Master_tld::where('status', '!=', '99')
            ->when($operator, function ($query) use ($operator, $text) {
                if ($operator == 'like') {
                    $query->where('no_seri_tld', 'like', '%' . $text . '%');
                } else {
                    $query->where('no_seri_tld', $operator, $text);
                }
            })
            // ->when($isPelanggan, function ($query) {
            //     $query->whereNotNull('kepemilikan');
            //     $query->where('kepemilikan', Auth::user()->id_perusahaan);
            // }, function ($query) {
            //     $query->whereNull('kepemilikan');
            // })
            ->orderBy('no_seri_tld', 'asc')
            ->get();

        return $this->output($tld);
    }
}
