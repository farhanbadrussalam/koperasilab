<?php

namespace App\Services\Adendum\Validators;

use App\Models\Pengiriman;
use App\Services\Adendum\AdendumContext;
use App\Services\Adendum\Contracts\AdendumValidatorContract;

/**
 * Validasi pengiriman TLD periode berikutnya.
 * Jika TLD untuk periode berikutnya sudah dikirim (status 1/2/3),
 * tidak boleh ada penambahan pengguna atau kontrol baru.
 *
 * Berlaku untuk P0 dan P+1.
 */
class TldNextSentValidator implements AdendumValidatorContract
{
    public function validate(AdendumContext $context): ?string
    {
        // Hanya berlaku jika periode >= aktif dan ada item baru
        if ($context->selisih < 0 || !$context->adaItemBaru()) {
            return null;
        }

        $checkPeriode = $context->dataPeriode->periode + 1;

        $isTldNextSent = Pengiriman::where('id_kontrak', $context->idKontrak)
            ->where('periode', $checkPeriode)
            ->whereHas('detail', fn($q) => $q->where('jenis', 'tld'))
            ->whereIn('status', [1, 2, 3])
            ->exists();

        if ($isTldNextSent) {
            return 'Tidak diperbolehkan menambah pengguna baru karena TLD untuk periode berikutnya sudah dikirim.';
        }

        return null;
    }
}
