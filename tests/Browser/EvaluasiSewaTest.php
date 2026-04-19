<?php

namespace Tests\Browser;

use App\Models\Keuangan;
use App\Models\Pengiriman;
use App\Models\Penyelia;
use App\Models\Penyelia_map;
// use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\Permohonan;
use App\Models\Setting_layanan;
use App\Models\User;
class EvaluasiSewaTest extends DuskTestCase
{
    private $pelanggan = "pelanggan@gmail.com";
    private $keuangan = "arsyaka.riselvi@gmail.com";
    private $managerKeuangan = "gaji.soraya@gmail.com";
    private $admin = "adipmuhammad30@gmail.com";
    private $penyelia = "firlinadia@gmail.com";
    private $managerPenyelia = "alex080590.arb@gmail.com";
    private $password = "lab@1234%";

    private $idLayananKontrak = "9k9avCgeHa1qfTj78022Aw";
    private $idLayananSewa = "wVytkcL66wSLKdnwaKS77Q";
    private $idTld = "9k9avCgeHa1qfTj78022Aw";

    private $waitingTime = 99999;

    /**
     * Helper untuk login berdasarkan environment.
     * Jika local menggunakan loginAs, jika tidak akan login manual.
     */
    private function loginUser(Browser $browser, $user)
    {
        if (app()->environment('local')) {
            return $browser->loginAs($user);
        }

        return $browser->visit('/login')
            ->type('email', $user->email)
            ->type('password', $this->password) // Sesuaikan dengan password default user di database/seeder
            ->press('Masuk');
    }

    /**
     * @group create-permohonan
     * @group evaluasi-sewa
     */
    public function test_pelanggan_create_permohonan(): void
    {
        $user = User::where('email', $this->pelanggan)->first();
        $this->browse(function (Browser $browser) use ($user) {
            $this->loginUser($browser, $user)
                    // 2. CREATE PERMOHONAN (KONTRAK - SEWA)
                    ->visit('/permohonan/pengajuan')
                    ->clickLink('Buat pengajuan')
                    ->assertSee('Jenis layanan')
                    ->screenshot('02_halaman_pengajuan')

                    ->select('jenis_layanan', $this->idLayananKontrak) // Pilih Kontrak
                    ->waitFor("#jenis_layanan_2 option[value='{$this->idLayananSewa}']", $this->waitingTime) // Tunggu opsi Sewa muncul
                    ->select('jenis_layanan_2', $this->idLayananSewa) // Pilih Sewa
                    ->press('Buat form')

                    // Tunggu form dinamis muncul, lalu isi
                    ->waitFor('#form-inputan.d-block')
                    ->assertSee('Jenis TLD')
                    ->select('jenis_tld', $this->idTld)
                    ->press('Select periode')
                    ->waitForText("Pilih periode", $this->waitingTime);

            // Mengisi periode menggunakan perulangan agar lebih rapi dan dinamis
            $periodes = [
                ['index' => 0, 'date' => '2026-03-01'],
                ['index' => 1, 'date' => '2026-06-01', 'text' => 'Periode 2'],
                ['index' => 2, 'date' => '2026-09-01', 'text' => 'Periode 3'],
                ['index' => 3, 'date' => '2026-12-01', 'text' => 'Periode 4'],
            ];

            foreach ($periodes as $i => $periode) {
                // Untuk periode kedua dan seterusnya, klik tombol "Tambah periode" terlebih dahulu
                if ($i > 0) {
                    $browser->press("Tambah periode")
                            ->waitForText($periode['text'], $this->waitingTime);
                }
                // Menggunakan script untuk mengisi tanggal pada flatpickr
                $browser->script("document.querySelector('#periode_start_{$periode['index']}')._flatpickr.setDate('{$periode['date']}', true);");
            }

            $browser->click("#btn-simpan-periode-1") // Simpan periode
                    ->waitForText("Apa anda yakin ingin menyimpan data ?", $this->waitingTime)
                    ->press("Iya")
                    ->screenshot('03_form_periode_terisi');

            $browser->clickLink('Tambah')
                    ->waitForText("Tambahkan Pengguna", $this->waitingTime)
                    ->assertVisible("tbody tr:first-child")
                    ->click("tbody tr:first-child button") // Klik tombol 'Pilih' pada baris pertama
                    ->screenshot('04_pilih_pengguna')
                    ->waitUntilMissing(".modal-backdrop", $this->waitingTime) // Tunggu modal hilang
                    ->waitUntilMissingText('Tidak ada pengguna') // Tunggu status pengguna muncul di tabel
                    ->waitUntilMissingText("Tidak ada kontrol") // Tunggu status kontrol muncul di tabel
                    ->press("Simpan pengajuan")
                    ->waitForText("Apa kamu yakin?", $this->waitingTime)
                    ->press("Yes, proceed!")
                    // Tunggu proses simpan selesai dengan menunggu redirect
                    ->waitForLocation('/permohonan/pengajuan')
                    ->assertPathIs('/permohonan/pengajuan')
                    // ->assertSee('Pengajuan berhasil disimpan') // Assertion ideal: cek pesan sukses
                    ->screenshot('05_simpan_pengajuan');

                    // 3. LOGOUT
            $browser->click('#userDropdown')
                    ->waitForLink('Logout') // Ganti pause dengan wait yang lebih andal
                    ->clickLink('Logout')
                    ->assertPathIs('/')
                    ->assertSee('NuklindoLab Koperasi JKRL') // Pastikan kembali ke halaman login
                    ->screenshot('06_logout_success');
        });
    }

