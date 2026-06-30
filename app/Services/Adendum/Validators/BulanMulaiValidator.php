<?php

namespace App\Services\Adendum\Validators;

use App\Services\Adendum\AdendumContext;
use App\Services\Adendum\Contracts\AdendumValidatorContract;

/**
 * Validasi bulan mulai untuk periode berikutnya (P+1).
 * Jika adendum pada P+1 dengan item baru, bulan mulai harus 1
 * (dimulai dari awal periode, full satu periode).
 */
class BulanMulaiValidator implements AdendumValidatorContract
{
    public function validate(AdendumContext $context): ?string
    {
        if ($context->isPeriodeBerikutnya() && $context->adaItemBaru()) {
            if ($context->bulanMulai !== 1) {
                return 'Untuk adendum periode berikutnya, bulan mulai harus diatur penuh satu periode.';
            }
        }

        return null;
    }
}
