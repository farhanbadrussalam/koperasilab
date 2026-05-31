<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Users_request;
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
use Auth;
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

    public function getData(Request $request)
    {
        $filter = $request->has('filter') ? $request->filter : [];

        $query = User::when($filter, function ($q, $filter) {
            foreach ($filter as $key => $value) {
                switch ($key) {
                    case 'satuan_kerja':
                        $q->whereJsonContains('satuankerja_id', (int) decryptor($value));
                        break;
                    case 'search':
                        $q->where('name', 'like', "%$value%");
                        break;
                }
            }
        })->orderBy('id', 'desc');

        $user = Auth::user();

        if (!is_array($user->satuankerja_id)) {
            $user->satuankerja_id = [$user->satuankerja_id];
        }

        if (!$user->hasRole('Super Admin')) {
            foreach ($user->satuankerja_id as $key => $satuanId) {
                if ($key == 0) {
                    $query->whereJsonContains('satuankerja_id', $satuanId);
                } else {
                    $query->orWhereJsonContains('satuankerja_id', $satuanId);
                }
            }
        }

        if (isset($filter['roles']) && $filter['roles'] != null) {
            $role = $filter['roles'];
            $query->whereHas('roles', function ($q) use ($role) {
                if (is_array($role)) $q->whereIn('name', $role);
                else $q->where('name', $role);
            });
        }

        if (request()->has('satuan_kerja') && request()->satuan_kerja != null) {
            $satuan_kerja = decryptor(request()->satuan_kerja);
            $query->whereJsonContains('satuankerja_id', (int) $satuan_kerja);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('name', function ($data) {
                $avatarHtml = renderUserAvatar($data, false, '32px', 'me-2');
                return '<div class="d-flex align-items-center">' . $avatarHtml . '<span class="fw-semibold text-dark">' . $data->name . '</span></div>';
            })
            ->editColumn('email', function ($data) {
                return '<span class="text-secondary"><i class="bi bi-envelope text-body-tertiary me-1"></i>' . $data->email . '</span>';
            })
            ->addColumn('action', function ($data) {
                return '<div class="text-center"><a href="' . route('users.edit', encryptor($data->id)) . '" class="btn btn-outline-primary btn-sm rounded-pill px-3 transition-all hover-scale shadow-sm"><i class="bi bi-pencil-square me-1"></i>Edit</a></div>';
            })
            ->addColumn('role', function ($data) {
                if (count($data->getRoleNames()) != 0) {
                    $roles = $data->getRoleNames()->toArray();
                    $text = '<span class="text-dark fw-medium">' . implode(', ', $roles) . '</span>';
                    if (in_array('Staff LHU', $roles)) {
                        $countJobs = $data->jobs ? count($data->jobs) : 0;
                        $text .= ' <span class="text-primary cursor-pointer small font-monospace fw-semibold ms-1" data-id="' . $data->user_hash . '" onclick="showTugas(this)">(' . $countJobs . ' Tugas)</span>';
                    }
                    return $text;
                } else {
                    return '';
                }
            })
            ->addColumn('satuankerja', function ($data) {
                if (is_array($data->satuankerja_id)) {
                    $satuankerja = $data->satuankerja_id;
                } else {
                    $satuankerja = $data->satuankerja_id ? [$data->satuankerja_id] : null;
                }
                $names = [];
                if ($satuankerja) {
                    foreach ($satuankerja as $item) {
                        $sk = Satuan_kerja::find($item);
                        if ($sk) $names[] = $sk->name;
                    }
                }
                return count($names) > 0 ? '<span class="text-secondary small fw-medium">' . implode(', ', $names) . '</span>' : '';
            })
            ->addColumn('tugas', function ($data) {
                $tugasNames = [];
                if ($data->jobs) {
                    foreach ($data->jobs as $item) {
                        $job = Master_jobs::find($item);
                        if ($job) $tugasNames[] = $job->name;
                    }
                }
                return count($tugasNames) > 0 ? '<span class="text-secondary small fw-medium">' . implode(', ', $tugasNames) . '</span>' : '-';
            })
            ->rawColumns(['name', 'email', 'action', 'role', 'satuankerja', 'tugas'])
            ->make(true);
    }

    public function getById(string $id)
    {
        DB::beginTransaction();
        try {
            $user = User::find(decryptor($id));
            if (!is_array($user->satuankerja_id)) {
                $user->satuankerja_id = [$user->satuankerja_id];
            }
            $user->satuankerja = Satuan_kerja::whereIn('id', $user->satuankerja_id)->get();

            if ($user->jobs) {
                $user->jobs = array_map(function ($item) {
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

        // Gunakan file edit.blade.php sebagai form yang dinamis
        return view('pages.management.users.form', $data);
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
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
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

        // Decrypt setiap role ID ke bentuk array integer untuk syncRoles
        $idsRole = array_map(function ($value) {
            return (int) decryptor($value);
        }, $role);
        $rolesName = Role::whereIn('id', $idsRole)->pluck('name')->toArray();

        if (in_array('Staff LHU', $rolesName)) {
            $paramsUser['jobs'] = array_map(function ($item) {
                return (int) decryptor($item);
            }, $request->tugas_lhu);
        }

        $user = User::factory()->create($paramsUser);

        $user->syncRoles($idsRole);

        $avatarId = null;
        if ($request->file('avatar')) {
            $avatarMedia = app(\App\Http\Controllers\MediaController::class)->upload($request->file('avatar'), 'avatar');
            $avatarMedia->store();
            $avatarId = $avatarMedia->getIdMedia();
        }

        if ($user) {
            $profile = Profile::create([
                'user_id' => $user->id,
                'avatar' => $avatarId,
                'nik' => $request->nik,
                'no_hp' => $request->no_telepon,
                'jenis_kelamin' => $request->jenis_kelamin,
                'alamat' => $request->alamat
            ]);

            if (in_array('Pelanggan', $rolesName)) {
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
        if (!is_array($d_user->satuankerja_id)) {
            $d_user->satuankerja_id = [$d_user->satuankerja_id];
        }
        $d_user->satuankerja = Satuan_kerja::whereIn('id', $d_user->satuankerja_id)->get();

        if ($d_user->jobs) {
            $d_user->jobs = array_map(function ($item) {
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

        return view('pages.management.users.form', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $arrValidator = [
            'name' => ['required', 'string', 'max:255'],
            'nik' => ['required'],
            'jenis_kelamin' => ['required'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
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

        // Decrypt setiap role ID ke dalam bentuk array integer
        $idsRole = array_map(function ($value) {
            return (int) decryptor($value);
        }, $role);

        // syncRoles secara otomatis menghapus role lama dan memasukkan array role baru
        $d_user->syncRoles($idsRole);

        if ($request->tugas_lhu) {
            $d_user->jobs = array_map(function ($item) {
                return (int) decryptor($item);
            }, $request->tugas_lhu);
        }
        $d_user->satuankerja_id = array_map(function ($item) {
            return (int) decryptor($item);
        }, $request->satuanKerja);
        $d_user->update();

        $avatarId = $profile ? $profile->avatar : null;
        if ($request->file('avatar')) {
            if ($profile && $profile->avatar && is_numeric($profile->avatar)) {
                app(\App\Http\Controllers\MediaController::class)->update($request->file('avatar'), $profile->avatar);
                $avatarId = $profile->avatar;
            } else {
                if ($profile && $profile->avatar && !is_numeric($profile->avatar)) {
                    if (Storage::exists('public/images/avatar/' . $profile->avatar)) {
                        Storage::delete('public/images/avatar/' . $profile->avatar);
                    }
                }
                $avatarMedia = app(\App\Http\Controllers\MediaController::class)->upload($request->file('avatar'), 'avatar');
                $avatarMedia->store();
                $avatarId = $avatarMedia->getIdMedia();
            }
        }

        if ($profile) {
            $profile->nik = $request->nik;
            $profile->no_hp = $request->no_telepon;
            $profile->jenis_kelamin = $request->jenis_kelamin;
            $profile->alamat = $request->alamat;
            $profile->avatar = $avatarId;
            $profile->update();
        } else {
            profile::create(array(
                'user_id' => $idHash,
                'nik' => $request->nik,
                'no_hp' => $request->no_telepon,
                'jenis_kelamin' => $request->jenis_kelamin,
                'alamat' => $request->alamat,
                'avatar' => $avatarId
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
            Profile::where('user_id', $user->id)->get()->each->delete();

            // menghapus semua role yang terikat
            // $user->getRoleNames()->each(function ($roleName) use ($user) {
            //     $user->removeRole($roleName);
            // });

            // menghapus user request
            // Users_request::where('id_user', $user->id)->get()->each->delete();
            $user->delete();

            DB::commit();

            return $this->output(array('msg' => 'User Behasil dihapus'));
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function getPermisionInRole(Request $request)
    {
        $role = $request->role;
        $permission = [];

        if ($role) {
            $idsRole = array_map(function ($value) {
                return (int) decryptor($value);
            }, $role);

            $roles = Role::with('permissions')->whereIn('id', $idsRole)->get();

            // ambil semua permission yang terikat dengan semua role yang dipilih
            $permission = $roles->flatMap(function ($r) {
                return $r->permissions->pluck('name');
            })->unique()->values()->toArray();
        }


        return $this->output($permission);
    }
}
