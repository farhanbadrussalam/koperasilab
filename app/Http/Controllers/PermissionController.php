<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use DataTables;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['token'] = generateToken();
        return view('pages.permission.index', $data);
    }

    public function getData()
    {
        $permissions = Permission::orderBy('name', 'ASC')->where('name', 'not like', 'Otorisasi-%')->get();
        return DataTables::of($permissions)
                ->addIndexColumn()
                ->addColumn('action', function($data){
                    return '
                        <button class="btn btn-warning btn-sm m-1" data-id="'.$data->id.'" data-value="'.$data->name.'" onclick="btnEdit(this)"><i class="bi bi-pencil-square"></i></button>
                        <button class="btn btn-danger btn-sm m-1" onclick="btnDelete('.$data->id.')"><i class="bi bi-trash3-fill"></i></a>
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

        Permission::create(['name' => $request->name]);

        return redirect()->route('permission.index')->with('success', 'Berhasil di tambah');
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
        $name = $request->name;
        $id = $request->id_permission;

        $data = Permission::findOrFail($id);

        $data->name = $request->name;
        $data->update();

        return response()->json(['message' => 'Permission berhasil diupdate'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = Permission::findOrFail($id);
        $data->delete();

        return response()->json(['message' => 'Permission berhasil dihapus'], 200);
    }
}
