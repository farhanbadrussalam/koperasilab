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

    public function getData()
    {
        $permissions = Permission::orderBy('name', 'ASC')->where('name', 'not like', 'Otorisasi-%')->get();
        return DataTables::of($permissions)
                ->addIndexColumn()
                ->addColumn('action', function($data){
                    return '
                        <button class="btn btn-outline-warning btn-sm m-1" data-id="'.$data->id.'" data-value="'.$data->name.'" onclick="btnEdit(this)"><i class="bi bi-pencil-square"></i> Edit</button>
                        <button class="btn btn-outline-danger btn-sm m-1" data-id="'.$data->id.'" onclick="btnDelete(this)"><i class="bi bi-trash3-fill"></i> Hapus</a>
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
        DB::beginTransaction();
        try {
            Permission::create(['name' => $request->name]);
            DB::commit();

            return $this->output(array('msg' => 'Permission Behasil ditambahkan'));
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        DB::beginTransaction();
        try {
            $name = $request->name;
            $id = $request->id_permission;
    
            $data = Permission::findOrFail($id);
            $data->name = $name;
            $data->update();
    
            DB::commit();
            
            return $this->output(array('msg' => 'Permission Behasil diubahkan'));
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
            $data = Permission::findOrFail($id);
            $data->delete();
    
            DB::commit();
    
            return $this->output(array('msg' => 'Permission Behasil dihapus'));
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }
}
