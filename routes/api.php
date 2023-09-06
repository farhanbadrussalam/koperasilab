<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\LayananjasaController;
use App\Http\Controllers\API\JadwalController;
use App\Http\Controllers\API\NotifikasiController;
use App\Http\Controllers\API\PermohonanController;
use App\Http\Controllers\API\OtorisasiAPI;
use App\Http\Controllers\API\PetugasLayananAPI;

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

    Route::get('/getPegawai', [LayananjasaController::class, 'getPegawai']);
    Route::delete('/deletePegawai', [LayananjasaController::class, 'delete']);

    Route::get('/getNotifikasi', [NotifikasiController::class, 'getNotifikasi']);
    Route::get('/setNotifikasi', [NotifikasiController::class, 'setNotifikasi']);


    Route::resource('jadwal', JadwalController::class);
    Route::get('/getJadwal', [JadwalController::class, 'getJadwal']);
    Route::post('/updatePenugasan', [JadwalController::class, 'confirm']);
    Route::delete('/deleteJadwal/{id}', [JadwalController::class, 'destroy']);

    Route::resource('permohonan', PermohonanController::class);
    Route::post('/updatePermohonan', [PermohonanController::class, 'confirm']);

    Route::prefix('otorisasi')->group(function () {
        Route::get('/getOtorisasi', [OtorisasiAPI::class, 'getOtorisasi']);
    });

    Route::prefix('petugas')->group(function () {
        Route::get('/getPetugas', [PetugasLayananAPI::class, 'getPetugas']);
    });
});
