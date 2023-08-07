<?php

namespace App\Http\Controllers;

use App\Models\profile;
use App\Models\user;
use Illuminate\Http\Request;
use Auth;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['token'] = generateToken();
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
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'nik' => ['required'],
            'email' => ['required', 'email'],
            'telepon' => ['required'],
            'jenis_kelamin' => ['required']
        ]);

        $profile = profile::findOrFail($id);
        $user = user::findOrFail($profile->user_id);

        $dataProfil = array(
            'nik' => $request->nik,
            'alamat' => $request->alamat,
            'no_hp' => unmask($request->telepon),
            'jenis_kelamin' => $request->jenis_kelamin,
        );
        $dataUser = array(
            'name' => $request->name,
            'email' => $request->email
        );

        $profile->update($dataProfil);
        $user->update($dataUser);

        return redirect()->route('userProfile.index')->with('success', 'Berhasil di update');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(profile $profile)
    {
        //
    }
}
