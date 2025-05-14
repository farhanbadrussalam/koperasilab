<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

use App\Models\Master_pengguna;
use App\Models\Master_radiasi;
use App\Models\Perusahaan;

use App\Traits\RestApi;

use App\Http\Controllers\MediaController;
use App\Http\Controllers\LogController;

use DB;
use Auth;

class PenggunaAPI extends Controller
{
    use RestApi;

    public function __construct() {
        $this->media = new MediaController();
        $this->log = new LogController();
    }

    public function action(Request $request) {
        
        DB::beginTransaction();
        try {
            $id = $request->id ? decryptor($request->id) : false;
            $name = $request->has('name') ? $request->name : false;
            $posisi = $request->has('divisi') ? $request->divisi : false;
            $radiasi = $request->has('radiasi') ? json_decode($request->radiasi) : false;
            $ktp = $request->has('ktp') ? $request->file('ktp') : false;

            foreach ($radiasi as $key => $value) {
                $radiasi[$key] = (int) decryptor($value);
            }

            $file_ktp = $this->media->upload($ktp, 'pengguna');

            $params = array();

            $name && $params['name'] = $name;
            $posisi && $params['posisi'] = $posisi;
            $radiasi && $params['id_radiasi'] = $radiasi;
            $ktp && $params['ktp'] = $file_ktp->getIdMedia();

            if(!$id){
                // generate kode lencana

                $params['created_by'] = Auth::user()->id;
                $params['id_perusahaan'] = Auth::user()->id_perusahaan;
                $params['status'] = 1;
                $params['kode_lencana'] = $this->generateKodeLencana();
            }

            Master_pengguna::updateOrCreate(
                ['id_pengguna' => $id],
                $params
            );

            $file_ktp->store();

            DB::commit();
            return $this->output(array('msg' => 'Pengguna Behasil ditambahkan'));

        } catch (\Exception $ex ) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function getDataById($id) {
        DB::beginTransaction();
        try {
            $id = decryptor($id);
            $data = Master_pengguna::with('media_ktp', 'perusahaan')->find($id);

            // mengambil radiasi dari master_radiasi
            $arr = array();
            foreach ($data->id_radiasi as $key => $value) {
                array_push($arr, Master_radiasi::find($value));
            }
            $data->radiasi = $arr;


            DB::commit();
            if(!$data){
                return $this->output(array('msg' => 'Data not found'), 'Fail', 400);
            }
            return $this->output($data, 200);
        } catch (\Exception $ex ) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function destroy($id) {
        DB::beginTransaction();
        try {
            $data = Master_pengguna::findOrFail(decryptor($id));
            $data->delete();
            DB::commit();

            if($data){
                $this->media->destroy($data->ktp);
                return $this->output(array('msg' => 'Pengguna Behasil dihapus'));
            }

            return $this->output(array('msg' => 'Pengguna Gagal dihapus'), 'Fail', 400);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    private function generateKodeLencana() {
        // Mengambil kode perusahaan
        $perusahaan = Auth::user()->id_perusahaan;
        $perusahaan = Perusahaan::find($perusahaan);
        $perusahaan = $perusahaan->kode_perusahaan;

        // Mengambil kode lencana terakhir yang ada
        $lencana = Master_pengguna::where('id_perusahaan', Auth::user()->id_perusahaan)->orderBy('id_pengguna', 'desc')->first();

        if($lencana){
            $lencana = (int) explode('-', $lencana->kode_lencana)[1];
        } else {
            $lencana = 0;
        }
        return $perusahaan .'-'. str_pad($lencana + 1, 4, '0', STR_PAD_LEFT);
    }
}
