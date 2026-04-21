<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\dashboardSkeletonController;
use App\Http\Controllers\DashboardWidgetController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\userPerusahaanController;
use App\Http\Controllers\NotifController;
use App\Http\Controllers\LogController;

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
use App\Http\Controllers\Management\DocumentController;

use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;

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
            Route::get('/pengajuan', 'indexPengajuan')->middleware(['permission:Permohonan/pengajuan'])->name('permohonan.pengajuan');
            Route::get('/pengajuan/tambah', 'tambahPengajuan')->name('permohonan.pengajuan.tambah');
            Route::get('/pengajuan/edit/{id_permohonan}', 'editPengajuan')->name('permohonan.pengajuan.edit');

            Route::get('/dikembalikan', 'indexPengembalian')->name('permohonan.dikembalikan');

            Route::get('/pembayaran', 'indexPembayaran')->name('permohonan.pembayaran');
            Route::get('/pembayaran/bayar/{idKeuangan}', 'bayarInvoicePembayaran')->name('permohonan.pembayaran.bayar');

            Route::get('/pengiriman', 'indexPengiriman')->name('permohonan.pengiriman');

            Route::get('/kontrak', 'indexKontrak')->middleware('permission:Kontrak')->name('permohonan.kontrak');
            Route::get('/kontrak/e/{idKontrak}/{idPeriode}', 'evaluasiKontrak')->name('permohonan.kontrak.evaluasi');
            Route::get('/kontrak/a/{idKontrak}', 'adendumKontrak')->name('permohonan.kontrak.adendum');
        });
    });

    Route::prefix('staff')->group(function () {
        Route::controller(StaffController::class)->group(function() {
            Route::get('/keuangan', 'indexKeuangan')->middleware(['permission:Staff/keuangan'])->name('staff.keuangan');
            Route::get('/permohonan', 'indexPermohonan')->middleware(['permission:Staff/permohonan'])->name('staff.permohonan');
            Route::get('/permohonan/verifikasi/{idPermohonan}', 'verifikasiPermohonan')->name('staff.permohonan.verifikasi');
            Route::get('/penyelia', 'indexPenyelia')->middleware(['permission:Staff/penyelia'])->name('staff.penyelia');
            Route::get('/penyelia/surat_tugas/c/{idPenyelia}', 'createSuratTugas')->name('staff.penyelia.create.surat_tugas');
            Route::get('/penyelia/surat_tugas/e/{idPenyelia}', 'createSuratTugas')->name('staff.penyelia.update.surat_tugas');
            Route::get('/penyelia/surat_tugas/s/{idPenyelia}', 'createSuratTugas')->name('staff.penyelia.show.surat_tugas');

            Route::get('/lhu', 'indexLhu')->middleware(['permission:Staff/lhu'])->name('staff.lhu');
            Route::get('/lhu/petugas', 'indexPetugas')->middleware(['permission:Staff/lhu/petugas'])->name('staff.lhu.petugas');

            Route::get('/pengiriman', 'indexPengiriman')->name('staff.pengiriman');
            Route::get('/pengiriman/permohonan', 'indexPengirimanPermohonan')->name('staff.pengiriman.permohonan');
            Route::get('/pengiriman/permohonan/kirim/{idPermohonan}', 'buatOrderPengiriman')->name('staff.pengiriman.permohonan.kirim');
            Route::get('/pengiriman/permohonan/kirim/{idKontrak}/{periode}', 'buatOrderPengiriman')->name('staff.pengiriman.permohonan.kirim.kontrak');
            Route::get('/pengiriman/pengembalian/{idKontrak}', 'buatOrderPengembalian');
            Route::get('/pengiriman/tambah', 'buatCustomPengiriman')->name('staff.pengiriman.tambah');

            Route::get('/perusahaan', 'indexPerusahaan')->middleware(['permission:Staff/perusahaan'])->name('staff.perusahaan');
            Route::get('/jenis/pembayaran', 'indexJenisPembayaran')->name('staff.jenis.pembayaran');
        });

        Route::get('/lhu/petugas/getData', [UserController::class, 'getData'])->name('staff.lhu.petugas.getData');
    });

    Route::prefix('manager')->group(function () {
        Route::controller(ManagerPengajuanController::class)->group(function () {
            Route::get('/pengajuan', 'index')->middleware(['permission:Manager/keuangan'])->name('manager.pengajuan');
            Route::get('/surat_tugas', 'indexSuratTugas')->middleware(['permission:Manager/pengajuan'])->name('manager.surat_tugas');
        });
        Route::controller(StaffController::class)->group(function() {
            Route::get('/surat_tugas/v/{idPenyelia}', 'createSuratTugas')->name('manager.surat_tugas.verif');
            Route::get('/surat_tugas/s/{idPenyelia}', 'createSuratTugas')->name('manager.surat_tugas.show');
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
            Route::get('/kontrak/{id}', 'kontrak')->name('laporan.kontrak');
            Route::get('/label/{id}', 'label')->name('laporan.label');
            Route::get('/SuratPengujian/{id}', 'SuratPengujian')->name('laporan.SuratPengujian');
            Route::get('/KontrakPengujian/{id}', 'KontrakPengujian')->name('laporan.KontrakPengujian');
            Route::get('/adendum/{id}', 'adendum')->name('laporan.adendum');
        });
    });

    Route::prefix('management')->middleware(['permission:Management|Tld'])->group(function () {
        Route::resource('users', UserController::class)->middleware('role:Super Admin');
        Route::get('getData', [UserController::class, 'getData'])->name('users.getData');
        Route::get('getById/{id}', [UserController::class, 'getById'])->name('users.getById');

        Route::resource('permission', PermissionController::class)->middleware('role:Super Admin');
        Route::get('getDataPermission', [PermissionController::class, 'getData'])->name('permission.getData');

        Route::resource('roles', RolesController::class)->middleware('role:Super Admin');
        Route::get('getDataRoles', [RolesController::class, 'getData'])->name('roles.getData');

        Route::resource('tld', TldController::class);
        Route::get('getDataTld', [TldController::class, 'getData'])->name('tld.getData');
        Route::get('searchTld', [TldController::class, 'searchTld'])->name('tld.search');

        Route::resource('radiasi', RadiasiController::class)->middleware('role:Super Admin');
        Route::get('getDataRadiasi', [RadiasiController::class, 'getData'])->name('radiasi.getData');

        Route::resource('userpengguna', PenggunaController::class);
        Route::get('getDataPengguna', [PenggunaController::class, 'getData'])->name('pengguna.getData');

        Route::resource('document', DocumentController::class);
        Route::post('document/{id}', [DocumentController::class, 'update']);
    });

    Route::prefix('logs')->group(function () {
        Route::controller(LogController::class)->group(function () {
            Route::get('/proses', 'getLogProses')->name('logs.proses');
        });
    });

    Route::resource('userProfile', ProfileController::class);
    Route::resource('userPerusahaan', userPerusahaanController::class);

    Route::get('/sendNotif', [NotifController::class, 'notif'])->name('notif.send');
    Route::get('/getNotif', [NotifController::class, 'latestNotification'])->name('notif.getNotif');
    Route::get('/markAllAsRead', [NotifController::class, 'markAllAsRead'])->name('notif.markAllAsRead');
    Route::post('/deleteNotification', [NotifController::class, 'deleteNotification'])->name('notif.deleteNotification');


    Route::prefix('settings')->group(function () {
        Route::controller(SettingsController::class)->group(function () {
            Route::post('notifications/realtime', 'toggleRealtime')->name('settings.realtime.toggle');
        });
    });

    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::prefix('widgets')->name('widgets.')->group(function () {
            Route::controller(DashboardWidgetController::class)->group(function () {
                Route::get('/summary-cards', 'summaryCards')->name('summary-cards');
                Route::get('/statistics-layanan', 'statisticsLayanan')->name('statistics-layanan');
                Route::get('/delivery-stats', 'deliveryStats')->name('delivery-stats');
                Route::get('/jobs-penyelia', 'jobsPenyelia')->name('jobs-penyelia');
                Route::get('/monitorPenyeliaan', 'monitorPenyeliaan')->name('monitor-penyeliaan');
                Route::get('/myJobsList', 'myJobsList')->name('myJobsList');
                Route::get('/finance-charts', 'financeCharts')->name('finance-charts');
                Route::get('/finance-inv-active', 'financeInvActive')->name('finance-inv-active');
                Route::get('/finance-chart-service', 'financeChartService')->name('finance-chart-service');
                Route::get('/finance-side', 'financeSide')->name('finance-side');

                Route::get('/track-search', 'trackSearch')->name('track-search');
            });
        });
        Route::prefix('skeleton')->name('skeleton.')->group(function () {
            Route::controller(dashboardSkeletonController::class)->group(function () {
                Route::get('/summary-cards', 'summaryCards')->name('summary-cards');
                Route::get('/statistics-layanan', 'statisticsLayanan')->name('statistics-layanan');
                Route::get('/delivery-stats', 'deliveryStats')->name('delivery-stats');
                Route::get('/jobs-penyelia', 'jobsPenyelia')->name('jobs-penyelia');
                Route::get('/monitorPenyeliaan', 'monitorPenyeliaan')->name('monitor-penyeliaan');
                Route::get('/myJobsList', 'myJobsList')->name('myJobsList');
                Route::get('/finance-chart-service', 'financeChartService')->name('finance-chart-service');
            });
        });
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
