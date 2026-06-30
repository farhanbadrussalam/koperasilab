<?php

namespace App\Http\Controllers\Adendum;

use App\Http\Controllers\Controller;
use App\Models\Kontrak;
use App\Models\Permohonan;
use App\Models\Pengiriman;
use App\Services\Adendum\AdendumService;
use Auth;
use Illuminate\Http\Request;

/**
 * Controller terpusat untuk semua operasi Adendum.
 *
 * Endpoint Web:
 *   GET  /permohonan/kontrak/a/{idKontrak}   → showForm()
 *   GET  /staff/pengiriman/adendum            → indexPengiriman()
 *
 * Endpoint API (prefix: /api/v1/adendum):
 *   POST   /store       → store()
 *   POST   /verify      → verify()
 *   DELETE /destroy/{id} → destroy()
 *   GET    /list        → list()
 */
class AdendumController extends Controller
{
    protected AdendumService $service;

    public function __construct()
    {
        $this->service = new AdendumService();
    }

    // ═══════════════════════════════════════════════════════════════
    // WEB — Halaman
    // ═══════════════════════════════════════════════════════════════

    /**
     * Tampilkan halaman form adendum.
     * Route: GET /permohonan/kontrak/a/{idKontrak}
     */
    public function showForm(string $idKontrak)
    {
        $idKontrak = decryptor($idKontrak);

        if (!$idKontrak) {
            abort(404);
        }

        $kontrak = Kontrak::with([
            'pelanggan',
            'pelanggan.perusahaan',
            'pelanggan.perusahaan.alamat',
            'layanan_jasa',
            'jenis_layanan',
            'jenis_layanan_parent',
            'jenisTld',
            'periode',
        ])->where('id_kontrak', $idKontrak)->first();

        if (!$kontrak) {
            abort(404);
        }

        // Cek status pengiriman TLD per periode
        $tldSentStatus = [];
        foreach ($kontrak->periode as $p) {
            $tldSentStatus[$p->periode] = Pengiriman::where('id_kontrak', $idKontrak)
                ->where('periode', $p->periode)
                ->whereHas('detail', fn($q) => $q->where('jenis', 'tld'))
                ->whereIn('status', [1, 2, 3])
                ->exists();
        }
        $kontrak->tld_sent_status = $tldSentStatus;

        // Cek apakah evaluasi sudah dibuat per periode
        $evaluasiCreated = [];
        foreach ($kontrak->periode as $p) {
            $evaluasiCreated[$p->periode] = Permohonan::where('id_kontrak', $idKontrak)
                ->where('periode', $p->periode)
                ->whereNot('tipe_kontrak', 'adendum')
                ->exists();
        }
        $kontrak->evaluasi_created = $evaluasiCreated;

        return view('pages.permohonan.kontrak.adendum', [
            'title'   => 'Adendum Kontrak',
            'module'  => 'permohonan-kontrak',
            'kontrak' => $kontrak,
        ]);
    }

