<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use DataTables;

class RolesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['permissions'] = Permission::orderBy('name', 'ASC')->get();
        $data['token'] = generateToken();
        return view('pages.roles.index', $data);
    }

    public function getData()
    {
        $role = Role::all();
        return DataTables::of($role)
                ->addIndexColumn()
                ->addColumn('action', function($data){
                    return '
                        <button class="btn btn-warning btn-sm" data-id="'.$data->id.'" data-value="'.$data->name.'" onclick="btnEdit(this)">Edit</button>
                        <button class="btn btn-danger btn-sm" onclick="btnDelete('.$data->id.')">Delete</a>
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
        $role = Role::create(['name' => $request->name]);

        // Give permission
        foreach ($request->permission as $key => $permission) {
            $role->givePermissionTo($permission);
        }

        return redirect()->route('roles.index')->with('success', 'Berhasil di tambah');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $role = Role::with('permissions')->where('id', $id)->first();

        return response()->json($role);
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
        $data = Role::findOrFail($request->id_role);

        $data->name = $request->name;
        $data->update();

        // Update permission
        $data->syncPermissions($request->permission);

        return response()->json(['message' => 'Role berhasil diupdate'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = Role::findOrFail($id);
        $data->delete();

        return response()->json(['message' => 'Role berhasil dihapus'], 200);
    }
}
