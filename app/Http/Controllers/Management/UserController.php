<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Satuan_kerja;
use App\Models\profile;
use App\Models\Perusahaan;
use App\Models\Master_jobs;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;
use App\Traits\RestApi;

use DataTables;
use DB;

class UserController extends Controller
{
    use RestApi;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = [
            'title' => 'Management',
            'module' => 'users',
            'role' => Role::all(),
            'satuankerja' => Satuan_kerja::all()
        ];

        return view('pages.management.users.index', $data);
    }

    public function getData(){
        $query = User::orderBy('id', 'desc');

        if(request()->has('satuan_kerja') && request()->satuan_kerja != null){
            $query->whereIn('satuankerja_id', (int) decryptor(request()->satuan_kerja));
        }

        if(request()->has('role') && request()->role != null){
            $query->whereHas('roles', function($q) {
                $q->where('name', request()->role);
            });
        }

        return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('action', function($data){
                    return '
                        <a href="'.route('users.edit', encryptor($data->id)).'" class="btn btn-outline-warning btn-sm m-1" ><i class="bi bi-pencil-square"></i> Edit</a>
                    ';
                })
                ->addColumn('role', function($data){
                    if(count($data->getRoleNames()) != 0){
                        $text = '';
                        $isLhu = false;
                        foreach($data->getRoleNames() as $key => $value){
                            $text .= '<span class="badge text-bg-secondary">'.$value.'</span> ';
                            if($value == 'Staff LHU') $isLhu = true;
                        }
                        if($isLhu) {
                            $countJobs = $data->jobs ? count($data->jobs) : 0;
                            $text .= '<br><span class="text-primary cursor-pointer" data-id="'.$data->user_hash.'" onclick="showTugas(this)">('.$countJobs.' Tugas)</span>';
                        }
                        return $text;
                    }else{
                        return '-';
                    }
                })
                ->addColumn('satuankerja', function($data){
                    if(is_array($data->satuankerja_id)) {
                        $satuankerja = $data->satuankerja_id;
                    }else {
                        $satuankerja = $data->satuankerja_id ? [$data->satuankerja_id] : null;
                    }
                    $textSatuan = $satuankerja ? array_map(function($item) {
                        $name = Satuan_kerja::find($item)->name;
                        return '<span class="badge text-bg-secondary">'.$name.'</span>';
                    }, $satuankerja) : [];
                    return "<div class='d-flex flex-wrap align-items-center gap-1'>".implode('', $textSatuan)."</div>";
                })
                ->addColumn('tugas', function($data){
                    $tugas = $data->jobs ? array_map(function($item) {
                        $name = Master_jobs::find($item)->name;
                        return '<span class="badge text-bg-secondary">'.$name.'</span>';
                    }, $data->jobs) : [];
                    return "<div class='d-flex flex-wrap align-items-center gap-1'>".implode('', $tugas)."</div>";
                })
                ->rawColumns(['action', 'role', 'satuankerja', 'tugas'])
                ->make(true);
    }

    public function getById($id) {
        DB::beginTransaction();
        try {
            $user = User::find(decryptor($id));
            if(!is_array($user->satuankerja_id)){
                $user->satuankerja_id = [$user->satuankerja_id];
            }
            $user->satuankerja = Satuan_kerja::whereIn('id', $user->satuankerja_id)->get();

            if($user->jobs){
                $user->jobs = array_map(function($item) {
                    return Master_jobs::find($item);
                }, $user->jobs);
            }

            return $this->output($user);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data = [
            'title' => 'Management',
            'module' => 'users',
            'satuankerja' => Satuan_kerja::all(),
            'role' => Role::all(),
            'jobs' => Master_jobs::all()
        ];

        return view('pages.management.users.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $arrValidator = [
            'name' => ['required', 'string', 'max:255'],
            'nik' => ['required'],
            'no_telepon' => ['required'],
            'jenis_kelamin' => ['required'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            // 'avatar' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'satuanKerja' => ['required'],
            'role' => ['required']
        ];
        $arrMessage = messageSanity($arrValidator);

        $validator = $request->validate($arrValidator, $arrMessage);

        $paramsUser = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ];

        foreach ($request->satuanKerja as $key => $value) {
            $paramsUser['satuankerja_id'][] = (int) decryptor($value);
        }

        $role = $request->role; // json
        if (in_array('Staff LHU', $role)) {
            $paramsUser['jobs'] = array_map(function($item) {
                return (int) decryptor($item);
            }, $request->tugas_lhu);
        }

        $user = User::factory()->create($paramsUser);

        foreach ($role as $key => $value) {
            $user->assignRole($value);
        }

        if($user){

            $profile = Profile::create([
                'user_id' => $user->id,
                'avatar' => null,
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
        $d_user = User::with('profile')->find(decryptor($id));
        if(!is_array($d_user->satuankerja_id)){
            $d_user->satuankerja_id = [$d_user->satuankerja_id];
        }
        $d_user->satuankerja = Satuan_kerja::whereIn('id', $d_user->satuankerja_id)->get();

        if($d_user->jobs){
            $d_user->jobs = array_map(function($item) {
                return encryptor($item);
            }, $d_user->jobs);
        }

        $data = [
            'title' => 'Management',
            'module' => 'users',
            'satuankerja' => Satuan_kerja::all(),
            'role' => Role::all(),
            'd_user' => $d_user,
            'jobs' => Master_jobs::all()
        ];

        return view('pages.management.users.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $arrValidator = [
            'name' => ['required', 'string', 'max:255'],
            'nik' => ['required'],
            'no_telepon' => ['required'],
            'jenis_kelamin' => ['required'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'satuanKerja' => ['required'],
            'role' => ['required']
        ];
        $arrMessage = messageSanity($arrValidator);
        $validator = $request->validate($arrValidator, $arrMessage);

        $idHash = decryptor($id);
        $d_user = User::findOrFail($idHash);
        $profile = profile::where('user_id', $idHash)->first();
        $role = $request->role;

        $d_user->name = $request->name;

        $d_user->roles()->detach();

        foreach($role as $key => $value){
            $d_user->assignRole($value);
        }
        if($request->tugas_lhu) {
            $d_user->jobs = array_map(function($item) {
                return (int) decryptor($item);
            }, $request->tugas_lhu);
        }
        $d_user->satuankerja_id = array_map(function($item) {
            return (int) decryptor($item);
        }, $request->satuanKerja);
        $d_user->update();

        $avatar = null;
        if($request->file('avatar')){
            // Menghapus file sebelumnya
            if(Storage::exists('public/images/avatar'.$profile->avatar)){
                Storage::delete('public/images/avatar'.$profile->avatar);
            }

            $image = $request->file('avatar');

            $filename = 'avatar_'.md5($id).'.'.$image->getClientOriginalExtension();

            $path = $image->storeAs('public/images/avatar', $filename);

            $avatar = $filename;
        }

        if($profile){
            $profile->nik = $request->nik;
            $profile->no_hp = $request->no_telepon;
            $profile->jenis_kelamin = $request->jenis_kelamin;
            $profile->alamat = $request->alamat;
            $profile->avatar = $avatar;
            $profile->update();
        }else{
            profile::create(array(
                'user_id' => $idHash,
                'nik' => $request->nik,
                'no_hp' => $request->no_telepon,
                'jenis_kelamin' => $request->jenis_kelamin,
                'alamat' => $request->alamat,
                'avatar' => $avatar
            ));
        }

        return redirect()->route('users.index')->with('success', 'Berhasil di update');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail(decryptor($id));
            Profile::where('user_id', $user->id)->delete();
            // menghapus semua role yang terikat
            $user->getRoleNames()->each(function ($roleName) use ($user) {
                $user->removeRole($roleName);
            });
            $user->delete();

            DB::commit();

            return $this->output(array('msg' => 'User Behasil dihapus'));
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }
}
