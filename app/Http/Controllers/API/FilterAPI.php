<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Traits\RestApi;

use App\Models\Master_jenistld;
use App\Models\Master_jenisLayanan;
use App\Models\Master_jobs;
use App\Models\Perusahaan;
use App\Models\Satuan_kerja;
use Auth;
use DB;
use Spatie\Permission\Models\Role;

class FilterAPI extends Controller
{
    use RestApi;

    public function getJenisTLD(Request $request)
    {
        DB::beginTransaction();
        try {
            $jenis_tld = Master_jenistld::where('status', 1)->get();

            DB::commit();
            return $this->output($jenis_tld, 200);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function getJenisLayanan(Request $request)
    {
        DB::beginTransaction();
        try {
            $jenis_layanan = Master_jenisLayanan::where('status', 1)->whereNull('parent')->get();

            DB::commit();
            return $this->output($jenis_layanan, 200);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function getPerusahaan(Request $request)
    {
        DB::beginTransaction();
        try {
            $perusahaan = $request->has('perusahaan') ? $request->perusahaan : false;
            $data = array();

            if (!empty($perusahaan)) {
                $data = Perusahaan::where('status', 1)
                    ->where('nama_perusahaan', 'like', '%' . $perusahaan . '%')
                    ->get();
            }

            DB::commit();
            return $this->output($data, 200);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function getStatus(Request $request)
    {
        try {
            $status = match ($request->input('jenis')) {
                'kontrak' => [
                    ['id' => encryptor(1), 'name' => 'Aktif']
                ],
                'penyelia' => array_merge(
                    [
                        ['id' => encryptor(1), 'name' => 'Pengajuan'],
                        ['id' => encryptor(2), 'name' => 'TTD Manager'],
                    ],
                    Master_jobs::all()->map(fn($item) => [
                        'id' => $item->jobs_hash,
                        'name' => $item->name
                    ])->toArray(),
                    [
                        ['id' => encryptor(3), 'name' => 'Selesai']
                    ]
                ),
                'manager-invoice' => [
                    ['id' => encryptor(2), 'name' => 'Verifikasi'],
                    ['id' => encryptor(3), 'name' => 'Perlu dibayar'],
                    ['id' => encryptor(4), 'name' => 'Menunggu konfirmasi'],
                    ['id' => encryptor(5), 'name' => 'Pembayaran diterima']
                ],
                'pembayaran' => [
                    ['id' => encryptor(3), 'name' => 'Perlu dibayar'],
                    ['id' => encryptor(4), 'name' => 'Menunggu konfirmasi'],
                    ['id' => encryptor(5), 'name' => 'Pembayaran diterima']
                ],
                'pengguna' => [
                    ['id' => encryptor(1), 'name' => 'Tidak Aktif'],
                    ['id' => encryptor(2), 'name' => 'Pengajuan'],
                    ['id' => encryptor(3), 'name' => 'Aktif']
                ],
                'tld' => [
                    ['id' => encryptor(1), 'name' => 'Digunakan'],
                    ['id' => encryptor(0), 'name' => 'Tidak Digunakan']
                ],
                'pengiriman' => [
                    ['id' => encryptor(1), 'name' => 'Sedang dikirim'],
                    ['id' => encryptor(2), 'name' => 'Sudah diterima'],
                    ['id' => encryptor(3), 'name' => 'Proses Pengiriman']
                ],
                'penyimpanan' => [
                    ['id' => 'di_lab', 'name' => 'Di Lab / Penyimpanan'],
                    ['id' => 'evaluasi', 'name' => 'Evaluasi'],
                    ['id' => 'sewa', 'name' => 'Sewa'],
                    ['id' => 'idle', 'name' => 'Idle / Siap Pakai']
                ],
                default => [
                    ['id' => encryptor(1), 'name' => 'Pengajuan'],
                    ['id' => encryptor(2), 'name' => 'Terverifikasi'],
                    ['id' => encryptor(3), 'name' => 'Proses LAB'],
                    ['id' => encryptor(5), 'name' => 'Selesai']
                ],
            };

            return $this->output($status, 200);
        } catch (\Exception $ex) {
            info($ex);
            return $this->output(['msg' => $ex->getMessage()], 'Fail', 500);
        }
    }

    public function getSelectCustom(Request $request)
    {
        $jenis = $request->has('jenis') ? $request->jenis : false;
        switch ($jenis) {
            case 'selected_custom':
                $data = [
                    array(
                        'id' => 'kontrol',
                        'name' => 'Kontrol',
                    ),
                    array(
                        'id' => 'pengguna',
                        'name' => 'Pengguna',
                    )
                ];
                break;
            case 'satuan_kerja':
                $allData = Satuan_kerja::all();
                $data = $allData->map(function ($item) {
                    return [
                        'id' => $item->satuan_hash,
                        'name' => $item->name
                    ];
                });
                break;
            case 'roles':
                $allData = Role::all();
                $data = $allData->map(function ($item) {
                    return [
                        'id' => $item->name,
                        'name' => $item->name
                    ];
                });
                break;
            default:
                $data = [];
                break;
        }
        return $this->output($data, 200);
    }
}
