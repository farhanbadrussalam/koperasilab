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
use App\Http\Controllers\LabController;
use App\Http\Controllers\OtorisasiController;
use App\Http\Controllers\PetugasLayananController;
use App\Http\Controllers\JobsController;
use App\Http\Controllers\PelaksanaKontrakController;
// use App\Http\Controllers\KeuanganController;
use App\Http\Controllers\ManagerController;

use App\Http\Controllers\Permohonan\PelangganController;

use App\Http\Controllers\Staff\PermohonanController;
use App\Http\Controllers\Staff\KeuanganController;
use App\Http\Controllers\Staff\StaffController;
use App\Http\Controllers\Staff\PenyeliaController;

use App\Http\Controllers\Manager\ManagerPengajuanController;

use App\Http\Controllers\Report\SuratTugas;
use App\Http\Controllers\Report\Kwitansi;
use App\Http\Controllers\Report\Invoice;
use App\Http\Controllers\Report\TandaTerima;

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
Route::get('petugasLayanan/v/{id}', [PetugasLayananController::class, 'verifikasiPetugas']);

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
            Route::get('/pengiriman', 'indexPengiriman')->name('staff.pengiriman');
            Route::get('/pengiriman/permohonan', 'indexPengirimanPermohonan')->name('staff.pengiriman.permohonan');
            Route::get('/pengiriman/permohonan/kirim/{idPermohonan}', 'buatOrderPengiriman')->name('staff.pengiriman.permohonan.kirim');
            Route::get('/pengiriman/tambah', 'buatCustomPengiriman')->name('staff.pengiriman.tambah');
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


    // Route::middleware(['permission:User.management'])->group(function () {
    // Route::group(function () {
        Route::resource('users', UserController::class);
        Route::get('getData', [UserController::class, 'getData'])->name('users.getData');

        Route::resource('permission', PermissionController::class);
        Route::get('getDataPermission', [PermissionController::class, 'getData'])->name('permission.getData');

        Route::resource('roles', RolesController::class);
        Route::get('getDataRoles', [RolesController::class, 'getData'])->name('roles.getData');
    // });

    // Route::middleware(['permission:Layananjasa'])->group(function () {
    // Route::group(function () {
        Route::resource('layananJasa', LayananJasaController::class);
        Route::get('getDataLayananJasa', [LayananJasaController::class, 'getData'])->name('layananJasa.getData');
    // });

    // Route::middleware(['permission:Permohonan'])->group(function () {
    // Route::group(function () {
        // Route::resource('permohonan', PermohonanController::class);
        // Route::get('getDataPermohonan', [PermohonanController::class, 'getData'])->name('permohonan.getData');
        // Route::get('getDTListLayanan', [PermohonanController::class, 'getDTListLayanan'])->name('permohonan.getDTListLayanan');
        // Route::get('permohonan/payment/{idPermohonan}', [PermohonanController::class, 'payment'])->name('permohonan.payment');
        // Route::get('permohonan/create/layanan/{idJadwal}', [PermohonanController::class, 'pilihLayanan'])->name('permohonan.create.layanan');
        // Route::get('listLayanan', [LayananJasaController::class, 'listLayanan'])->name('layananJasa.listLayanan');
    // });

    // Route::middleware(['permission:Penjadwalan'])->group(function () {
    // Route::group(function () {
        Route::resource('jadwal', JadwalController::class);
        Route::get('getDataJadwal', [JadwalController::class, 'getData'])->name('jadwal.getData');
        Route::get('getPetugasDT', [JadwalController::class, 'getPetugasDT'])->name('jadwal.getPetugasDT');
        Route::post('updatePenugasan', [JadwalController::class, 'confirm'])->name('jadwal.updatePetugas');
    // });

    // Route::middleware(['permission:Management.Lab'])->group(function () {
    // Route::group(function () {
        Route::resource('lab', LabController::class);
        Route::get('getDataLab', [LabController::class, 'getData'])->name('lab.getData');
    // });

    // Route::middleware(['permission:Management.Otorisasi'])->group(function () {
    // Route::group(function () {
        Route::resource('otorisasi', OtorisasiController::class);
        Route::get('getDataOtorisasi', [OtorisasiController::class, 'getData'])->name('otorisasi.getData');
    // });

    // Route::middleware(['permission:Keuangan'])->group(function () {
    // Route::group(function () {
        Route::get('keuangan', [KeuanganController::class, 'index'])->name('keuangan.index');
        Route::post('sendKIP', [KeuanganController::class, 'sendKIP'])->name('keuangan.send');
    // });

    // Route::middleware(['permission:kiplhu'])->group(function () {
        Route::get('manager/lhukip', [ManagerController::class, 'index'])->name('manager.lhukip.index');
        Route::get('manager/getData', [ManagerController::class, 'getData'])->name('manager.getData');
    // });

    Route::get('jobs/frontdesk', [JobsController::class, 'indexFrontdesk'])->name('jobs.frontdesk.index');
    Route::get('jobs/pelaksana', [JobsController::class, 'indexPelaksanaKontrak'])->name('jobs.pelaksana.index');
    Route::get('jobs/penyelia', [JobsController::class, 'indexPenyelia'])->name('jobs.penyelia.index');
    Route::get('jobs/pelaksanaLab', [JobsController::class, 'indexPelaksanaLab'])->name('jobs.pelaksanaLab.index');
    Route::get('jobs/getData', [JobsController::class, 'getData'])->name('jobs.getData');
    Route::get('jobs/getDataPelaksanaLab', [JobsController::class, 'getDataPelaksanaLab'])->name('jobs.getDataPelaksanaLab');
    Route::get('jobs/getDataLhu', [JobsController::class, 'getDataLhu'])->name('jobs.getDataLhu');


    Route::resource('petugasLayanan', PetugasLayananController::class);
    Route::get('getDataPetugas', [PetugasLayananController::class, 'getData'])->name('petugasLayanan.getData');

    Route::resource('userProfile', ProfileController::class);//->middleware(['permission:Biodata.pribadi']);
    Route::resource('userPerusahaan', userPerusahaanController::class);//->middleware(['permission:Biodata.perusahaan']);

    Route::get('/sendNotif', [NotifController::class, 'notif'])->name('notif.send');

    Route::prefix('laporan')->group(function() {
        Route::get('/suratTugas/{id}', [SuratTugas::class, 'index'])->name('laporan.suratTugas');
        Route::get('/kwitansi/{id}', [Kwitansi::class, 'index'])->name('laporan.kwitansi');
        Route::get('/invoice/{id}', [Invoice::class, 'index'])->name('laporan.invoice');
        Route::get('/tandaterima/{id}', [TandaTerima::class, 'index'])->name('laporan.tandaterima');
    });
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
