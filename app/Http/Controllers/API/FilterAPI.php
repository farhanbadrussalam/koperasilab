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

use Auth;
use DB;

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

            if(!empty($perusahaan)){
                $data = Perusahaan::where('status', 1)
                        ->where('nama_perusahaan', 'like', '%'.$perusahaan.'%')
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
        DB::beginTransaction();
        try {
            $jenis = $request->has('jenis') ? $request->jenis : false;
            switch ($jenis) {
                case 'kontrak':
                    $status = [
                        array(
                            'id' => encryptor(1),
                            'name' => 'Aktif',
                        )
                    ];
                    break;
                case 'penyelia':
                    $jobs = Master_jobs::all();
                    $arrJobs = $jobs->map(function($item) {
                        return [
                            'id' => $item->jobs_hash,
                            'name' => $item->name
                        ];
                    })->toArray();
                    $status = array_merge([
                        [
                            'id' => encryptor(1),
                            'name' => 'Pengajuan',
                        ],
                        [
                            'id' => encryptor(2),
                            'name' => 'TTD Manager',
                        ],
                    ], $arrJobs, [
                        [
                            'id' => encryptor(3),
                            'name' => 'Selesai',
                        ]
                    ]);
                    break;
                case 'manager-invoice':
                    $status = [
                        array(
                            'id' => encryptor(2),
                            'name' => 'Verifikasi',
                        ),
                        array(
                            'id' => encryptor(3),
                            'name' => 'Perlu dibayar',
                        ),
                        array(
                            'id' => encryptor(4),
                            'name' => 'Menunggu konfirmasi',
                        ),
                        array(
                            'id' => encryptor(5),
                            'name' => 'Pembayaran diterima',
                        )
                    ];
                    break;
                case 'pembayaran':
                    $status = [
                        array(
                            'id' => encryptor(3),
                            'name' => 'Perlu dibayar',
                        ),
                        array(
                            'id' => encryptor(4),
                            'name' => 'Menunggu konfirmasi',
                        ),
                        array(
                            'id' => encryptor(5),
                            'name' => 'Pembayaran diterima',
                        )
                    ];
                    break;
                case 'pengguna':
                    $status = [
                        array(
                            'id' => encryptor(1),
                            'name' => 'Tidak Aktif',
                        ),
                        array(
                            'id' => encryptor(2),
                            'name' => 'Pengajuan',
                        ),
                        array(
                            'id' => encryptor(3),
                            'name' => 'Aktif',
                        )
                    ];
                    break;
                default:
                    $status = [
                        array(
                            'id' => encryptor(1),
                            'name' => 'Pengajuan',
                        ),
                        array(
                            'id' => encryptor(2),
                            'name' => 'Terverifikasi',
                        ),
                        array(
                            'id' => encryptor(3),
                            'name' => 'Proses LAB',
                        ),
                        array(
                            'id' => encryptor(5),
                            'name' => 'Selesai',
                        )
                    ];
                    break;
            }

            DB::commit();
            return $this->output($status, 200);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }
}
