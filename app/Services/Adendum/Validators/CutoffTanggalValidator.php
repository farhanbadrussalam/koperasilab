<?php

namespace App\Services\Adendum\Validators;

use App\Services\Adendum\AdendumContext;
use App\Services\Adendum\Contracts\AdendumValidatorContract;
use Carbon\Carbon;

/**
 * Validasi cut-off tanggal dan batas masa adendum untuk periode aktif (P0).
 *
 * Aturan yang dicek:
 * 1. Bulan mulai layanan tidak boleh di masa lalu
 * 2. Jika bulan mulai = bulan berjalan dan hari > 11 → sudah tutup
 * 3. Jika bulan berjalan sudah melewati bulan ke-3 periode → masa adendum habis
 */
class CutoffTanggalValidator implements AdendumValidatorContract
{
    public function validate(AdendumContext $context): ?string
    {
        // Hanya berlaku untuk P0 dan jika ada item baru
        if (!$context->isPeriodeAktif() || !$context->adaItemBaru()) {
            return null;
        }

        $today     = Carbon::now();
        $startDate = Carbon::parse($context->dataPeriode->start_date);

        // Hitung bulan ke berapa dari start_date kontrak periode
        $diffMonths        = ($today->year - $startDate->year) * 12 + ($today->month - $startDate->month);
        $currentPeriodMonth = $diffMonths + 1; // Bulan ke-1, ke-2, atau ke-3 dalam periode

        // Aturan 1: Bulan mulai tidak boleh di masa lalu
        if ($context->bulanMulai < $currentPeriodMonth) {
            return 'Bulan mulai layanan tidak boleh berada di masa lalu.';
        }

        // Aturan 2: Cut-off tanggal 11 untuk bulan berjalan
        if ($context->bulanMulai === $currentPeriodMonth && $today->day > 11) {
            return 'Bulan mulai layanan untuk bulan berjalan sudah ditutup setelah tanggal 11. Silakan pilih bulan berikutnya.';
        }

        // Aturan 3: Batas masa adendum (maksimal 3 bulan dalam periode)
        if ($currentPeriodMonth > 3 || ($currentPeriodMonth === 3 && $today->day > 11)) {
            return 'Masa adendum untuk periode berjalan saat ini sudah habis. Silakan pilih periode berikutnya.';
        }

        return null;
    }
}
