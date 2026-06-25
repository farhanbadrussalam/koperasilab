<?php

namespace App\Services\Adendum\Validators;

use App\Services\Adendum\AdendumContext;
use App\Services\Adendum\Contracts\AdendumValidatorContract;

/**
 * Validasi batasan periode adendum.
 * Hanya boleh P-1, P0, atau P+1 dari periode aktif.
 */
class PeriodeValidator implements AdendumValidatorContract
{
    public function validate(AdendumContext $context): ?string
    {
        if ($context->selisih < -1 || $context->selisih > 1) {
            return 'Periode adendum tidak valid. Hanya diperbolehkan untuk periode sebelumnya, aktif, atau berikutnya.';
        }

        return null;
    }
}