    /**
     * @group create-permohonan
     * @group evaluasi-sewa
     */
    public function test_admin_verifikasi_permohonan(): void
    {
        $user = User::where('email', $this->admin)->first(); // Admin

        $this->browse(function (Browser $browser) use ($user) {
            $permohonan = Permohonan::with('tandaterima')
                            ->where('status', 1)
                            ->orderBy('id_permohonan', 'desc')
                            ->first();

            $tglSelesai = date('Y-m-d', strtotime('2 weeks'));
            if($permohonan){
                $isAdendumNotZerocek = false;
                if($permohonan->tipe_kontrak == 'adendum') {
                    if($permohonan->is_zerocek == 0){
                        $isAdendumNotZerocek = true;
                    }
                }
                $this->loginUser($browser, $user)
                        // 2. VERIFIKASI PERMOHONAN
                        ->visit('/staff/permohonan')
                        ->waitUntilMissing("#list-placeholder", $this->waitingTime)
                        ->visit("/staff/permohonan/verifikasi/{$permohonan->permohonan_hash}")
                        ->waitForLocation("/staff/permohonan/verifikasi/{$permohonan->permohonan_hash}")
                        ->click('#frontdeskVal')
                        ->script("document.querySelector('#tanggal-selesai')._flatpickr.setDate('$tglSelesai', true);");

                if (count($permohonan->tandaterima) == 0 && $isAdendumNotZerocek == false) {
                    $browser->click("#btn-tandaterima")
                            ->waitForText("List Tanda Terima", $this->waitingTime)
                            ->click("#answer_1_baik")
                            ->click("#answer_2_baik");

                    for ($i = 3; $i <= 11; $i++) {
                        $browser->type("answer_{$i}", "Ok");
                    }

                    $browser->screenshot('tambah_tandaterima')
                            ->press("Simpan")
                            ->waitForText("Data berhasil disimpan", $this->waitingTime)
                            ->press("OK")
                            ->waitUntilMissing(".modal-backdrop", $this->waitingTime);
                }

                $browser->press("Lengkap")
                        ->waitForText("Apakah data sudah lengkap?", $this->waitingTime)
                        ->press("Iya")
                        ->screenshot('verifikasi_permohonan')
                        ->waitForLocation('/staff/permohonan', $this->waitingTime);

                        // 3. LOGOUT
                $browser->click('#userDropdown')
                        ->waitForLink('Logout') // Ganti pause dengan wait yang lebih andal
                        ->clickLink('Logout')
                        ->assertPathIs('/')
                        ->assertSee('NuklindoLab Koperasi JKRL') // Pastikan kembali ke halaman login
                        ->screenshot('logout_success');
            } else {
                $browser->dump();
            }
        });
    }

