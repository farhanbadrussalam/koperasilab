<?php

namespace App\Services\Adendum\Contracts;

use App\Services\Adendum\AdendumContext;

/**
 * Interface untuk semua validator adendum.
 *
 * Cara menambah kondisi/case baru:
 * 1. Buat class baru yang implements interface ini
 * 2. Implementasikan method validate()
 * 3. Daftarkan class baru di AdendumValidatorPipeline::$validators
 */
interface AdendumValidatorContract
{
    /**
     * Validasi satu kondisi/case adendum.
     *
     * @param AdendumContext $context Data lengkap adendum yang akan divalidasi
     * @return string|null Pesan error jika validasi gagal, null jika OK
     */
    public function validate(AdendumContext $context): ?string;
}