    /**
     * Tampilkan halaman daftar pengiriman adendum (Staff).
     * Route: GET /staff/pengiriman/adendum
     */
    public function indexPengiriman()
    {
        return view('pages.staff.pengiriman.adendum', [
            'title'  => 'Pengiriman Adendum',
            'module' => 'staff-pengiriman-adendum',
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // API — Endpoint
    // ═══════════════════════════════════════════════════════════════

    /**
     * Simpan adendum baru.
     * Route: POST /api/v1/adendum/store
     */
    public function store(Request $request)
    {
        $payload = [
            'note'      => $request->note,
            'pengguna'  => $request->pengguna  ? json_decode($request->pengguna)  : [],
            'kontrol'   => $request->kontrol   ? json_decode($request->kontrol)   : [],
            'id_periode' => $request->idPeriode ? decryptor($request->idPeriode)  : false,
            'id_kontrak' => $request->id_kontrak ? decryptor($request->id_kontrak) : false,
            'sub_total'  => $request->sub_total  ?? 0,
            'bulan_mulai' => (int) ($request->bulan_mulai ?? 1),
            'is_zerocek'  => (int) ($request->is_zerocek  ?? 0),
            'is_havetld'  => (int) ($request->is_havetld  ?? 0),
        ];

        if (!$payload['id_kontrak'] || !$payload['id_periode']) {
            return $this->apiResponse(['msg' => 'Kontrak atau Periode tidak valid.'], 'Fail', 422);
        }

        $result = $this->service->store($payload);

        if (!$result['status']) {
            return $this->apiResponse(['msg' => $result['msg']], 'Fail', $result['code'] ?? 422);
        }

        return $this->apiResponse(['msg' => $result['msg']]);
    }

    /**
     * Verifikasi adendum oleh staff.
     * Route: POST /api/v1/adendum/verify
     */
    public function verify(Request $request)
    {
        $idPermohonan = $request->idPermohonan ? decryptor($request->idPermohonan) : false;

        if (!$idPermohonan) {
            return $this->apiResponse(['msg' => 'ID Permohonan tidak valid.'], 'Fail', 422);
        }

        $payload = [
            'id_permohonan'  => $idPermohonan,
            'ttd'            => $request->ttd    ? decryptor($request->ttd)    : null,
            'ttd_by'         => $request->ttd_by ? decryptor($request->ttd_by) : null,
            'list_tld'       => $request->listTld ? json_decode($request->listTld) : [],
            'tanggal_selesai' => $request->tanggal_selesai,
        ];

        $result = $this->service->verify($payload);

        if (!$result['status']) {
            return $this->apiResponse(['msg' => $result['msg']], 'Fail', $result['code'] ?? 422);
        }

        return $this->apiResponse(['msg' => $result['msg']]);
    }

    /**
     * Hapus adendum beserta data turunannya.
     * Route: DELETE /api/v1/adendum/destroy/{id}
     * Hanya Super Admin & Developer.
     */
    public function destroy(string $id)
    {
        $user = Auth::user();
        if (!$user || !($user->hasRole('Super Admin') || $user->getRoleNames()->contains('Developer'))) {
            return $this->apiResponse(['msg' => 'Akses ditolak. Hanya Developer atau Super Admin yang diperbolehkan.'], 'Fail', 403);
        }

        $idDecrypted = decryptor($id);
        if (!$idDecrypted) {
            return $this->apiResponse(['msg' => 'ID Permohonan tidak valid'], 'Fail', 400);
        }

        $result = $this->service->destroy((int) $idDecrypted);

        if (!$result['status']) {
            return $this->apiResponse(['msg' => $result['msg']], 'Fail', $result['code'] ?? 400);
        }

        return $this->apiResponse(['msg' => $result['msg']]);
    }

    /**
     * Daftar adendum yang perlu diproses pengirimannya.
     * Route: GET /api/v1/adendum/list
     */
    public function list(Request $request)
    {
        $result = $this->service->list([
            'limit'      => $request->limit     ?? 10,
            'page'       => $request->page       ?? 1,
            'no_kontrak' => $request->no_kontrak ?? '',
            'filter'     => $request->filter     ?? [],
        ]);

        return response()->json([
            'status'     => 'Success',
            'data'       => $result['data'],
            'pagination' => $result['pagination'],
        ], 200);
    }

    // ═══════════════════════════════════════════════════════════════
    // PRIVATE HELPERS
    // ═══════════════════════════════════════════════════════════════

    /**
     * Buat response API yang konsisten (mirip RestApi trait).
     */
    private function apiResponse(array $data, string $status = 'Success', int $code = 200)
    {
        $meta = [
            'code'          => $code,
            'message'       => $status,
            'response_time' => microtime(true) - LARAVEL_START,
            'response_date' => now()->toISOString(),
        ];

        return response()->json([
            'meta' => $meta,
            'data' => $data,
        ], $code);
    }
}
