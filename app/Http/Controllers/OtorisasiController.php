<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use DataTables;

class OtorisasiController extends Controller
{
    private $prefix;

    public function __construct()
    {
        $this->prefix = 'Otorisasi-';
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['token'] = generateToken();
        return view('pages.otorisasi.index', $data);
    }

    public function getData()
    {
        $otorisasi = Permission::orderBy('name', 'ASC')->where('name', 'like', $this->prefix.'%')->get();
        $prefix = $this->prefix;

        return DataTables::of($otorisasi)
                ->addIndexColumn()
                ->editColumn('name', function($data) use ($prefix) {
                    return stringSplit($data->name, $prefix);
                })
                ->addColumn('action', function($data) use ($prefix){
                    return '
                        <button class="btn btn-warning btn-sm m-1" data-id="'.encryptor($data->id).'" data-value="'.stringSplit($data->name, $prefix).'" onclick="btnEdit(this)"><i class="bi bi-pencil-square"></i></button>
                        <button class="btn btn-danger btn-sm m-1" data-id="'.encryptor($data->id).'" onclick="btnDelete(this)"><i class="bi bi-trash3-fill"></i></a>
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
        $validator = $request->validate([
            'name' => 'required'
        ]);

        Permission::create(['name' => "$this->prefix$request->name"]);

        return redirect()->route('otorisasi.index')->with('success', 'Berhasil di tambah');
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = $request->validate([
            'name' => 'required'
        ]);

        $idOtorisasi = decryptor($id);
        $data = Permission::findOrFail($idOtorisasi);

        $data->name = "$this->prefix$request->name";
        $data->update();

        return response()->json(['message' => 'Otorisasi berhasil diupdate'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $id_otorisasi = decryptor($id);

        $dataOtorisasi = Permission::findOrFail($id_otorisasi);
        $dataOtorisasi->delete();

        return response()->json(['message' => 'Otorisasi berhasil dihapus'], 200);
    }
}
