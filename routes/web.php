<?php

use App\Http\Controllers\auth\GoogleController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\userPerusahaanController;
use App\Http\Controllers\NotifController;

use App\Http\Controllers\Permohonan\PelangganController;
use App\Http\Controllers\Staff\StaffController;
use App\Http\Controllers\Manager\ManagerPengajuanController;

// Management
use App\Http\Controllers\Management\UserController;
use App\Http\Controllers\Management\PermissionController;
use App\Http\Controllers\Management\RolesController;
use App\Http\Controllers\Management\TldController;
use App\Http\Controllers\Management\RadiasiController;
use App\Http\Controllers\Management\PenggunaController;

use App\Http\Controllers\ReportController;

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
Auth::routes();
Route::get('/', [HomeController::class, 'login']);

Route::middleware(['auth', 'verified'])->group(function() {
    Route::get('home', [HomeController::class, 'index'])->name('home');

    // NEW ROUTE
    Route::prefix('permohonan')->group(function () {
        Route::controller(PelangganController::class)->group(function () {
            Route::get('/pengajuan', 'indexPengajuan')->name('permohonan.pengajuan');
            Route::get('/pengajuan/tambah', 'tambahPengajuan')->name('permohonan.pengajuan.tambah');
            Route::get('/pengajuan/edit/{id_permohonan}', 'editPengajuan')->name('permohonan.pengajuan.edit');
            
            Route::get('/dikembalikan', 'indexPengembalian')->name('permohonan.dikembalikan');

            Route::get('/pembayaran', 'indexPembayaran')->name('permohonan.pembayaran');
            Route::get('/pembayaran/bayar/{idKeuangan}', 'bayarInvoicePembayaran')->name('permohonan.pembayaran.bayar');
            
            Route::get('/pengiriman', 'indexPengiriman')->name('permohonan.pengiriman');

            Route::get('/kontrak', 'indexKontrak')->name('permohonan.kontrak');
            Route::get('/kontrak/e/{idKontrak}/{idPeriode}', 'evaluasiKontrak')->name('permohonan.kontrak.evaluasi');
        });
    });

    Route::prefix('staff')->group(function () {
        Route::controller(StaffController::class)->group(function() {
            Route::get('/keuangan', 'indexKeuangan')->name('staff.keuangan');
            Route::get('/permohonan', 'indexPermohonan')->name('staff.permohonan');
            Route::get('/permohonan/verifikasi/{idPermohonan}', 'verifikasiPermohonan')->name('staff.permohonan.verifikasi');
            Route::get('/penyelia', 'indexPenyelia')->name('staff.penyelia');
            Route::get('/penyelia/surat_tugas/c/{idPenyelia}', 'createSuratTugas')->name('staff.penyelia.create.surat_tugas');
            Route::get('/penyelia/surat_tugas/e/{idPenyelia}', 'createSuratTugas')->name('staff.penyelia.update.surat_tugas');
            Route::get('/penyelia/surat_tugas/s/{idPenyelia}', 'createSuratTugas')->name('staff.penyelia.update.surat_tugas');

            Route::get('/lhu', 'indexLhu')->name('staff.lhu');
            Route::get('/lhu/petugas', 'indexPetugas')->name('staff.lhu.petugas');

            Route::get('/pengiriman', 'indexPengiriman')->name('staff.pengiriman');
            Route::get('/pengiriman/permohonan', 'indexPengirimanPermohonan')->name('staff.pengiriman.permohonan');
            Route::get('/pengiriman/permohonan/kirim/{idPermohonan}', 'buatOrderPengiriman')->name('staff.pengiriman.permohonan.kirim');
            Route::get('/pengiriman/permohonan/kirim/{idKontrak}/{periode}', 'buatOrderPengiriman')->name('staff.pengiriman.permohonan.kirim.kontrak');
            Route::get('/pengiriman/tambah', 'buatCustomPengiriman')->name('staff.pengiriman.tambah');

            Route::get('/perusahaan', 'indexPerusahaan')->name('staff.perusahaan');
        });
    });

    Route::prefix('manager')->group(function () {
        Route::controller(ManagerPengajuanController::class)->group(function () {
            Route::get('/pengajuan', 'index')->name('manager.pengajuan');
            Route::get('/surat_tugas', 'indexSuratTugas')->name('manager.surat_tugas');
        });
        Route::controller(StaffController::class)->group(function() {
            Route::get('/surat_tugas/v/{idPenyelia}', 'createSuratTugas')->name('manager.surat_tugas.verif');
            Route::get('/surat_tugas/s/{idPenyelia}', 'createSuratTugas')->name('manager.surat_tugas.verif');
        });
    });

    Route::prefix('profile')->group(function () {
        Route::controller(ProfileController::class)->group(function () {
            Route::post('/update/{idAlamat}', 'updateAlamat')->name('profile.update');
        });
    });

    Route::prefix('laporan')->group(function() {
        Route::controller(ReportController::class)->group(function () {
            Route::get('/surattugas/{id}', 'suratTugas')->name('laporan.surattugas');
            Route::get('/kwitansi/{id}', 'kwitansi')->name('laporan.kwitansi');
            Route::get('/invoice/{id}', 'invoice')->name('laporan.invoice');
            Route::get('/tandaterima/{id}', 'tandaTerima')->name('laporan.tandaterima');
            Route::get('/surpeng/{id}/{periode}', 'suratPengantar')->name('laporan.surpeng');
            Route::get('/perjanjian/{id}', 'perjanjian')->name('laporan.perjanjian');
            Route::get('/label/{id}', 'label')->name('laporan.label');
        });
    });


    // Route::middleware(['permission:User.management'])->group(function () {
    // Route::group(function () {
    Route::prefix('management')->group(function () {
        Route::resource('users', UserController::class);
        Route::get('getData', [UserController::class, 'getData'])->name('users.getData');
        Route::get('getById/{id}', [UserController::class, 'getById'])->name('users.getById');

        Route::resource('permission', PermissionController::class);
        Route::get('getDataPermission', [PermissionController::class, 'getData'])->name('permission.getData');

        Route::resource('roles', RolesController::class);
        Route::get('getDataRoles', [RolesController::class, 'getData'])->name('roles.getData');

        Route::resource('tld', TldController::class);
        Route::get('getDataTld', [TldController::class, 'getData'])->name('tld.getData');

        Route::resource('radiasi', RadiasiController::class);
        Route::get('getDataRadiasi', [RadiasiController::class, 'getData'])->name('radiasi.getData');

        Route::resource('userpengguna', PenggunaController::class);
        Route::get('getDataPengguna', [PenggunaController::class, 'getData'])->name('pengguna.getData');
    });
    // });

    // Route::middleware(['permission:Layananjasa'])->group(function () {
    // Route::group(function () {
    // });

    // Route::middleware(['permission:Penjadwalan'])->group(function () {
    // Route::group(function () {
    // });

    // Route::middleware(['permission:Management.Lab'])->group(function () {
    // Route::group(function () {
    // });

    Route::resource('userProfile', ProfileController::class);//->middleware(['permission:Biodata.pribadi']);
    Route::resource('userPerusahaan', userPerusahaanController::class);//->middleware(['permission:Biodata.perusahaan']);

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
