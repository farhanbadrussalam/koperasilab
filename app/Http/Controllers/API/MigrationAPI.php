<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Master_pengguna;
use App\Models\Permohonan_detail;
use App\Models\Kontrak_detail;
use App\Traits\RestApi;
use Auth;
use DB;

class MigrationAPI extends Controller
{
    use RestApi;

    /**
     * Endpoint migrasi data lama master_pengguna (id_divisi & kode_lencana tunggal)
     * ke format baru divisi_list (JSON array).
     * 
     * Khusus role Superadmin / Developer.
     */
    public function migratePenggunaDivisiList(Request $request)
    {
        $user = Auth::user();

        if (!$user || !($user->hasRole('Super Admin') || $user->hasRole('Developer'))) {
            return $this->output(['msg' => 'Akses ditolak. API ini khusus Super Admin.'], 'Fail', 403);
        }

        DB::beginTransaction();
        try {
            $penggunaList = Master_pengguna::all();
            $migratedCount = 0;

            foreach ($penggunaList as $pengguna) {
                // Cek jika divisi_list belum terisi atau kosong, tapi ada id_divisi ATAU kode_lencana
                $divisiList = $pengguna->divisi_list;
                if (empty($divisiList) && ($pengguna->id_divisi || $pengguna->kode_lencana)) {
                    $newDivisiList = [
                        [
                            'id_divisi' => $pengguna->id_divisi ? (int) $pengguna->id_divisi : null,
                            'kode_lencana' => $pengguna->kode_lencana ?: str_pad('1', 3, '0', STR_PAD_LEFT),
                        ]
                    ];

                    $pengguna->update([
                        'divisi_list' => $newDivisiList
                    ]);

                    $migratedCount++;
                }
            }

            DB::commit();

            return $this->output([
                'msg' => "Migrasi data pengguna berhasil. Total $migratedCount data diperbarui.",
                'total_migrated' => $migratedCount,
                'total_pengguna' => $penggunaList->count()
            ], 200);

        } catch (\Exception $ex) {
            DB::rollBack();
            info($ex);
            return $this->output(['msg' => 'Gagal melakukan migrasi: ' . $ex->getMessage()], 'Fail', 500);
        }
    }

    /**
     * Endpoint migrasi data lama permohonan_detail & kontrak_detail.
     * Mengisi kolom id_divisi_selected & kode_lencana_selected berdasarkan pengguna/divisi terkait.
     * 
     * Khusus role Superadmin / Developer.
     */
    public function migrateDetails(Request $request)
    {
        $user = Auth::user();

        if (!$user || !($user->hasRole('Super Admin') || $user->hasRole('Developer'))) {
            return $this->output(['msg' => 'Akses ditolak. API ini khusus Super Admin.'], 'Fail', 403);
        }

        DB::beginTransaction();
        try {
            $migratedPermohonan = 0;
            $migratedKontrak = 0;

            // 1. Migrasi permohonan_detail
            $permohonanDetails = Permohonan_detail::whereNull('id_divisi_selected')
                ->whereNull('kode_lencana_selected')
                ->get();

            foreach ($permohonanDetails as $detail) {
                if ($detail->jenis === 'pengguna' && $detail->id_pengguna_divisi) {
                    $pengguna = Master_pengguna::find($detail->id_pengguna_divisi);
                    if ($pengguna) {
                        $idDivisi = $pengguna->id_divisi;
                        $kodeLencana = $pengguna->kode_lencana;
                        if (!$idDivisi && !$kodeLencana && !empty($pengguna->divisi_list)) {
                            $idDivisi = $pengguna->divisi_list[0]['id_divisi'] ?? null;
                            $kodeLencana = $pengguna->divisi_list[0]['kode_lencana'] ?? null;
                        }

                        if ($idDivisi || $kodeLencana) {
                            $detail->update([
                                'id_divisi_selected' => $idDivisi ? (int) $idDivisi : null,
                                'kode_lencana_selected' => $kodeLencana
                            ]);
                            $migratedPermohonan++;
                        }
                    }
                } elseif ($detail->jenis === 'kontrol' && $detail->id_pengguna_divisi) {
                    $detail->update([
                        'id_divisi_selected' => (int) $detail->id_pengguna_divisi,
                        'kode_lencana_selected' => null
                    ]);
                    $migratedPermohonan++;
                }
            }

            // 2. Migrasi kontrak_detail
            $kontrakDetails = Kontrak_detail::whereNull('id_divisi_selected')
                ->whereNull('kode_lencana_selected')
                ->get();

            foreach ($kontrakDetails as $detail) {
                if ($detail->jenis === 'pengguna' && $detail->id_pengguna_divisi) {
                    $pengguna = Master_pengguna::find($detail->id_pengguna_divisi);
                    if ($pengguna) {
                        $idDivisi = $pengguna->id_divisi;
                        $kodeLencana = $pengguna->kode_lencana;
                        if (!$idDivisi && !$kodeLencana && !empty($pengguna->divisi_list)) {
                            $idDivisi = $pengguna->divisi_list[0]['id_divisi'] ?? null;
                            $kodeLencana = $pengguna->divisi_list[0]['kode_lencana'] ?? null;
                        }

                        if ($idDivisi || $kodeLencana) {
                            $detail->update([
                                'id_divisi_selected' => $idDivisi ? (int) $idDivisi : null,
                                'kode_lencana_selected' => $kodeLencana
                            ]);
                            $migratedKontrak++;
                        }
                    }
                } elseif ($detail->jenis === 'kontrol' && $detail->id_pengguna_divisi) {
                    $detail->update([
                        'id_divisi_selected' => (int) $detail->id_pengguna_divisi,
                        'kode_lencana_selected' => null
                    ]);
                    $migratedKontrak++;
                }
            }

            DB::commit();

            return $this->output([
                'msg' => "Migrasi data detail berhasil. Total $migratedPermohonan permohonan_detail dan $migratedKontrak kontrak_detail diperbarui.",
                'migrated_permohonan_detail' => $migratedPermohonan,
                'migrated_kontrak_detail' => $migratedKontrak,
            ], 200);

        } catch (\Exception $ex) {
            DB::rollBack();
            info($ex);
            return $this->output(['msg' => 'Gagal melakukan migrasi detail: ' . $ex->getMessage()], 'Fail', 500);
        }
    }
}