    /**
     * @group keuangan
     * @group evaluasi-sewa
     */
    public function test_buat_invoice(): void
    {
        $pathFilePdf = base_path('tests/Browser/file_dummy/filePdf.pdf');
        $user = User::where('email', $this->keuangan)->first(); // Keuangan
        $this->browse(function (Browser $browser) use ($user, $pathFilePdf) {
            $keuangan = Keuangan::where('status', 1)->orderBy('id_keuangan', 'desc')->first();
            $this->loginUser($browser, $user)
                    ->visit('staff/keuangan')
                    ->waitUntilMissing('#list-placeholder', $this->waitingTime)
                    ->within('div[data-id="'.$keuangan->keuangan_hash.'"]', function ($row) {
                        $row->press('Buat invoice');
                    })
                    ->waitForText("Manajemen Invoice")
                    ->screenshot("keuangan_permohonan")
                    ->click("#checkPpn")->click("#checkPph")
                    ->select("#methode-pembayaran-select", "bXcdJ1FcdxXxzIwN-43wdw")
                    ->press("Simpan")
                    ->waitForText("Apa anda yakin ingin membuat invoice ?")
                    ->press("Iya")
                    ->waitUntilMissing(".modal-backdrop", $this->waitingTime);

            // Upload Faktur Pajak

            $browser->waitUntilMissing('#list-placeholder', $this->waitingTime)
                    ->click('button[onclick="switchLoadTab(6)"]')
                    ->waitUntilMissing("#list-placeholder", $this->waitingTime)
                    ->within('div[data-id="'.$keuangan->keuangan_hash.'"]', function ($row) {
                        $row->press('Upload Faktur');
                    })
                    ->waitForText("Manajemen Invoice")
                    ->attach('uploadFile', $pathFilePdf)
                    ->press("Tambah")
                    ->waitUntilMissingText("Tidak ada file yang diupload", $this->waitingTime)
                    ->press("Simpan")
                    ->waitForText("Apa faktur sudah benar ?")
                    ->press("Iya")
                    ->waitUntilMissing(".modal-backdrop", $this->waitingTime);

                // 3. LOGOUT
            $browser->click('#userDropdown')
                    ->waitForLink('Logout') // Ganti pause dengan wait yang lebih andal
                    ->clickLink('Logout')
                    ->assertPathIs('/')
                    ->assertSee('NuklindoLab Koperasi JKRL') // Pastikan kembali ke halaman login
                    ->screenshot('logout_success');
        });
    }

    /**
     * @group keuangan
     * @group evaluasi-sewa
     */
    public function test_keuangan_ttd(): void
    {
        // TTD MANAGER
        $user = User::where('email', $this->managerKeuangan)->first(); // Keuangan
        $this->browse(function (Browser $browser) use ($user) {
            $keuangan = Keuangan::where('status', 2)->orderBy('id_keuangan', 'desc')->first();
            $this->loginUser($browser, $user)
                    ->visit('manager/pengajuan')
                    ->waitUntilMissing('#list-placeholder', $this->waitingTime)
                    ->within('div[data-id="'.$keuangan->keuangan_hash.'"]', function ($row) {
                        $row->press('verifikasi');
                    })
                    ->waitForText("Manajemen Invoice")
                    ->screenshot("manager_verifikasi")
                    ->click("#invoice-validation-manager")
                    ->press("Setujui")
                    ->waitForText("Apa invoice sudah benar ?")
                    ->press("Iya")
                    ->waitUntilMissing(".modal-backdrop", $this->waitingTime);

            // LOGOUT
            $browser->click('#userDropdown')
                    ->waitForLink('Logout') // Ganti pause dengan wait yang lebih andal
                    ->clickLink('Logout')
                    ->assertPathIs('/')
                    ->assertSee('NuklindoLab Koperasi JKRL') // Pastikan kembali ke halaman login
                    ->screenshot('logout_success');
        });
    }

    /**
     * @group keuangan
     * @group evaluasi-sewa
     */
    public function test_bayar_keuangan(): void
    {
        // bayar keuangan
        $pathFilePdf = base_path('tests/Browser/file_dummy/filePdf.pdf');
        $pathFileImage = base_path('tests/Browser/file_dummy/fileImage.jpg');
        $user = User::where('email', $this->pelanggan)->first(); // Pelanggan
        $this->browse(function (Browser $browser) use ($user, $pathFileImage, $pathFilePdf) {
            $keuangan = Keuangan::where('status', 3)->orderBy('id_keuangan', 'desc')->first();
            $this->loginUser($browser, $user)
                    ->visit('permohonan/pembayaran')
                    ->waitUntilMissing('#list-placeholder', $this->waitingTime)
                    ->within('div[data-id="'.$keuangan->keuangan_hash.'"]', function ($row) {
                        $row->clickLink('Bayar');
                    })
                    ->waitForLocation("/permohonan/pembayaran/bayar/{$keuangan->keuangan_hash}")
                    ->screenshot("bayar_keuangan")
                    ->within('#uploadBuktiBayar', function ($row) use ($pathFileImage) {
                        $row->attach('uploadFile', $pathFileImage)
                            ->press('Tambah')
                            ->waitUntilMissingText("Tidak ada file yang diupload", 5);
                    })
                    ->within('#uploadBuktiBayarPph', function ($row) use ($pathFilePdf) {
                        $row->attach('uploadFile', $pathFilePdf)
                            ->press('Tambah')
                            ->waitUntilMissingText("Tidak ada file yang diupload", 5);
                    })
                    ->press("Kirim Konfirmasi")
                    ->waitForText("Apa anda yakin ingin menyimpan data ?")
                    ->press("Iya")
                    ->waitForLocation("/permohonan/pembayaran");

            // LOGOUT
            $browser->click('#userDropdown')
                    ->waitForLink('Logout') // Ganti pause dengan wait yang lebih andal
                    ->clickLink('Logout')
                    ->assertPathIs('/')
                    ->assertSee('NuklindoLab Koperasi JKRL') // Pastikan kembali ke halaman login
                    ->screenshot('logout_success');
        });
    }

