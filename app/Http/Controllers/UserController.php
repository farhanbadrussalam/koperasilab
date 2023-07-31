<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Satuan_kerja;
use App\Models\profile;
use App\Models\Perusahaan;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use DataTables;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['token'] = generateToken();
        return view('pages.users.index', $data);
    }

    public function getData(){
        $query = User::orderBy('id');

        return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('action', function($data){
                    return '
                        <button class="btn btn-warning btn-sm" >Edit</button>
                    ';
                })
                ->addColumn('role', function($data){
                    return $data->getRoleNames()[0];
                })
                ->rawColumns(['action'])
                ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data['satuankerja'] = Satuan_kerja::all();
        $data['role'] = Role::all();
        $data['token'] = generateToken();
        return view('pages.users.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nik' => ['required'],
            'no_telepon' => ['required'],
            'jenis_kelamin' => ['required'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'avatar' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'satuanKerja' => ['required'],
            'role' => ['required']
        ]);

        $user = User::factory()->create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'satuankerja_id' => $request->satuanKerja
        ])->assignRole($request->role);

        if($user){
            if($request->file('avatar')){
                $image = $request->file('avatar');

                $filename = 'avatar_'.$user->id.'.'.$image->getClientOriginalExtension();

                $path = $image->storeAs('public/images/avatar', $filename);
            }else{
                $filename = '';
            }

            $profile = Profile::create([
                'user_id' => $user->id,
                'avatar' => $filename,
                'nik' => $request->nik,
                'no_hp' => $request->no_telepon,
                'jenis_kelamin' => $request->jenis_kelamin,
                'alamat' => $request->alamat
            ]);

            if($request->role == 'Pelanggan'){
                $perusahaan = Perusahaan::create([
                    'user_id' => $user->id
                ]);
            }
        }

        return redirect()->route('users.index')->with('success', 'Berhasil di tambah');
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
