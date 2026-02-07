<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Perusahaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\RestApi;

use App\Models\Profile;
use App\Models\User;

class AuthController extends Controller
{
    use RestApi;
    /**
     * Display a listing of the resource.
     */
    public function login(Request $request)
    {
        $credential = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        if(Auth::attempt($credential)){
            $user = Auth::user();
            $token = $user->createToken('api-token')->plainTextToken;
            return response()->json(['token' => $token], 200);
        }else{
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
    }

    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();
        // Auth::logout();

        return response()->json(['message' => 'Logged out successfully'], 200);
    }

    public function encryptor(Request $request){
        $allid = $request->id ? $request->id : [];

        $tmp = array();

        foreach ($allid as $key => $id) {
            array_push($tmp, encryptor($id));
        }

        return $this->output($tmp);
    }

    public function decryptor(Request $request){
        $allid = $request->id ? $request->id : [];

        $tmp = array();

        foreach ($allid as $key => $id) {
            array_push($tmp, decryptor($id));
        }

        return $this->output($tmp);
    }

    public function search_akun(Request $request){
        $search = unmask($request->search);
        $data = Profile::where('nik', $search)->first();

        if($data == null){
            return $this->output(['message' => 'Data not found'], 'Fail', 200);
        }

        return $this->output($data);
    }

    public function checkEmail(Request $request){
        $email = $request->email;
        $jenis = $request->jenis;

        if($jenis == 'user'){
            $data = User::where('email', $email)->first();
        } else {
            $data = Perusahaan::where('email', $email)->first();
        }

        if($data == null){
            return $this->output(['message' => 'Data not found'], 'Fail', 200);
        }

        return $this->output($data);
    }

    public function checkNik(Request $request){
        $nik = $request->nik;
        $data = Profile::where('nik', $nik)->first();

        if($data == null){
            return $this->output(['message' => 'Data not found'], 'Fail', 200);
        }

        return $this->output($data);
    }
}
