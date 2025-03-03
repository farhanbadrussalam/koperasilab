<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Traits\RestApi;

use App\Models\Master_tld;

use DataTables;
use DB;

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
        $tld = Master_tld::where('status', '!=', '99')->orderBy('id_tld', 'desc');

        if(request()->has('status') && request()->status != null){
            $tld->where('status', request()->status);
        }

        if(request()->has('jenis') && request()->jenis != null){
            $tld->where('jenis', request()->jenis);
        }

        return DataTables::of($tld)
            ->addIndexColumn()
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
            ->rawColumns(['action', 'status'])
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
            'kode_lencana' => 'required',
            'jenis' => 'required',
        ]);
        try {
            $exists = Master_tld::where('kode_lencana', $request->kode_lencana)
                ->where('jenis', $request->jenis)
                ->where('status', '!=', '99')
                ->exists();

            if ($exists) {
                return $this->output(array('msg' => 'Kode lencana dan jenis sudah ada'), 'Fail', 422);
            }

            Master_tld::create([
                'kode_lencana' => $request->kode_lencana,
                'jenis' => $request->jenis,
                'status' => 2
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
            $exists = Master_tld::where('kode_lencana', $request->kode_lencana)
                ->where('jenis', $request->jenis)
                ->exists();

            if ($exists) {
                return $this->output(array('msg' => 'Kode lencana dan jenis sudah ada'), 'Fail', 422);
            }
            
            $tld = Master_tld::findOrFail(decryptor($request->id_tld));
            $tld->update([
                'kode_lencana' => $request->kode_lencana,
                'jenis' => $request->jenis
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
