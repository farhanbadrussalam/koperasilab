<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Traits\RestApi;

use App\Models\Master_radiasi;

use DataTables;
use DB;

class RadiasiController extends Controller
{
    use RestApi;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = [
            'title' => 'Management',
            'module' => 'radiasi',
        ];

        return view('pages.management.radiasi.index', $data);
    }

    public function getData()
    {
        $radiasi = Master_radiasi::where('status', '!=', '99')->orderBy('id_radiasi', 'desc');

        return DataTables::of($radiasi)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                return '
                    <button data-id="' . $row->radiasi_hash . '" class="btn btn-outline-warning btn-sm edit" onclick="btnEdit(this)"><i class="bi bi-pencil-square"></i> Edit</button>
                    <button data-id="' . $row->radiasi_hash . '" class="btn btn-outline-danger btn-sm delete" onclick="btnDelete(this)"><i class="bi bi-trash3-fill"></i> Hapus</button>
                ';
            })
            ->rawColumns(['action'])
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
        try {
            Master_radiasi::create([
                'nama_radiasi' => $request->nama_radiasi,
                'status' => 1
            ]);

            DB::commit();

            return $this->output(array('msg' => 'Radiasi Behasil ditambahkan'));
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
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        DB::beginTransaction();
        try {
            $radiasi = Master_radiasi::findOrFail(decryptor($id));

            DB::commit();

            return $this->output($radiasi);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        DB::beginTransaction();
        try {
            $radiasi = Master_radiasi::findOrFail(decryptor($request->id_radiasi));
            $radiasi->update([
                'nama_radiasi' => $request->nama_radiasi
            ]);

            DB::commit();

            return $this->output(array('msg' => 'Radiasi Behasil diubah'));
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
            $radiasi = Master_radiasi::findOrFail(decryptor($id));
            $radiasi->update([
                'status' => 99
            ]);

            DB::commit();

            return $this->output(array('msg' => 'Radiasi Behasil dihapus'));
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }
}