    /**
     * @group keuangan
     * @group evaluasi-sewa
     */
    public function test_verifikasi_pembayaran(): void
    {
        // Verifikasi pembayaran
        $user = User::where('email', $this->keuangan)->first(); // Keuangan
        $this->browse(function (Browser $browser) use ($user) {
            $keuangan = Keuangan::where('status', 4)->orderBy('id_keuangan', 'desc')->first();
            $this->loginUser($browser, $user)
                    ->visit('staff/keuangan')
                    ->waitUntilMissing('#list-placeholder', $this->waitingTime)
                    ->click('button[onclick="switchLoadTab(3)"]')
                    ->waitUntilMissing('#list-placeholder', $this->waitingTime)
                    ->within('div[data-id="'.$keuangan->keuangan_hash.'"]', function ($row) {
                        $row->press('Verif Invoice');
                    })
                    ->waitForText("Manajemen Invoice")
                    ->screenshot("verifikasi_pembayaran")
                    ->press('Setujui')
                    ->waitForText("Apa invoice sudah benar ?")
                    ->press("Iya")
                    ->waitUntilMissing(".modal-backdrop", $this->waitingTime);

            // LOGOUT
            $browser->click('#userDropdown')
                    ->waitForLink('Logout') // Ganti pause dengan wait yang lebih andal
                    ->clickLink('Logout')
                    ->assertPathIs('/')
                    ->assertSee('NuklindoLab Koperasi JKRL') // Pastikan kembali ke halaman login
                    ->screenshot('logout_success');
        });
    }

    /**
     * @group penyelia
     * @group evaluasi-sewa
     */
    public function test_create_surattugas(): void
    {
        $user = User::where('email', $this->penyelia)->first(); // Keuangan

        $this->browse(function (Browser $browser) use ($user) {
            $penyelia = Penyelia::with('permohonan')->where('status', 1)->orderBy('id_penyelia', 'desc')->first();

            $type = '';
            if($penyelia->permohonan->tipe_kontrak == "adendum") {
                if($penyelia->permohonan->is_zerocek == 1) {
                    if ($penyelia->permohonan->is_have_tld == 1) {
                        $type = 'havetld';
                    } else {
                        $type = 'nonhavetld';
                    }
                } else {
                    $type = 'adendum';
                }
            } else {
                if ($penyelia->permohonan->is_have_tld == 1) {
                    $type = 'havetld';
                } else {
                    $type = 'nonhavetld';
                }
            }

            $listJobs = Setting_layanan::where('name', $type)->where('status', 1)->first()->list_jobs;
            $listJobsParalel = Setting_layanan::where('name', $type)->where('status', 1)->first()->list_jobs_paralel;

            $this->loginUser($browser, $user)
                    ->visit("staff/penyelia")
                    ->waitForLocation("/staff/penyelia")
                    ->waitUntilMissing('#list-placeholder', $this->waitingTime)
                    ->within('div[data-id="'.$penyelia->penyelia_hash.'"]', function ($row) {
                        $row->clickLink('Surat Tugas');
                    })
                    ->waitForLocation("/staff/penyelia/surat_tugas/c/{$penyelia->penyelia_hash}", $this->waitingTime)
                    ->screenshot("create_surattugas");

            // tambah petugas di jobs
            foreach ($listJobs as $job) {
                $browser->within('li[data-idjobs="'.$job->jobs_hash.'"]', function ($row) {
                    $row->press('Tambah petugas');
                })
                ->waitForText("List petugas")
                ->waitUntilMissing(".spinner-border")
                ->click('#modal-list-petugas > div:first-child .text-success')
                ->waitUntilMissing(".modal-backdrop", $this->waitingTime);
            }

            // tambah petugas di jobs paralel
            foreach ($listJobsParalel as $jobParalel) {
                $browser->within('li[data-idjobs="'.$jobParalel->jobs_hash.'"]', function ($row) {
                    $row->press('Tambah petugas');
                })
                ->waitForText("List petugas")
                ->waitUntilMissing(".spinner-border")
                ->click('#modal-list-petugas > div:first-child .text-success')
                ->waitUntilMissing(".modal-backdrop", $this->waitingTime);
            }

            // simpan surat tugas
            $browser->press('Simpan')
                    ->waitForText("tambah surat tugas?")
                    ->press("Iya")
                    ->waitForLocation("/staff/penyelia");

            $browser->screenshot("create_surattugas_success");

            // LOGOUT
            $browser->click('#userDropdown')
                    ->waitForLink('Logout') // Ganti pause dengan wait yang lebih andal
                    ->clickLink('Logout')
                    ->assertPathIs('/')
                    ->assertSee('NuklindoLab Koperasi JKRL') // Pastikan kembali ke halaman login
                    ->screenshot('logout_success');
        });
    }

