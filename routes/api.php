<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\JadwalAPI;
use App\Http\Controllers\API\NotifikasiController;
use App\Http\Controllers\API\PermohonanAPI;
use App\Http\Controllers\API\LayananjasaAPI;
use App\Http\Controllers\API\OtorisasiAPI;
use App\Http\Controllers\API\PetugasLayananAPI;
use App\Http\Controllers\API\AssetsAPI;
use App\Http\Controllers\API\LhuAPI;
use App\Http\Controllers\API\SendMailAPI;

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

Route::post('/login', [AuthController::class, 'login']);
Route::post('/encryptor', [AuthController::class, 'encryptor']);

Route::middleware('auth:sanctum')->group(function() {
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

    // Route::resource('permohonan', PermohonanAPI::class);

    Route::prefix("layananjasa")->controller(LayananjasaAPI::class)->group(function() {
        Route::get('/list', 'listLayananjasa');
        Route::get('/getLayanan/{id}', 'getLayananjasa');
        Route::post('/addLayanan', 'addLayananjasa');
        Route::post('/updateLayanan', 'updateLayananjasa');
        Route::delete('/deleteLayanan/{id}', 'deleteLayananjasa');
    });

    Route::prefix("permohonan")->controller(PermohonanAPI::class)->group(function () {
        Route::get('/show/{id}', 'show');
        Route::get('/list', 'listPermohonan');
        Route::delete('/destroy/{id}', 'destroy');
        Route::post('/update/{id}', 'update');
        Route::post('/verifikasi_fd', 'verifikasi_fd');
        Route::post('/verifikasi_kontrak', 'verifikasi_kontrak');
        Route::post('/sendSuratTugas', 'sendSuratTugas');
        Route::post('/addPermohonan', 'addPermohonan');
    });

    Route::prefix('otorisasi')->group(function () {
        Route::get('/getOtorisasi', [OtorisasiAPI::class, 'getOtorisasi']);
    });

    Route::prefix('petugas')->controller(PetugasLayananAPI::class)->group(function () {
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
