<?php

namespace App\Services\Adendum\Validators;

use App\Services\Adendum\AdendumContext;
use App\Services\Adendum\Contracts\AdendumValidatorContract;

/**
 * Pipeline yang menjalankan semua validator adendum secara berurutan.
 *
 * ─────────────────────────────────────────────────────────────
 * CARA MENAMBAH KONDISI/CASE BARU:
 * ─────────────────────────────────────────────────────────────
 * 1. Buat file baru di folder Validators/, misal: GanjilGenapValidator.php
 * 2. Implement interface AdendumValidatorContract (1 method: validate)
 * 3. Tambahkan class ke array $validators di bawah ini
 * 4. SELESAI — tidak perlu ubah file lain
 * ─────────────────────────────────────────────────────────────
 */
class AdendumValidatorPipeline
{
    /**
     * Daftar validator yang dijalankan secara berurutan.
     * Urutan penting: validator yang lebih umum diletakkan lebih awal.
     *
     * @var class-string<AdendumValidatorContract>[]
     */
    protected array $validators = [
        PeriodeValidator::class,          // Cek batas periode (-1, 0, +1)
        ZeroCekValidator::class,          // Cek kewajiban zero check
        PreviousPeriodeValidator::class,  // Cek larangan baru di P-1
        CutoffTanggalValidator::class,    // Cek cut-off tanggal 11 & batas 3 bulan
        BulanMulaiValidator::class,       // Cek bulan mulai untuk P+1
        TldNextSentValidator::class,      // Cek TLD periode berikutnya sudah dikirim

        // ─────────────────────────────────────────────────
        // Tambahkan validator baru di bawah ini:
        // ExampleNewCaseValidator::class,
        // ─────────────────────────────────────────────────
    ];

    /**
     * Jalankan semua validator secara berurutan.
     * Berhenti di validator pertama yang gagal.
     *
     * @return string|null Pesan error pertama yang ditemukan, atau null jika semua OK
     */
    public function run(AdendumContext $context): ?string
    {
        foreach ($this->validators as $validatorClass) {
            /** @var AdendumValidatorContract $validator */
            $validator = new $validatorClass();
            $error = $validator->validate($context);

            if ($error !== null) {
                return $error;
            }
        }

        return null;
    }

    /**
     * Tambahkan validator secara runtime (opsional, untuk testing atau konfigurasi dinamis).
     *
     * @param class-string<AdendumValidatorContract> $validatorClass
     */
    public function push(string $validatorClass): static
    {
        $this->validators[] = $validatorClass;
        return $this;
    }
}
