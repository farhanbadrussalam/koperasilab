<?php

namespace App\Services\Adendum\Validators;

use App\Services\Adendum\AdendumContext;
use App\Services\Adendum\Contracts\AdendumValidatorContract;
use App\Models\Permohonan;

/**
 * Validasi untuk mencegah pembuatan adendum baru jika masih ada adendum aktif (belum selesai).
 */
class ActiveAdendumValidator implements AdendumValidatorContract
{
    public function validate(AdendumContext $context): ?string
    {
        // Cek apakah ada permohonan adendum yang belum berstatus 5 (selesai) atau 0 (dibatalkan)
        $activeAdendum = Permohonan::where('id_kontrak', $context->idKontrak)
            ->where('tipe_kontrak', 'adendum')
            ->whereIn('status', [1, 2, 3, 4])
            ->exists();

        if ($activeAdendum) {
            return 'Tidak dapat mengajukan adendum baru. Masih ada pengajuan adendum yang sedang diproses untuk kontrak ini. Silakan tunggu hingga adendum sebelumnya selesai atau dibatalkan.';
        }

        return null;
    }
}
