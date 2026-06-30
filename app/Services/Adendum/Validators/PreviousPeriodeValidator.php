<?php

namespace App\Services\Adendum\Validators;

use App\Services\Adendum\AdendumContext;
use App\Services\Adendum\Contracts\AdendumValidatorContract;

/**
 * Validasi untuk periode sebelumnya (P-1).
 * Pada P-1 tidak diperbolehkan menambah pengguna baru atau kontrol baru,
 * hanya pergantian pengguna yang diperbolehkan.
 */
class PreviousPeriodeValidator implements AdendumValidatorContract
{
    public function validate(AdendumContext $context): ?string
    {
        if ($context->isPeriodeSebelumnya() && $context->adaItemBaru()) {
            return 'Tidak diperbolehkan menambah pengguna baru pada periode sebelumnya.';
        }

        return null;
    }
}
