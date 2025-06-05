<?php

namespace App\Http\Controllers;

use Illuminate\Support\Arr;
use App\Traits\RestApi;

use App\Models\profile;
use App\Models\user;
use App\Models\Master_alamat;
use Illuminate\Http\Request;

use Auth;
use DB;

class ProfileController extends Controller
{
    use RestApi;
    /**
     * Display a listing of the resource.
     */

    public function __construct() {
        $this->module = 'profile-pelanggan';
    }

    public function index()
    {
        $profile = user::with('profile', 'perusahaan', 'perusahaan.alamat')->where('id', decryptor(Auth::user()->user_hash))->first();

        if($profile) {
            $isPassword = $profile->password == null ? false : true;
        }
        $data = [
            'title' => 'Profile',
            'module' => $this->module,
            'profile' => $profile,
            'isPassword' => $isPassword
        ];

        return view('pages.profile.index', $data);
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(profile $profile)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(profile $profile)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateAlamat(Request $request, $id)
    {
        try {
            $profile = Master_alamat::findOrFail(decryptor($id));

            $params = array();

            $status = $request->has('status') ? $request->status : 99;
            $alamat = $request->has('alamat') ? $request->alamat : false;
            $kodePos = $request->has('kodePos') ? $request->kodePos : false;

            $status != 99 && $params['status'] = $status;
            $alamat && $params['alamat'] = $alamat;
            $kodePos && $params['kodePos'] = $kodePos;

            $profile->update($params);

            $result = array(
                'status' => 'change'
            );
            return $this->output($result);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(profile $profile)
    {
        //
    }
}