    /**
     * @group penyelia
     * @group evaluasi-sewa
     */
    public function test_ttd_manager_surattugas(): void
    {
        $user = User::where('email', $this->managerPenyelia)->first(); // Keuangan
        $this->browse(function (Browser $browser) use ($user) {
            $penyelia = Penyelia::with('permohonan')->where('status', 2)->orderBy('id_penyelia', 'desc')->first();

            $this->loginUser($browser, $user)
                    ->visit("manager/surat_tugas", $this->waitingTime)
                    ->waitForLocation("/manager/surat_tugas", $this->waitingTime)
                    ->waitUntilMissing('#list-placeholder', $this->waitingTime)
                    ->within('div[data-id="'.$penyelia->penyelia_hash.'"]', function ($row) {
                        $row->clickLink('Surat Tugas');
                    })
                    ->waitForLocation("/manager/surat_tugas/v/{$penyelia->penyelia_hash}")
                    ->click("#managerValid")
                    ->press("Simpan")
                    ->waitForText("verif surat tugas?")
                    ->press("Iya")
                    ->waitForLocation("/manager/surat_tugas")
                    ->screenshot("ttd_manager_surattugas");

            // LOGOUT
            $browser->click('#userDropdown')
                    ->waitForLink('Logout') // Ganti pause dengan wait yang lebih andal
                    ->clickLink('Logout')
                    ->assertPathIs('/')
                    ->assertSee('NuklindoLab Koperasi JKRL') // Pastikan kembali ke halaman login
                    ->screenshot('logout_success');
        });
    }

    /**
     * @group penyelia
     * @group evaluasi-sewa
     */
    public function test_progress_lab(): void
    {
        $pathFilePdf = base_path('tests/Browser/file_dummy/filePdf.pdf');
        $penyelia = Penyelia::with(
            'petugas',
            'petugas.jobs',
            'petugas.user'
        )->where('status', 10)->orderBy('id_penyelia', 'desc')->first();

        $this->browse(function (Browser $browser) use ($penyelia, $pathFilePdf) {
            if($penyelia){
                foreach($penyelia->petugas as $petugas){
                    $jobs = Penyelia_map::where('id_map', $petugas->id_map)->first();
                    if($jobs->status == 1){
                        $user = User::where('email', $petugas->user->email)->first();

                        $this->loginUser($browser, $user)
                                ->visit("staff/lhu", $this->waitingTime)
                                ->waitForLocation("/staff/lhu", $this->waitingTime)
                                ->waitUntilMissing('#list-placeholder-lhu', $this->waitingTime)
                                ->within('div[data-id="'.$penyelia->penyelia_hash.'"]', function ($row) {
                                    $row->press('update progress');
                                })
                                ->waitForText("Update progress")
                                ->type("#inputNote", "Ok");

                        if($petugas->jobs->id_jobs == 10) {
                            $browser->attach('uploadFile', $pathFilePdf)
                                    ->press("Tambah")
                                    ->waitUntilMissingText("Tidak ada file yang diupload", $this->waitingTime);
                        }

                        $browser->press("Update")
                                ->waitUntilMissing(".modal-backdrop", $this->waitingTime)
                                ->waitForText("Progress berhasil diupdate", $this->waitingTime)
                                ->press("OK");

                        // LOGOUT
                        $browser->click('#userDropdown')
                                ->waitForLink('Logout') // Ganti pause dengan wait yang lebih andal
                                ->clickLink('Logout')
                                ->assertPathIs('/')
                                ->assertSee('NuklindoLab Koperasi JKRL') // Pastikan kembali ke halaman login
                                ->screenshot('logout_success');
                    }

                }
            }
        });
    }

