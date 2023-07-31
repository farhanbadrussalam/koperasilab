<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\LayananjasaController;
use App\Http\Controllers\API\JadwalController;
use App\Http\Controllers\API\NotifikasiController;

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

Route::post('/login', [AuthController::class, 'index']);


Route::middleware('auth:sanctum')->group(function() {
    Route::get('/getPegawai', [LayananjasaController::class, 'getPegawai']);
    Route::delete('/deletePegawai', [LayananjasaController::class, 'delete']);

    Route::get('/getNotifikasi', [NotifikasiController::class, 'getNotifikasi']);
    Route::get('/setNotifikasi', [NotifikasiController::class, 'setNotifikasi']);


    Route::resource('jadwal', JadwalController::class);
    Route::delete('/deleteJadwal/{id}', [JadwalController::class, 'destroy']);
});
