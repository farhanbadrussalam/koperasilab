@echo off
title Koperasi launcher

:: 1. Pindah ke folder project
cd /d "%~dp0"

:MENU
cls
echo =============================================
echo        CONTROL PANEL: Koperasi LAB
echo =============================================
echo.
echo  [R1] Run Dev
echo  [R2] Run Prod
echo.
echo =================================================
echo  PILIH GROUP ATAU TEST YANG AKAN DIJALANKAN
echo =================================================
echo.
echo  --- GROUP ---
echo [G1] Jalankan SEMUA test Evaluasi Sewa
echo [G2] Jalankan Group create-permohonan
echo [G3] Jalankan Group Keuangan
echo [G4] Jalankan Group Penyelia
echo [G5] Jalankan Group Pengiriman
echo.
echo  --- INDIVIDUAL ---
echo [1] test_pelanggan_create_permohonan
echo [2] test_admin_verifikasi_permohonan
echo [3] test_buat_invoice
echo [4] test_keuangan_ttd
echo [5] test_bayar_keuangan
echo [6] test_verifikasi_pembayaran
echo [7] test_create_surattugas
echo [8] test_ttd_manager_surattugas
echo [9] test_ttd_manager_surpeng
echo [10] test_progress_lab
echo [11] test_pengiriman_send
echo [12] test_pengiriman_pelanggan
echo.
echo [0] Keluar
echo =================================================
echo.
set choice=
set /p choice=Pilih kode (misal: G1 atau 1), lalu tekan ENTER:

:: Logika Pilihan
if /i "%choice%"=="R1" goto START_ALL
if /i "%choice%"=="R2" goto START_ALL_PROD

:: Set argumen dan judul untuk test, lalu lompat ke label :RUN_DUSK
if /i "%choice%"=="G1" set DUSK_ARGS=--group=evaluasi-sewa & set DUSK_TITLE=Dusk: Evaluasi Sewa & goto RUN_DUSK
if /i "%choice%"=="G2" set DUSK_ARGS=--group=create-permohonan & set DUSK_TITLE=Dusk: Create Permohonan & goto RUN_DUSK
if /i "%choice%"=="G3" set DUSK_ARGS=--group=keuangan & set DUSK_TITLE=Dusk: Keuangan & goto RUN_DUSK
if /i "%choice%"=="G4" set DUSK_ARGS=--group=penyelia & set DUSK_TITLE=Dusk: Penyelia & goto RUN_DUSK
if /i "%choice%"=="G5" set DUSK_ARGS=--group=pengiriman & set DUSK_TITLE=Dusk: Pengiriman & goto RUN_DUSK

if "%choice%"=="1" set DUSK_ARGS=--filter=EvaluasiSewaTest::test_pelanggan_create_permohonan & set DUSK_TITLE=Dusk: Pelanggan Create Permohonan & goto RUN_DUSK
if "%choice%"=="2" set DUSK_ARGS=--filter=EvaluasiSewaTest::test_admin_verifikasi_permohonan & set DUSK_TITLE=Dusk: Admin Verifikasi Permohonan & goto RUN_DUSK
if "%choice%"=="3" set DUSK_ARGS=--filter=EvaluasiSewaTest::test_buat_invoice & set DUSK_TITLE=Dusk: Buat Invoice & goto RUN_DUSK
if "%choice%"=="4" set DUSK_ARGS=--filter=EvaluasiSewaTest::test_keuangan_ttd & set DUSK_TITLE=Dusk: Keuangan TTD & goto RUN_DUSK
if "%choice%"=="5" set DUSK_ARGS=--filter=EvaluasiSewaTest::test_bayar_keuangan & set DUSK_TITLE=Dusk: Bayar Keuangan & goto RUN_DUSK
if "%choice%"=="6" set DUSK_ARGS=--filter=EvaluasiSewaTest::test_verifikasi_pembayaran & set DUSK_TITLE=Dusk: Verifikasi Pembayaran & goto RUN_DUSK
if "%choice%"=="7" set DUSK_ARGS=--filter=EvaluasiSewaTest::test_create_surattugas & set DUSK_TITLE=Dusk: Create Surattugas & goto RUN_DUSK
if "%choice%"=="8" set DUSK_ARGS=--filter=EvaluasiSewaTest::test_ttd_manager_surattugas & set DUSK_TITLE=Dusk: TTD Manager Surattugas & goto RUN_DUSK
if "%choice%"=="9" set DUSK_ARGS=--filter=EvaluasiSewaTest::test_ttd_manager_surpeng & set DUSK_TITLE=Dusk: TTD Manager Surpeng & goto RUN_DUSK
if "%choice%"=="10" set DUSK_ARGS=--filter=EvaluasiSewaTest::test_progress_lab & set DUSK_TITLE=Dusk: Progress Lab & goto RUN_DUSK
if "%choice%"=="11" set DUSK_ARGS=--filter=EvaluasiSewaTest::test_pengiriman_send & set DUSK_TITLE=Dusk: Pengiriman Send & goto RUN_DUSK
if "%choice%"=="12" set DUSK_ARGS=--filter=EvaluasiSewaTest::test_pengiriman_pelanggan & set DUSK_TITLE=Dusk: Pengiriman Pelanggan & goto RUN_DUSK
if "%choice%"=="0" goto KELUAR

echo.
echo =================================================
echo Pilihan tidak valid. Tekan tombol apa saja untuk kembali ke menu...
echo =================================================
pause > nul
goto MENU

:START_ALL
echo.
echo [+] Menghapus cache lama...
call php artisan optimize:clear
echo [+] Membuat ulang package discovery...
call php artisan package:discover
echo.
echo [+] Membuka Koperasi DEV di tab baru...
wt new-tab --title "Vite" --startingDirectory "%CD%" cmd /k "npm run dev -- --host"
wt new-tab --title "Koperasi Dev" --startingDirectory "%CD%" cmd /k "php artisan serve --host=0.0.0.0 --port=8000"
wt new-tab --title "Queue Worker" --startingDirectory "%CD%" cmd /k "php artisan queue:listen"
goto MENU

:: Label ini diperbaiki dari START_ALL menjadi START_ALL_PROD
:START_ALL_PROD
echo.
echo [+] Menyiapkan build aset...
call npm run build
echo [+] Menghapus cache lama...
call php artisan optimize:clear
echo [+] Membuat ulang package discovery...
call php artisan package:discover
echo.
echo [+] Membuka Koperasi PROD di tab baru...
wt new-tab --title "Koperasi PROD" --startingDirectory "%CD%" cmd /k "php artisan serve --port=8000"
wt new-tab --title "Queue Worker PROD" --startingDirectory "%CD%" cmd /k "php artisan queue:listen"
goto MENU

:RUN_DUSK
:: Opsi ini membuka tab baru untuk menjalankan perintah Dusk yang dipilih
wt new-tab --title "%DUSK_TITLE%" --startingDirectory "%CD%" cmd /k "php artisan dusk %DUSK_ARGS%"
goto MENU

:KELUAR
exit