    /**
     * @group pengiriman
     * @group evaluasi-sewa
     */
    public function test_pengiriman_send(): void
    {
        $pathFileImage = base_path('tests/Browser/file_dummy/fileImage.jpg');
        $user = User::where('email', $this->admin)->first(); // Admin
        $this->browse(function (Browser $browser) use ($user, $pathFileImage) {
            $pengiriman = Pengiriman::where('status', 3)->orderBy('id_pengiriman', 'desc')->first();
            $this->loginUser($browser, $user)
                    ->visit("staff/pengiriman")
                    ->waitUntilMissing('#list-placeholder-pengiriman', $this->waitingTime);

            $browser->within('div[data-id="'.$pengiriman->id_pengiriman.'"]', function ($row) {
                $row->press('Kirim');
            });

            $noResi = "R-".rand(1000000000, 9999999999);

            $browser->waitForText("Kirim Dokumen")
                    ->select("jasa_kurir", "wVytkcL66wSLKdnwaKS77Q")
                    ->type("noResi", $noResi)
                    ->waitUntil("document.querySelector('input[name=\"noResi\"]').value == '$noResi'")
                    ->attach('uploadFile', $pathFileImage)
                    ->press("Tambah")
                    ->waitUntilMissingText("Tidak ada file yang diupload", $this->waitingTime)
                    ->click("#btn-kirim")
                    ->waitForText("Apakah Anda yakin?")
                    ->press("Ya, kirim!")
                    ->waitForText("Dokumen berhasil dikirim", $this->waitingTime)
                    ->waitUntilMissingText("Dokumen berhasil dikirim", $this->waitingTime);

            // LOGOUT
            $browser->click('#userDropdown')
                    ->waitForLink('Logout') // Ganti pause dengan wait yang lebih andal
                    ->clickLink('Logout')
                    ->assertPathIs('/')
                    ->assertSee('NuklindoLab Koperasi JKRL') // Pastikan kembali ke halaman login
                    ->screenshot('logout_success');

        });
    }

    /**
     * @group pengiriman
     * @group evaluasi-sewa
     */
    public function test_pengiriman_pelanggan(): void
    {
        $pathFileImage = base_path('tests/Browser/file_dummy/fileImage.jpg');
        $user = User::where('email', $this->pelanggan)->first(); // Pelanggan
        $this->browse(function (Browser $browser) use ($user, $pathFileImage) {
            $pengiriman = Pengiriman::where('status', 1)->orderBy('id_pengiriman', 'desc')->first();
            $this->loginUser($browser, $user)
                    ->visit("permohonan/pengiriman")
                    ->waitFor('#list-container-pengiriman', $this->waitingTime)
                    ->within('div[data-id="'.$pengiriman->id_pengiriman.'"]', function ($row) {
                        $row->press('Diterima');
                    })
                    ->waitForText("Dokumen diterima");
            $browser->waitFor('#list-kelengkapan', $this->waitingTime);

            // 1. Ambil semua elemen checkbox di dalam list
            $checkboxes = $browser->elements('#list-kelengkapan input[type="checkbox"]');

            // 2. Lakukan perulangan untuk mengeklik masing-masing checkbox
            foreach ($checkboxes as $checkbox) {
                if (!$checkbox->isSelected()) {
                    $checkbox->click();
                }
            }
            $browser->attach('uploadFile', $pathFileImage)
                    ->press("Tambah")
                    ->waitUntilMissingText("Tidak ada file yang diupload", $this->waitingTime)
                    ->click("#btnSendDocument")
                    ->waitForText("Konfirmasi Penerimaan Dokumen")
                    ->press("Ya, terima!")
                    ->waitForText("Document diterima", $this->waitingTime)
                    ->press("OK")
                    ->waitUntilMissingText("Document diterima", $this->waitingTime);

            // LOGOUT
            $browser->click('#userDropdown')
                    ->waitForLink('Logout') // Ganti pause dengan wait yang lebih andal
                    ->clickLink('Logout')
                    ->assertPathIs('/')
                    ->assertSee('NuklindoLab Koperasi JKRL') // Pastikan kembali ke halaman login
                    ->screenshot('logout_success');
        });
    }
}
