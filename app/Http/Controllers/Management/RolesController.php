<?php

namespace App\Http\Controllers\Management;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Arr;
use App\Traits\RestApi;

use DataTables;
use DB;

class RolesController extends Controller
{
    use RestApi;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = [
            'title' => 'Management',
            'module' => 'roles',
            'permissions' => Permission::orderBy('name', 'ASC')->get()
        ];

        return view('pages.management.roles.index', $data);
    }

    public function getData(Request $request)
    {
        $filter = $request->has('filter') ? $request->filter : [];

        $role = Role::when($filter, function($q, $filter) {
            foreach ($filter as $key => $value) {
                $q->where('name', 'like', "%$value%");
            }
        })
        ->get();

        return DataTables::of($role)
                ->addIndexColumn()
                ->addColumn('action', function($data){
                    return '
                        <button class="btn btn-outline-warning btn-sm m-1 rounded-pill" data-id="'.$data->id.'" data-value="'.$data->name.'" onclick="btnEdit(this)"><i class="bi bi-pencil-square"></i></button>
                        <button class="btn btn-outline-danger btn-sm m-1 rounded-pill" data-id="'.$data->id.'" onclick="btnDelete(this)"><i class="bi bi-trash3-fill"></i></a>
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
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permission' => 'nullable|array'
        ], [
            'name.required' => 'Nama role wajib diisi.',
            'name.unique' => 'Nama role sudah terdaftar.'
        ]);

        DB::beginTransaction();
        try {
            $role = Role::create(['name' => $request->name]);

            // Give permission
            if ($request->has('permission')) {
                foreach ($request->permission as $key => $permission) {
                    $role->givePermissionTo($permission);
                }
            }

            DB::commit();

            return $this->output(array('msg' => 'Role Behasil ditambahkan'));
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
            $role = Role::with('permissions')->where('id', $id)->first();

            DB::commit();

            return $this->output($role);
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
        $targetId = $request->id_role ?? $id;
        $request->validate([
            'name' => 'required|unique:roles,name,' . $targetId,
            'permissionEdit' => 'nullable|array'
        ], [
            'name.required' => 'Nama role wajib diisi.',
            'name.unique' => 'Nama role sudah terdaftar.'
        ]);

        DB::beginTransaction();
        try {
            $data = Role::findOrFail($targetId);

            $data->name = $request->name;
            $data->update();

            // Update permission
            $permissions = $request->permissionEdit ?? [];
            $data->syncPermissions($permissions);

            DB::commit();

            return $this->output(array('msg' => 'Role Behasil diubah'));
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
            $data = Role::findOrFail($id);
            $data->delete();

            DB::commit();

            return $this->output(array('msg' => 'Role Behasil dihapus'));
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }
}
