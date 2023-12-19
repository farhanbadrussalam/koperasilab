<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\RestApi;

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
}
