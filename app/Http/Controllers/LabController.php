<?php

namespace App\Http\Controllers;

use App\Models\tbl_lab;
use Illuminate\Http\Request;
use DataTables;

class LabController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['token'] = generateToken();
        return view('pages.lab.index', $data);
    }

    public function getData()
    {
        $permissions = tbl_lab::orderBy('name_lab', 'ASC')->get();
        return DataTables::of($permissions)
                ->addIndexColumn()
                ->addColumn('action', function($data){
                    return '
                        <button class="btn btn-warning btn-sm m-1" data-id="'.encryptor($data->id).'" data-value="'.$data->name_lab.'" onclick="btnEdit(this)">Edit</button>
                        <button class="btn btn-danger btn-sm m-1" data-id="'.encryptor($data->id).'" onclick="btnDelete(this)">Delete</a>
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

        tbl_lab::create(['name_lab' => $request->name]);

        return redirect()->route('lab.index')->with('success', 'Berhasil di tambah');
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

        $id_lab = decryptor($id);
        $dataLab = tbl_lab::findOrFail($id_lab);

        $dataLab->name_lab = $request->name;

        $dataLab->update();

        return response()->json(['message' => 'Lab berhasil diupdate'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $id_lab = decryptor($id);

        $dataLab = tbl_lab::findOrFail($id_lab);
        $dataLab->delete();

        return response()->json(['message' => 'Lab berhasil dihapus'], 200);
    }
}
