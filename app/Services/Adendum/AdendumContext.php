<?php

namespace App\Services\Adendum;

use App\Models\Kontrak;
use App\Models\Kontrak_periode;

/**
 * Value Object yang membawa seluruh data konteks adendum.
 * Di-pass ke semua validator dalam pipeline.
 *
 * Tambahkan property baru di sini jika ada kondisi baru yang butuh data tambahan.
 */
class AdendumContext
{
    // ── Data Utama ──────────────────────────────────────────────────────
    public readonly Kontrak         $kontrak;
    public readonly Kontrak_periode $dataPeriode;
    public readonly Kontrak_periode $periodeActive;

    // ── Kalkulasi Periode ────────────────────────────────────────────────
    /** Selisih antara periode dipilih dan periode aktif: -1, 0, atau +1 */
    public readonly int $selisih;

    // ── Data Pengguna & Kontrol ──────────────────────────────────────────
    /** Array pengguna yang diajukan (raw dari request) */
    public readonly array $pengguna;

    /** Array kontrol yang diajukan (raw dari request) */
    public readonly array $kontrol;

    /** Jumlah pengguna dengan status 'baru' */
    public readonly int $jumPenggunaBaru;

    /** Jumlah kontrol dengan status 'baru' */
    public readonly int $jumKontrolBaru;

    // ── Flag / Opsi ──────────────────────────────────────────────────────
    /** 1 jika Zero Check diaktifkan, 0 jika tidak */
    public readonly int $isZeroCek;

    /** 1 jika TLD dari pelanggan, 0 jika dari lab */
    public readonly int $isHaveTld;

    /** Nomor bulan mulai layanan (1, 2, atau 3) */
    public readonly int $bulanMulai;

    /** ID kontrak (decrypted) */
    public readonly int $idKontrak;

    /** ID periode (decrypted) */
    public readonly int $idPeriode;

    /**
     * @param array $pengguna     Array dari json_decode request->pengguna
     * @param array $kontrol      Array dari json_decode request->kontrol
     */
    public function __construct(
        Kontrak         $kontrak,
        Kontrak_periode $dataPeriode,
        Kontrak_periode $periodeActive,
        array           $pengguna,
        array           $kontrol,
        int             $isZeroCek,
        int             $isHaveTld,
        int             $bulanMulai,
        int             $idKontrak,
        int             $idPeriode,
    ) {
        $this->kontrak       = $kontrak;
        $this->dataPeriode   = $dataPeriode;
        $this->periodeActive = $periodeActive;
        $this->pengguna      = $pengguna;
        $this->kontrol       = $kontrol;
        $this->isZeroCek     = $isZeroCek;
        $this->isHaveTld     = $isHaveTld;
        $this->bulanMulai    = $bulanMulai;
        $this->idKontrak     = $idKontrak;
        $this->idPeriode     = $idPeriode;

        // Hitung selisih periode
        $this->selisih = $dataPeriode->periode - $periodeActive->periode;

        // Hitung jumlah baru
        $this->jumPenggunaBaru = count(array_filter($pengguna, fn($item) => $item->status === 'baru'));
        $this->jumKontrolBaru  = count(array_filter($kontrol,  fn($item) => $item->status === 'baru'));
    }

    /** Apakah ada pengguna atau kontrol yang baru? */
    public function adaItemBaru(): bool
    {
        return $this->jumPenggunaBaru > 0 || $this->jumKontrolBaru > 0;
    }

    /** Apakah periode yang dipilih adalah periode aktif (P0)? */
    public function isPeriodeAktif(): bool
    {
        return $this->selisih === 0;
    }

    /** Apakah periode yang dipilih adalah periode sebelumnya (P-1)? */
    public function isPeriodeSebelumnya(): bool
    {
        return $this->selisih === -1;
    }

    /** Apakah periode yang dipilih adalah periode berikutnya (P+1)? */
    public function isPeriodeBerikutnya(): bool
    {
        return $this->selisih === 1;
    }
}
