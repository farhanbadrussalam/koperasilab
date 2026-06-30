<?php

namespace App\Services\Adendum\Validators;

use App\Services\Adendum\AdendumContext;
use App\Services\Adendum\Contracts\AdendumValidatorContract;

/**
 * Validasi kewajiban Zero Check.
 * Jika ada pengguna baru atau kontrol baru, Zero Check harus diaktifkan.
 */
class ZeroCekValidator implements AdendumValidatorContract
{
    public function validate(AdendumContext $context): ?string
    {
        if ($context->adaItemBaru() && $context->isZeroCek !== 1) {
            return 'Zero Check wajib diaktifkan untuk adendum penambahan pengguna baru.';
        }

        return null;
    }
}
