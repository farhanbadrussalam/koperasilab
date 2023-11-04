<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Profile;
use App\Models\Perusahaan;
use App\Models\tbl_media;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Storage;

class GoogleController extends Controller
{
    public function redirect(){
        return Socialite::driver('google')->redirect();
    }

    public function callback(){
        $googleUser = Socialite::driver('google')->user();

        $user = User::updateOrCreate([
            'google_id' => $googleUser->id,
        ], [
            'name' => $googleUser->name,
            'email' => $googleUser->email,
            'email_verified_at' => Carbon::now()->format('Y-m-d H:i:s')
        ])->assignRole('pelanggan');

        if($user){
            $urlEksternal = $googleUser->avatar;

            // Ambil konten gambar dari URL
            $imageContent = file_get_contents($urlEksternal);

            // Mengambil extension
            $path = parse_url($urlEksternal, PHP_URL_PATH);
            $extension = pathinfo($path, PATHINFO_EXTENSION);

            if($extension){
                $extension = '.'.$extension;
            }
            // Generate unique file name
            $filename = 'avatar_'.$googleUser->id . $extension;

            // Simpan gambar ke direktori yang ditentukan
            Storage::disk('public')->put('images/avatar/' . $filename, $imageContent);

            $media = tbl_media::create([
                'file_hash' => $filename,
                'file_ori' => $filename,
                'file_size' => 0,
                'file_type' => 'image/jpeg',
                'file_path' => 'images/avatar',
                'status' => 1
            ]);

            $profile = Profile::updateOrCreate([
                'user_id' => $user->id
            ], [
                'avatar' => $media->id
            ]);
        }

        $perusahaan = Perusahaan::updateOrCreate([
            'user_id' => $user->id
        ]);

        Auth::login($user);

        return redirect()->route('userProfile.index');
    }
}
