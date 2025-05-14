<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Traits\RestApi;

use App\Models\Master_tld;

use DataTables;
use DB;
use Auth;

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

    public function getData()
    {
        $tld = Master_tld::where('status', '!=', '99')
            ->with('pemilik')
            ->orderBy('kepemilikan', 'asc')
            ->orderBy('status', 'asc')
            ->orderBy('jenis', 'asc');

        // mengambil role
        Auth::user()->getRoleNames()[0] == 'Pelanggan' ? $tld->where('kepemilikan', Auth::user()->id_perusahaan) : false;

        if(request()->has('status') && request()->status != null){
            $tld->where('status', request()->status);
        }

        if(request()->has('jenis') && request()->jenis != null){
            $tld->where('jenis', request()->jenis);
        }

        return DataTables::of($tld)
            ->addIndexColumn()
            ->addColumn('no_seri_tld', function ($tld) {
                $htmlKepemilikan = '';
                if($tld->pemilik != null && Auth::user()->getRoleNames()[0] != 'Pelanggan'){
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
                return $tld->status == 1 ? '<span class="badge bg-success">Digunakan</span>' : '<span class="badge bg-secondary">Tidak Digunakan</span>';
            })
            ->addColumn('action', function ($tld) {
                $btn = '
                    <button data-id="' . $tld->tld_hash . '" class="btn btn-outline-warning btn-sm edit" onclick="btnEdit(this)"><i class="bi bi-pencil-square"></i> Edit</button>
                    <button data-id="'. $tld->tld_hash .'" class="btn btn-outline-danger btn-sm delete" onclick="btnDelete(this)"><i class="bi bi-trash3-fill"></i> Hapus</button>
                ';
                return $btn;
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
            $role = Auth::user()->getRoleNames()[0];
            if($role == 'Pelanggan'){
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
        //
        DB::beginTransaction();
        try {
            $tld = Master_tld::findOrFail(decryptor($id));

            DB::commit();

            return $this->output($tld);
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
            
            $tld = Master_tld::findOrFail(decryptor($request->id_tld));
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
}
