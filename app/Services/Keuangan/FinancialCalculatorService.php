<?php

namespace App\Services\Keuangan;

use App\Models\Permohonan;
use App\Models\Kontrak;

class FinancialCalculatorService
{
    /**
     * Calculate the invoice details based on the base price from Permohonan.
     *
     * @param float|int $basePrice
     * @param iterable $diskon Array or Collection of diskon objects (with 'diskon' percentage)
     * @param float|int $ppnRate PPN percentage (e.g. 11)
     * @param float|int $pphRate PPH percentage (e.g. 2)
     * @return array
     */
    public function calculateInvoice($basePrice, $diskon = [], $ppnRate = 0, $pphRate = 0): array
    {
        $basePrice = (float) $basePrice;
        $totalDiskon = 0;
        
        // Calculate total discount nominal
        foreach ($diskon as $item) {
            $diskonPercentage = 0;
            if (is_array($item) && isset($item['diskon'])) {
                $diskonPercentage = (float) $item['diskon'];
            } elseif (is_object($item) && isset($item->diskon)) {
                $diskonPercentage = (float) $item->diskon;
            }
            $jumDiskon = $basePrice * ($diskonPercentage / 100);
            
            // Mutate for compatibility with ReportController which expects jumDiskon on the object
            if (is_object($item)) {
                $item->jumDiskon = $jumDiskon;
            } elseif (is_array($item)) {
                $item['jumDiskon'] = $jumDiskon;
            }
            $totalDiskon += $jumDiskon;
        }

        $priceAfterDiskon = $basePrice - $totalDiskon;

        // Calculate PPH based on base price (jumLayanan) as per original JS logic
        $pphAmount = 0;
        if ($pphRate > 0) {
            $pphAmount = $basePrice * ((float) $pphRate / 100);
        }

        $priceAfterPph = $priceAfterDiskon - $pphAmount;

        // Calculate PPN based on base price as per original JS logic
        $ppnAmount = 0;
        if ($ppnRate > 0) {
            $ppnAmount = $basePrice * ((float) $ppnRate / 100);
        }

        $grandTotal = $priceAfterPph + $ppnAmount;

        return [
            'diskon'         => $diskon,
            'jumAfterDiskon' => $priceAfterDiskon,
            'jumPpn'         => $ppnAmount,
            'jumPph'         => $pphAmount,
            'subTotal'       => $grandTotal, // Note: subTotal here means grand total to match old LabHelper structure
            // Keep new keys for future proofing
            'base_price'   => $basePrice,
            'total_diskon' => $totalDiskon,
            'ppn_amount'   => $ppnAmount,
            'pph_amount'   => $pphAmount,
            'grand_total'  => $grandTotal
        ];
    }

    /**
     * Calculate Adendum total price.
     *
     * @param Kontrak $kontrak
     * @param int $jumlahPenambahan
     * @param int $bulanMulai (1, 2, or 3)
     * @param int $periode_saat_ini
     * @return float
     */
    public function calculateAdendum(Kontrak $kontrak, int $jumlahPenambahan, int $bulanMulai, int $periode_saat_ini): float
    {
        if ($jumlahPenambahan <= 0) {
            return 0;
        }

        $hargaLayanan = (float) $kontrak->harga_layanan;
        $tarifBulanan = $hargaLayanan / 3;

        // Number of future periods
        $sisaPeriodeMendatang = 0;
        foreach ($kontrak->periode as $p) {
            if ($p->periode > $periode_saat_ini) {
                $sisaPeriodeMendatang++;
            }
        }
        
        // Match JS logic exactly: sisaBulanPeriodeIni is overridden to 3
        $sisaBulanPeriodeIni = 3; 

        $totalBulan = $sisaBulanPeriodeIni + ($sisaPeriodeMendatang * 3);
        $subTotal = $tarifBulanan * $totalBulan * $jumlahPenambahan;

        return $subTotal;
    }
}
