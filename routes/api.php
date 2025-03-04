<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\KeuanganAPI;
use App\Http\Controllers\API\NotifikasiController;
use App\Http\Controllers\API\PermohonanAPI;
use App\Http\Controllers\API\LayananjasaAPI;
use App\Http\Controllers\API\PetugasLayananAPI;
use App\Http\Controllers\API\SendMailAPI;
use App\Http\Controllers\API\ManagerAPI;
use App\Http\Controllers\API\PenyeliaAPI;
use App\Http\Controllers\API\PengirimanAPI;
use App\Http\Controllers\API\ProfileAPI;
use App\Http\Controllers\API\KontrakAPI;
use App\Http\Controllers\API\TldAPI;
use App\Http\Controllers\API\FilterAPI;

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
        Route::get('/getPengajuanById/{id}', 'getPengajuanById');
        Route::post('/verifikasi/cek', 'verifPermohonan');
        Route::post('/verifikasi/tambahTandaterima', 'tambahTandaterima');
        Route::delete('/destroyTandaterima/{idPermohonan}', 'destroyTandaterima');
        Route::post('/uploadLhuZeroCek', 'uploadLhuZeroCek');
        Route::delete('/destroyLhuZero/{idPermohonan}/{idMedia}', 'destroyLhuZero');
    });

    Route::prefix("keuangan")->controller(KeuanganAPI::class)->group(function () {
        Route::post('/action', 'keuanganAction');
        Route::get('/listKeuangan', 'listKeuangan');
        Route::get('/getKeuangan/{idKeuangan}', 'getKeuangan');
        Route::post('/uploadFaktur', 'uploadFaktur');
        Route::post('/uploadBuktiBayar', 'uploadBuktiBayar');
        Route::post('/uploadBuktiPph', 'uploadBuktiBayarPph');
        Route::delete('/destroyFaktur/{idKeuangan}/{idMedia}', 'destroyFaktur');
        Route::delete('/destroyBuktiBayar/{idKeuangan}/{idMedia}', 'destroyBuktiBayar');
        Route::delete('/destroyBuktiPph/{idKeuangan}/{idMedia}', 'destroyBuktiBayarPph');
    });

    Route::prefix("pengiriman")->controller(PengirimanAPI::class)->group(function () {
        Route::post('/action', 'actionPengiriman');
        Route::get('/list', 'listPengiriman');
        Route::get('/listPermohonan', 'listPermohonan');
        Route::get('/getById/{pengiriman_hash}', 'getPengirimanById');
        Route::get('/getPermohonan', 'getPermohonan');
        Route::delete('/destroy/{pengiriman_hash}', 'destroy');
    });
    
    Route::prefix("kontrak")->controller(KontrakAPI::class)->group(function () {
        Route::post('/action', 'actionKontrak');
        Route::get('/list', 'listKontrak');
        Route::get('/getById/{kontrak_hash}', 'getKontrakById');
        Route::get('/search', 'searchKontrak');
        // Route::delete('/destroy/{kontrak_hash}', 'destroy');
    });

    Route::prefix("penyelia")->controller(PenyeliaAPI::class)->group(function () {
        Route::post('/action', 'actionPenyelia');
        Route::get('/list', 'listPenyelia');
        Route::get('/listPetugas', 'getListPetugas');
        Route::get('/getById/{idPenyelia}', 'getPenyeliaById');
        Route::get('/getPenyeliaMapById/{idPenyeliaMap}', 'getPenyeliaMapById');
        Route::post('/uploadDokumenLhu', 'uploadDokumenLhu');
        Route::delete('/destroyDokumenLhu/{idPenyelia}/{idMedia}', 'destroyDokumenLhu');
        Route::delete('/remove/{idPenyelia}', 'removeSuratTugas');
    });

    Route::prefix("manager")->controller(ManagerAPI::class)->group(function () {
        Route::get('/listManager', 'listManager');
    });

    Route::prefix("profile")->controller(ProfileAPI::class)->group(function () {
        Route::post('/action', 'actionProfile');
        Route::post('/action/alamat', 'actionAlamat');
        Route::post('/action/perusahaan', 'actionPerusahaan');
        Route::get('/getPerusahaan/{kode}', 'getPerusahaanByKode');
    });

    Route::prefix("filter")->controller(FilterAPI::class)->group(function () {
        Route::get('/getJenisTld', 'getJenisTld');
        Route::get('/getStatus', 'getStatus');
        Route::get('/getJenisLayanan', 'getJenisLayanan');
    });

    Route::prefix('petugas')->controller(PetugasLayananAPI::class)->group(function () {
        // NEW API
        Route::get('/list', 'listPetugas');

        // OLD API
        Route::get('/getPetugas', 'getPetugas');
    });

    Route::prefix('email')->group(function(){
        Route::post('/verifikasiPetugas', [SendMailAPI::class, 'verifikasiPetugas']);
    });

    Route::prefix('tld')->group(function(){
        Route::get('/searchTldNotUsed', [TldApi::class, 'searchTldNotUsed']);
        Route::get('/searchTld', [TldApi::class, 'searchTld']);
        Route::post('/action', [TldApi::class, 'action']);
    });

});
