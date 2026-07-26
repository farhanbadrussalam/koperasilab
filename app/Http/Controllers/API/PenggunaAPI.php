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
    protected MediaController $media;
    protected LogController $log;

    public function __construct() {
        $this->media = new MediaController();
        $this->log = resolve(LogController::class);
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
            $radiasi = $request->has('radiasi') ? json_decode($request->radiasi) : false;
            $ktp = $request->has('ktp') ? $request->file('ktp') : false;

            // Handle multi-divisi input
            $rawDivisiList = $request->has('divisi_list') ? $request->divisi_list : false;
            if (is_string($rawDivisiList)) {
                $rawDivisiList = json_decode($rawDivisiList, true);
            }

            // Fallback jika dikirim via format single divisi lama
            if (empty($rawDivisiList) && $request->has('divisi')) {
                $rawDivisiList = [
                    [
                        'id_divisi' => $request->divisi,
                        'kode_lencana' => $request->has('kode_lencana') ? $request->kode_lencana : '',
                        'is_auto' => $request->has('is_aktif') ? (int) $request->is_aktif : 0
                    ]
                ];
            }

            $file_ktp = false;

            if ($radiasi) {
                $radiasi = array_map(function($value) {
                    if(decryptor($value) == 0) {
                        if($value == null){
                            return false;
                        }

                        $dataRadiasi = Master_radiasi::create([
                            'nama_radiasi' => $value,
                            'status' => 1,
                        ]);
                        return (int) $dataRadiasi->id_radiasi;
                    }else {
                        return (int) decryptor($value);
                    }
                }, $radiasi);
            }

            // Proteksi Hapus Divisi jika sedang digunakan pada Kontrak Aktif (saat edit)
            if ($id) {
                $existingPengguna = Master_pengguna::find($id);
                if ($existingPengguna) {
                    $oldDivisiList = $existingPengguna->divisi_list;
                    $oldDivisiIds = [];
                    if (is_array($oldDivisiList)) {
                        foreach ($oldDivisiList as $oItem) {
                            if (!empty($oItem['id_divisi'])) {
                                $oldDivisiIds[] = (int) $oItem['id_divisi'];
                            }
                        }
                    } elseif ($existingPengguna->id_divisi) {
                        $oldDivisiIds[] = (int) $existingPengguna->id_divisi;
                    }

                    $newDivisiIds = [];
                    if (is_array($rawDivisiList)) {
                        foreach ($rawDivisiList as $nItem) {
                            $divVal = $nItem['id_divisi'] ?? null;
                            if ($divVal) {
                                $dec = decryptor($divVal);
                                if ($dec != 0) {
                                    $newDivisiIds[] = (int) $dec;
                                } elseif (is_numeric($divVal)) {
                                    $newDivisiIds[] = (int) $divVal;
                                }
                            }
                        }
                    }

                    $removedDivisiIds = array_diff($oldDivisiIds, $newDivisiIds);
                    foreach ($removedDivisiIds as $remDivId) {
                        $isBound = \App\Models\Kontrak_detail::where('jenis', 'pengguna')
                            ->where('id_pengguna_divisi', $id)
                            ->where('status', 1)
                            ->where(function ($q) use ($remDivId) {
                                $q->where('id_divisi_selected', $remDivId)
                                  ->orWhereNull('id_divisi_selected');
                            })
                            ->whereHas('kontrak', fn($q) => $q->where('status', 1))
                            ->exists();

                        if ($isBound) {
                            $divModel = Master_divisi::withTrashed()->find($remDivId);
                            $divName = $divModel ? $divModel->name : "ID #$remDivId";
                            throw new \Exception("Divisi '$divName' tidak dapat dihapus karena masih terikat pada kontrak aktif.");
                        }
                    }
                }
            }

            // Proses pembentukan final divisi_list & generate kode lencana jika diminta/otomatis
            $finalDivisiList = [];
            $idPerusahaan = Auth::user()->id_perusahaan;
            $lastMaxKode = null;

            if (is_array($rawDivisiList) && count($rawDivisiList) > 0) {
                foreach ($rawDivisiList as $item) {
                    $divVal = $item['id_divisi'] ?? null;
                    $divId = null;

                    if ($divVal) {
                        $decrypted = decryptor($divVal);
                        if ($decrypted == 0) {
                            $dataDivisi = Master_divisi::create([
                                'kode_lencana' => "C",
                                'name' => $divVal,
                                'status' => 1,
                                'created_by' => Auth::user()->id
                            ]);
                            $divId = (int) $dataDivisi->id_divisi;
                        } else {
                            $divId = (int) $decrypted;
                        }
                    }

                    $isAuto = isset($item['is_auto']) ? (int) $item['is_auto'] : 0;
                    $kLencana = $item['kode_lencana'] ?? null;

                    if ($isAuto == 1 || empty($kLencana)) {
                        $kLencana = $this->generateKodeLencana($idPerusahaan, $lastMaxKode);
                    } else {
                        $kLencana = str_pad($kLencana, 3, '0', STR_PAD_LEFT);
                    }

                    $finalDivisiList[] = [
                        'id_divisi' => $divId,
                        'kode_lencana' => $kLencana
                    ];
                }
            }

            if($ktp) {
                $file_ktp = $this->media->upload($ktp, 'pengguna');

                if($id){
                    info("hapus media lama ". $id);
                    $idMedia = Master_pengguna::select('ktp')->where('id_pengguna', $id)->first();
                    if ($idMedia && $idMedia->ktp) {
                        $this->media->destroy($idMedia->ktp);
                    }
                }
            }

            info("prepare simpan pengguna");
            $params = array();

            $name && $params['name'] = $name;
            $radiasi && $params['id_radiasi'] = $radiasi;
            $ktp && $params['ktp'] = $file_ktp->getIdMedia();
            $nik && $params['nik'] = unmask($nik);
            $jenisKelamin && $params['jenis_kelamin'] = $jenisKelamin;
            $tanggalLahir && $params['tanggal_lahir'] = $tanggalLahir;
            $tempatLahir && $params['tempat_lahir'] = $tempatLahir;

            if (!empty($finalDivisiList)) {
                $params['divisi_list'] = $finalDivisiList;
            }

            if(!$id){
                $params['created_by'] = Auth::user()->id;
                $params['id_perusahaan'] = Auth::user()->id_perusahaan;
                $params['status'] = 1;
            }

            $pengguna = Master_pengguna::updateOrCreate(
                ['id_pengguna' => $id],
                $params
            );

            $ktp && $file_ktp->store();

            DB::commit();
            return $this->output(array('msg' => 'Pengguna Berhasil disimpan', 'id' => encryptor($pengguna->id_pengguna)), 200);

        } catch (\Exception $ex ) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function getDataById(string $id) {
        DB::beginTransaction();
        try {
            $id = decryptor($id);
            $data = Master_pengguna::with('media_ktp', 'perusahaan', 'divisi')->find($id);

            if(!$data){
                DB::rollBack();
                return $this->output(array('msg' => 'Data not found'), 'Fail', 400);
            }

            // mengambil radiasi dari master_radiasi
            $arr = array();
            if (is_array($data->id_radiasi)) {
                foreach ($data->id_radiasi as $key => $value) {
                    $radModel = Master_radiasi::find($value);
                    if ($radModel) {
                        array_push($arr, $radModel);
                    }
                }
            }
            $data->radiasi = $arr;

            DB::commit();
            return $this->output($data, 200);
        } catch (\Exception $ex ) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function getDivisi(Request $request) {
        DB::beginTransaction();
        try {
            $name_divisi = $request->has('name_divisi') ? $request->name_divisi : false;
            $limit = $request->has('limit') ? $request->limit : 10;
            $data = Master_divisi::when($name_divisi, function ($q) use ($name_divisi) {
                    return $q->where('name', 'like', '%'.$name_divisi.'%');
                })
                ->limit($limit)
                ->get();
            DB::commit();
            return $this->output($data, 200);
        } catch (\Exception $ex ) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function getRadiasi(Request $request) {
        DB::beginTransaction();
        try {
            $name_radiasi = $request->has('name_radiasi') ? $request->name_radiasi : false;
            $limit = $request->has('limit') ? $request->limit : 10;
            $data = Master_radiasi::when($name_radiasi, function ($q) use ($name_radiasi) {
                    return $q->where('nama_radiasi', 'like', '%'.$name_radiasi.'%');
            })
            ->limit($limit)
            ->get();
            DB::commit();
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
            $idPengguna = decryptor($id);
            $data = Master_pengguna::findOrFail($idPengguna);

            // Cek apakah pengguna terikat pada kontrak aktif
            $isBound = \App\Models\Kontrak_detail::where('jenis', 'pengguna')
                ->where('id_pengguna_divisi', $idPengguna)
                ->where('status', 1)
                ->whereHas('kontrak', fn($q) => $q->where('status', 1))
                ->exists();

            if ($isBound) {
                DB::rollBack();
                return $this->output(array('msg' => 'Pengguna tidak dapat dihapus karena masih terikat pada kontrak aktif.'), 'Fail', 400);
            }

            $data->delete();
            DB::commit();

            if($data){
                if ($data->ktp) {
                    $this->media->destroy($data->ktp);
                }
                return $this->output(array('msg' => 'Pengguna Berhasil dihapus'));
            }

            return $this->output(array('msg' => 'Pengguna Gagal dihapus'), 'Fail', 400);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    private function generateKodeLencana($idPerusahaan, &$lastMaxKode = null) {
        if ($lastMaxKode !== null) {
            $lastMaxKode++;
            return str_pad($lastMaxKode, 3, '0', STR_PAD_LEFT);
        }

        $allPengguna = Master_pengguna::where('id_perusahaan', $idPerusahaan)->get();
        $maxVal = 0;

        foreach ($allPengguna as $p) {
            if (!empty($p->kode_lencana) && is_numeric($p->kode_lencana)) {
                $maxVal = max($maxVal, (int) $p->kode_lencana);
            }
            $list = $p->divisi_list;
            if (is_array($list)) {
                foreach ($list as $item) {
                    if (!empty($item['kode_lencana']) && is_numeric($item['kode_lencana'])) {
                        $maxVal = max($maxVal, (int) $item['kode_lencana']);
                    }
                }
            }
        }

        $lastMaxKode = $maxVal + 1;
        return str_pad($lastMaxKode, 3, '0', STR_PAD_LEFT);
    }
}
