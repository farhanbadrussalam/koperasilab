<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\JadwalAPI;
use App\Http\Controllers\API\NotifikasiController;
use App\Http\Controllers\API\PermohonanController;
use App\Http\Controllers\API\LayananjasaAPI;
use App\Http\Controllers\API\OtorisasiAPI;
use App\Http\Controllers\API\PetugasLayananAPI;
use App\Http\Controllers\API\AssetsAPI;

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


Route::middleware('auth:sanctum')->group(function() {
    Route::get('/logout', [AuthController::class, 'logout']);

    Route::get('/getPegawai', [LayananjasaAPI::class, 'getPegawai']);
    Route::delete('/deletePegawai', [LayananjasaAPI::class, 'delete']);

    Route::get('/getNotifikasi', [NotifikasiController::class, 'getNotifikasi']);
    Route::get('/setNotifikasi', [NotifikasiController::class, 'setNotifikasi']);


    Route::resource('jadwal_api', JadwalAPI::class);
    Route::get('/getJadwal', [JadwalAPI::class, 'getJadwal']);
    Route::get('/getJadwalPetugas', [JadwalAPI::class, 'getJadwalPetugas']);
    Route::post('/updatePenugasan', [JadwalAPI::class, 'confirm']);
    Route::delete('/deleteJadwal/{id}', [JadwalAPI::class, 'destroy']);

    Route::resource('permohonan_api', PermohonanController::class);
    Route::post('/updatePermohonan', [PermohonanController::class, 'confirm']);

    Route::prefix('otorisasi')->group(function () {
        Route::get('/getOtorisasi', [OtorisasiAPI::class, 'getOtorisasi']);
    });

    Route::prefix('petugas')->group(function () {
        Route::get('/getPetugas', [PetugasLayananAPI::class, 'getPetugas']);
    });

});
