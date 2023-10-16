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
use App\Http\Controllers\NotifController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PermohonanController;
use App\Http\Controllers\LabController;
use App\Http\Controllers\OtorisasiController;
use App\Http\Controllers\PetugasLayananController;
use App\Http\Controllers\FrontdeskController;
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

        Route::resource('permission', PermissionController::class);
        Route::get('getDataPermission', [PermissionController::class, 'getData'])->name('permission.getData');

        Route::resource('roles', RolesController::class);
        Route::get('getDataRoles', [RolesController::class, 'getData'])->name('roles.getData');
    });

    Route::middleware(['permission:Layananjasa'])->group(function () {
        Route::resource('layananJasa', LayananJasaController::class);
        Route::get('getDataLayananJasa', [LayananJasaController::class, 'getData'])->name('layananJasa.getData');
    });

    Route::middleware(['permission:Permohonan'])->group(function () {
        Route::resource('permohonan', PermohonanController::class);
        Route::get('getDataPermohonan', [PermohonanController::class, 'getData'])->name('permohonan.getData');
        Route::get('getDTListLayanan', [PermohonanController::class, 'getDTListLayanan'])->name('permohonan.getDTListLayanan');
        Route::get('permohonan/create/layanan/{idJadwal}', [PermohonanController::class, 'pilihLayanan'])->name('permohonan.create.layanan');
    });

    Route::middleware(['permission:Penjadwalan'])->group(function () {
        Route::resource('jadwal', JadwalController::class);
        Route::get('getDataJadwal', [JadwalController::class, 'getData'])->name('jadwal.getData');
        Route::get('getPetugasDT', [JadwalController::class, 'getPetugasDT'])->name('jadwal.getPetugasDT');
        Route::post('updatePenugasan', [JadwalController::class, 'confirm'])->name('jadwal.updatePetugas');
    });

    Route::middleware(['permission:Management.Lab'])->group(function () {
        Route::resource('lab', LabController::class);
        Route::get('getDataLab', [LabController::class, 'getData'])->name('lab.getData');
    });

    Route::middleware(['permission:Management.Otorisasi'])->group(function () {
        Route::resource('otorisasi', OtorisasiController::class);
        Route::get('getDataOtorisasi', [OtorisasiController::class, 'getData'])->name('otorisasi.getData');
    });

    Route::get('frontdesk', [FrontdeskController::class, 'index'])->name('frontdesk.index');
    Route::get('frontdesk/getData', [FrontdeskController::class, 'getData'])->name('frontdesk.getData');

    Route::resource('petugasLayanan', PetugasLayananController::class);
    Route::get('getDataPetugas', [PetugasLayananController::class, 'getData'])->name('petugasLayanan.getData');

    Route::resource('userProfile', ProfileController::class)->middleware(['permission:Biodata.pribadi']);
    Route::resource('userPerusahaan', userPerusahaanController::class)->middleware(['permission:Biodata.perusahaan']);

    Route::get('/sendNotif', [NotifController::class, 'notif'])->name('notif.send');
});

Route::get('/auth/google/redirect', [GoogleController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');

Route::post('password/email', [
    'as' => 'laravel.password.email',
    'uses' => 'App\Http\Controllers\Auth\ForgotPasswordController@sendResetLinkEmail'
]);

Route::get('password/reset', [
    'as' => 'laravel.password.request',
    'uses' => 'App\Http\Controllers\Auth\ForgotPasswordController@showLinkRequestForm'
]);

Route::get('password/reset/{token}', [
    'as' => 'laravel.password.reset',
    'uses' => 'App\Http\Controllers\Auth\ResetPasswordController@showResetForm'
]);

Route::get('password/reset', [
    'as' => 'laravel.password.update',
    'uses' => 'App\Http\Controllers\Auth\ResetPasswordController@reset'
]);

Route::post('password/reset', [
    'as' => 'laravel.password.update.post',
    'uses' => 'App\Http\Controllers\Auth\ResetPasswordController@reset'
]);

Route::get('password/confirm', [
    'as' => 'laravel.password.confirm',
    'uses' => 'App\Http\Controllers\Auth\ConfirmPasswordController@showConfirmForm'
]);
