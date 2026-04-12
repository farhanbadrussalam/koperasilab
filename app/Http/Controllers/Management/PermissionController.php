<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Arr;
use App\Traits\RestApi;

use DataTables;
use DB;

class PermissionController extends Controller
{
    use RestApi;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = [
            'title' => 'Management',
            'module' => 'permission'
        ];

        return view('pages.management.permission.index', $data);
    }

    public function getData(Request $request)
    {
        $filter = $request->has('filter') ? $request->filter : [];
        $permissions = Permission::orderBy('name', 'ASC')
            ->where('name', 'not like', 'Otorisasi-%')
            ->when($filter, function($q, $filter) {
                foreach ($filter as $key => $value) {
                    $q->where('name', 'like', "%$value%");
                }
            })
            ->get();
        return DataTables::of($permissions)
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
            'name' => 'required|unique:permissions,name'
        ]);

        try {
            $permission = Permission::create(['name' => $request->name]);
            return $this->output(['msg' => 'Permission berhasil ditambahkan', 'data' => $permission]);
        } catch (\Exception $ex) {
            info($ex);
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name,' . ($request->id_permission ?? $id)
        ]);

        try {
            $targetId = $request->id_permission ?? $id;
            $data = Permission::findOrFail($targetId);
            $data->name = $request->name;
            $data->save();

            return $this->output(array('msg' => 'Permission berhasil diperbarui'));
        } catch (\Exception $ex) {
            info($ex);
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $data = Permission::findOrFail($id);
            $data->delete();

            return $this->output(array('msg' => 'Permission berhasil dihapus'));
        } catch (\Exception $ex) {
            info($ex);
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }
}
