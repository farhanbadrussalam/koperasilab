<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

use App\Models\Master_pengguna;
use App\Models\Master_radiasi;
use App\Models\Master_divisi;
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
            $nik = $request->has('nik') ? $request->nik : false;
            $jenisKelamin = $request->has('jenis_kelamin') ? $request->jenis_kelamin : false;
            $tanggalLahir = $request->has('tanggal_lahir') ? $request->tanggal_lahir : false;
            $tempatLahir = $request->has('tempat_lahir') ? $request->tempat_lahir : false;
            $name = $request->has('name') ? $request->name : false;
            $posisi = $request->has('divisi') ? $request->divisi : false;
            $radiasi = $request->has('radiasi') ? json_decode($request->radiasi) : false;
            $ktp = $request->has('ktp') ? $request->file('ktp') : false;

            $isAktif = $request->has('is_aktif') ? $request->is_aktif : false;
            $kodeLencana = $request->has('kode_lencana') ? $request->kode_lencana : false;

            if ($radiasi) {
                $radiasi = array_map(function($value) {
                    if(decryptor($value) == 0) {
                        $dataRadiasi = Master_radiasi::create([
                            'nama_radiasi' => $value,
                            'status' => 1,
                        ]);
                        return $dataRadiasi->id_radiasi;
                    }else {
                        return decryptor($value);
                    }
                }, $radiasi);
            }

            if ($posisi) {
                if (decryptor($posisi) == 0) {
                    $dataDivisi = Master_divisi::create([
                        'kode_lencana' => "C",
                        'name' => $posisi,
                        'id_perusahaan' => Auth::user()->id_perusahaan,
                        'status' => 1,
                        'created_by' => Auth::user()->id
                    ]);
                    $posisi = $dataDivisi->id_divisi;
                } else {
                    $posisi = decryptor($posisi);
                }
            }

            $file_ktp = $this->media->upload($ktp, 'pengguna');

            $params = array();

            $name && $params['name'] = $name;
            $posisi && $params['id_divisi'] = $posisi;
            $radiasi && $params['id_radiasi'] = $radiasi;
            $ktp && $params['ktp'] = $file_ktp->getIdMedia();
            $nik && $params['nik'] = $nik;
            $kodeLencana && $params['kode_lencana'] = str_pad($kodeLencana, 3, '0', STR_PAD_LEFT);
            $jenisKelamin && $params['jenis_kelamin'] = $jenisKelamin;
            $tanggalLahir && $params['tanggal_lahir'] = $tanggalLahir;
            $tempatLahir && $params['tempat_lahir'] = $tempatLahir;

            if(!$id){
                // generate kode lencana
                $params['created_by'] = Auth::user()->id;
                $params['id_perusahaan'] = Auth::user()->id_perusahaan;
                $params['status'] = 1;
                $params['kode_lencana'] = $isAktif == 1 ? $this->generateKodeLencana() : str_pad($kodeLencana, 3, '0', STR_PAD_LEFT);
            }

            $pengguna = Master_pengguna::updateOrCreate(
                ['id_pengguna' => $id],
                $params
            );

            $file_ktp->store();

            DB::commit();
            return $this->output(array('msg' => 'Pengguna Behasil ditambahkan', 'id' => encryptor($pengguna->id_pengguna)), 200);

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
        // $perusahaan = Auth::user()->id_perusahaan;
        // $perusahaan = Perusahaan::find($perusahaan);
        // $perusahaan = $perusahaan->kode_perusahaan;

        // Mengambil kode lencana terakhir yang ada
        $lencana = Master_pengguna::where('id_perusahaan', Auth::user()->id_perusahaan)->orderBy('kode_lencana', 'desc')->first();

        if($lencana){
            $lencana = (int) $lencana->kode_lencana;
        } else {
            $lencana = 0;
        }
        return str_pad($lencana + 1, 3, '0', STR_PAD_LEFT);
    }
}
