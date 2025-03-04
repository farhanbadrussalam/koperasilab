<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Traits\RestApi;

use App\Models\Master_jenisTld;
use App\Models\Master_jenisLayanan;

use Auth;
use DB;

class FilterAPI extends Controller
{
    use RestApi;

    public function getJenisTLD(Request $request)
    {
        DB::beginTransaction();
        try {
            $jenis_tld = Master_jenisTld::where('status', 1)->get();

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

    public function getStatus(Request $request)
    {
        DB::beginTransaction();
        try {
            $status = [
                array(
                    'id' => encryptor(1),
                    'name' => 'Pengajuan',
                ),
                array(
                    'id' => encryptor(5),
                    'name' => 'Selesai',
                )
            ];

            DB::commit();
            return $this->output($status, 200);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }
}
