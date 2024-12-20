<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\JadwalAPI;
use App\Http\Controllers\API\KeuanganAPI;
use App\Http\Controllers\API\NotifikasiController;
use App\Http\Controllers\API\PermohonanAPI;
use App\Http\Controllers\API\LayananjasaAPI;
use App\Http\Controllers\API\OtorisasiAPI;
use App\Http\Controllers\API\PetugasLayananAPI;
use App\Http\Controllers\API\AssetsAPI;
use App\Http\Controllers\API\LhuAPI;
use App\Http\Controllers\API\SendMailAPI;
use App\Http\Controllers\API\ManagerAPI;
use App\Http\Controllers\API\PenyeliaAPI;
use App\Http\Controllers\API\PengirimanAPI;
use App\Http\Controllers\API\ProfileAPI;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::prefix('v1/')->group(function() {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/encryptor', [AuthController::class, 'encryptor']);
    Route::get('/profile/list/perusahaan', [ProfileAPI::class, 'getListPerusahaan']);
});

Route::middleware('auth:sanctum')->prefix('v1/')->group(function() {
    Route::get('/logout', [AuthController::class, 'logout']);

    Route::get('/getPegawai', [LayananjasaAPI::class, 'getPegawai']);
    Route::delete('/deletePegawai', [LayananjasaAPI::class, 'delete']);

    Route::get('/getNotifikasi', [NotifikasiController::class, 'getNotifikasi']);
    Route::get('/setNotifikasi', [NotifikasiController::class, 'setNotifikasi']);

    Route::prefix("jadwal")->controller(jadwalAPI::class)->group(function() {
        Route::post('/addJadwal', 'store');
    });

    Route::resource('jadwal_api', JadwalAPI::class);
    Route::get('/getJadwal', [JadwalAPI::class, 'getJadwal']);
    Route::get('/getJadwalPetugas', [JadwalAPI::class, 'getJadwalPetugas']);
    Route::post('/updatePenugasan', [JadwalAPI::class, 'confirm']);
    Route::delete('/deleteJadwal/{id}', [JadwalAPI::class, 'destroy']);

    Route::prefix("layananjasa")->controller(LayananjasaAPI::class)->group(function() {
        Route::get('/list', 'listLayananjasa');
        Route::get('/getLayanan/{id}', 'getLayananjasa');
        Route::post('/addLayanan', 'addLayananjasa');
        Route::post('/updateLayanan', 'updateLayananjasa');
        Route::delete('/deleteLayanan/{id}', 'deleteLayananjasa');
    });

    Route::prefix("permohonan")->controller(PermohonanAPI::class)->group(function () {
        Route::delete('/destroyPermohonan/{id}', 'destroyPermohonan');
        Route::get('/listPengajuan', 'listPengajuan');
        Route::post('/tambahPengajuan', 'tambahPengajuan');
        Route::post('/tambahPengguna', 'tambahPengguna');
        Route::delete('/destroyPengguna/{idPengguna}', 'destroyPengguna');
        Route::get('/listPengguna', 'listPengguna');
        Route::get('/getChildJenisLayanan/{idParent}', 'getChildJenisLayanan');
        Route::get('/getJenisTld/{idJenisLayanan}', 'getJenisTld');
        Route::get('/getPrice', 'getPrice');
        Route::post('/verifikasi/cek', 'verifPermohonan');
    });

    Route::prefix("keuangan")->controller(KeuanganAPI::class)->group(function () {
        Route::post('/action', 'keuanganAction');
        Route::get('/listKeuangan', 'listKeuangan');
        Route::get('/getKeuangan/{idKeuangan}', 'getKeuangan');
        Route::post('/uploadFaktur', 'uploadFaktur');
        Route::delete('/destroyFaktur/{idKeuangan}/{idMedia}', 'destroyFaktur');
    });

    Route::prefix("pengiriman")->controller(PengirimanAPI::class)->group(function () {
        Route::post('/action', 'actionPengiriman');
        Route::get('/list', 'listPengiriman');
        Route::get('/listPermohonan', 'listPermohonan');
        Route::get('/getById/{pengiriman_hash}', 'getPengirimanById');
        Route::get('/getPermohonan', 'getPermohonan');
        Route::delete('/destroy/{pengiriman_hash}', 'destroy');
    });

    Route::prefix("penyelia")->controller(PenyeliaAPI::class)->group(function () {
        Route::post('/action', 'actionPenyelia');
        Route::get('/list', 'listPenyelia');
        Route::get('/listPetugas', 'getListPetugas');
        Route::delete('/remove/{idPenyelia}', 'removeSuratTugas');
    });

    Route::prefix("manager")->controller(ManagerAPI::class)->group(function () {
        Route::get('/listManager', 'listManager');
    });

    Route::prefix("profile")->controller(ProfileAPI::class)->group(function () {
        Route::post('/action', 'actionProfile');
        Route::post('/action/alamat', 'actionAlamat');
    });

    Route::prefix('otorisasi')->group(function () {
        Route::get('/getOtorisasi', [OtorisasiAPI::class, 'getOtorisasi']);
    });

    Route::prefix('petugas')->controller(PetugasLayananAPI::class)->group(function () {
        // NEW API
        Route::get('/list', 'listPetugas');

        // OLD API
        Route::get('/getPetugas', 'getPetugas');
        Route::get('/getJadwalPetugas/{jadwal_hash}', 'getJadwalPetugas');
        Route::get('/search', 'searchData');
        Route::post('/storeJadwalPetugas', 'storeJadwalPetugas');
        Route::post('/updateJadwalPetugas', 'updateJadwalPetugas');
        Route::delete('/destroyJadwalPetugas/{jadwalPetugas_hash}', 'destroyJadwalPetugas');
    });

    Route::prefix('lhu')->group(function () {
        Route::get('/getDokumenLHU/{id_lhu}', [LhuAPI::class, 'getDokumenLHU']);
        Route::get('/getDokumenKIP/{id_kip}', [LhuAPI::class, 'getDokumenKIP']);
        Route::post('/sendDokumen', [LhuAPI::class, 'sendDokumen']);
        Route::post('/validasiLHU', [LhuAPI::class, 'validasiLHU']);
        Route::post('/validasiKIP', [LhuAPI::class, 'validasiKIP']);
        Route::post('/sendToPelanggan', [LhuAPI::class, 'sendToPelanggan']);
        Route::get('/getPertanyaan', [LhuAPI::class, 'ambilPertanyaanLhu']);
    });

    Route::prefix('kip')->group(function(){
        Route::post('/sendPayment', [LhuAPI::class, 'sendPayment']);
    });

    Route::prefix('email')->group(function(){
        Route::post('/verifikasiPetugas', [SendMailAPI::class, 'verifikasiPetugas']);
    });

});
