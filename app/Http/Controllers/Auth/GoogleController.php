<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Profile;
use App\Models\Perusahaan;
use App\Models\Master_media;
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
            'email' => $googleUser->email,
        ], [
            'name' => $googleUser->name,
            'google_id' => $googleUser->id,
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
            $filehash = 'avatar_'.$googleUser->id . $extension;
            $fileori = 'avatar_'.$googleUser->name . $extension;

            // Simpan gambar ke direktori yang ditentukan
            // mengecek apakah filehash sudah da atau belum
            if(!Storage::disk('public')->exists('images/avatar/' . $filehash)){
                Storage::disk('public')->put('images/avatar/' . $filehash, $imageContent);
            }

            $media = Master_media::updateOrCreate(['file_hash' => $filehash], [
                'file_hash' => $filehash,
                'file_ori' => $fileori,
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

        Auth::login($user);

        return redirect('/userProfile');
    }
}
