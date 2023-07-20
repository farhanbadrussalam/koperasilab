<?php

use App\Http\Controllers\auth\GoogleController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\userPerusahaanController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\LayananJasaController;
use App\Http\Controllers\JadwalController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});
Auth::routes();

Route::middleware(['auth', 'verified'])->group(function() {
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    Route::middleware(['permission:User.management'])->group(function () {
        Route::resource('users', UserController::class);
        Route::get('getData', [UserController::class, 'getData'])->name('users.getData');

        Route::resource('roles', RolesController::class);
        Route::get('getDataRoles', [RolesController::class, 'getData'])->name('roles.getData');
    });

    Route::middleware(['permission:Management.layanan.jasa'])->group(function () {
        Route::resource('layananJasa', LayananJasaController::class);
        Route::get('getDataLayananJasa', [LayananJasaController::class, 'getData'])->name('layananJasa.getData');
    });

    Route::resource('jadwal', JadwalController::class);

    Route::resource('userProfile', ProfileController::class)->middleware(['permission:Biodata.pribadi']);
    Route::resource('userPerusahaan', userPerusahaanController::class)->middleware(['permission:Biodata.perusahaan']);
});

Route::get('/auth/google/redirect', [GoogleController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');

