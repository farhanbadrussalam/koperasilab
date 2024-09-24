<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Traits\RestApi;

use App\Models\Permohonan;
use App\Models\Permohonan_pengguna;
use App\Models\Master_layanan_jasa;
use App\Models\Master_jenisLayanan;
use App\Models\Master_media;
use App\Models\Master_radiasi;
use App\Models\Master_price;
use App\Models\Master_jenistld;

use App\Http\Controllers\MediaController;

use Auth;
use DB;

class PermohonanAPI extends Controller
{
    use RestApi;

    public function __construct(){
        $this->media = resolve(MediaController::class);
    }
    /**
     * Display a listing of the resource.
     */
    // public function listPermohonan(Request $request)
    // {
    //     $limit = $request->has('limit') ? $request->limit : 10;
    //     $page = $request->has('page') ? $request->page : 1;
    //     $search = $request->has('search') ? $request->search : '';
    //     $status = $request->has('status') ? $request->status : 1;

    //     $query = Permohonan::with(['layananjasa', 'tbl_lhu', 'tbl_kip'])
    //                 ->where('status', $status)
    //                 ->where('created_by', Auth::user()->id)
    //                 ->offset(($page - 1) * $limit)
    //                 ->limit($limit)
    //                 ->paginate($limit);
    //     $arr = $query->toArray();
    //     $this->pagination = Arr::except($arr, 'data');
    //     return $this->output($query);
    // }

    /**
     * Store a newly created resource in storage.
     */
    public function tambahPengajuan(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = $request->validate([
                'idPermohonan' => 'required',
                'idLayanan' => 'required',
                'jenisLayanan1' => 'required',
                'jenisLayanan2' => 'required'
            ]);
            $idPermohonan = decryptor($request->idPermohonan);
            $idLayanan = decryptor($request->idLayanan);
            $jenisLayanan1 = decryptor($request->jenisLayanan1);
            $jenisLayanan2 = decryptor($request->jenisLayanan2);

            $tipeKontrak = $request->tipeKontrak ? $request->tipeKontrak : false;
            $noKontrak = $request->noKontrak ? $request->noKontrak : null;
            $jenisTld = $request->jenisTld ? decryptor($request->jenisTld) : null;
            $periodePemakaian = $request->periodePemakaian ? $request->periodePemakaian : false;
            $jumlahPengguna = $request->jumlahPengguna ? $request->jumlahPengguna : false;
            $jumlahKontrol = $request->jumlahKontrol ? $request->jumlahKontrol : false;
            $totalHarga = $request->totalHarga ? $request->totalHarga : false;
            $hargaLayanan = $request->hargaLayanan ? $request->hargaLayanan : false;

            $data = array();

            $data['id_layanan'] = $idLayanan;
            $data['jenis_layanan_1'] = $jenisLayanan1;
            $data['jenis_layanan_2'] = $jenisLayanan2;

            $tipeKontrak && $data['tipe_kontrak'] = $tipeKontrak;
            $noKontrak && $data['no_kontrak'] = $noKontrak;
            $periodePemakaian && $data['periode_pemakaian'] = $periodePemakaian;
            $jenisTld && $data['jenis_tld'] = $jenisTld;
            $jumlahPengguna && $data['jumlah_pengguna'] = $jumlahPengguna;
            $jumlahKontrol && $data['jumlah_kontrol'] = $jumlahKontrol;
            $totalHarga && $data['total_harga'] = unmask($totalHarga);
            $hargaLayanan && $data['harga_layanan'] = $hargaLayanan;
            $data['status'] = 1;
            $data['flag_read'] = 0;

            // Save to db
            $permohonan = Permohonan::where('id_permohonan', $idPermohonan)->update($data);
            DB::commit();

            if($permohonan) {
                return $this->output(array('msg' => 'Data berhasil disimpan!'));
            }
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function tambahPengguna(Request $request)
    {
        $validator = $request->validate([
            'idPermohonan' => 'required',
            'ktp' => 'required',
            'nama' => 'required',
            'radiasi' => 'required'
        ]);

        DB::beginTransaction();
        try {
            $idPermohonan = decryptor($request->idPermohonan);
            $nama = $request->nama;
            $divisi = $request->divisi;
            $radiasi = array();
            $arrRadiasi = json_decode($request->radiasi);
            
            foreach ($arrRadiasi as $key => $value) {
                array_push($radiasi, decryptor($value));
            }

            $ktp = $request->file('ktp');
    
            $file_ktp = $this->media->upload($ktp, 'permohonan');
            
            $create = Permohonan_pengguna::create(array(
                'id_permohonan' => $idPermohonan,
                'nama' => $nama,
                'posisi' => $divisi,
                'id_radiasi' => json_encode($radiasi),
                'file_ktp' => $file_ktp->getIdMedia(),
                'status' => 2,
                'created_by' => Auth::user()->id
            ));
            DB::commit();

            if($create){
                $file_ktp->store();
                return $this->output(array('msg' => 'Pengguna Behasil ditambahkan'));
            }
            return $this->output(array('msg' => 'Gagal ditambahkan'), 'Fail', 400);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function destroyPengguna(string $idPengguna)
    {
        $id = decryptor($idPengguna);

        DB::beginTransaction();
        try {
            $fileKtp = Permohonan_pengguna::select('file_ktp')->where('id_pengguna', $id)->first();
            $delete = Permohonan_pengguna::where('id_pengguna', $id)->delete();
            DB::commit();
            if($delete){
                $this->media->destroy($fileKtp->file_ktp);
                return $this->output(array('msg' => 'Data berhasil dihapus'));
            }

            return $this->output(array('msg' => 'Data gagal dihapus'), 'Fail', 400);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }

    }

    public function destroyPermohonan(string $id)
    {
        $id = decryptor($id);

        DB::beginTransaction();
        try {
            
            $dataPengguna = Permohonan_pengguna::where('id_permohonan', $id)->get();
            
            if($dataPengguna) {
                foreach ($dataPengguna as $item) {
                    $this->media->destroy($item->file_ktp);
                }
            }
            $permohonan = Permohonan::where('id_permohonan', $id)->delete();
            
            DB::commit();

            if($permohonan){
                Permohonan_pengguna::where('id_permohonan', $id)->delete();

                return $this->output(array('msg' => 'Data berhasil dihapus!'));
            }

            return $this->output(array('msg' => 'Data gagal dihapus'), 'Fail', 400);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function listPengguna(Request $request)
    {
        $validator = $request->validate([
            'idPermohonan' => 'required'
        ]);

        $idPermohonan = decryptor($request->idPermohonan);
        $limit = $request->has('limit') ? $request->limit : 10;
        $page = $request->has('page') ? $request->page : 1;
        $search = $request->has('search') ? $request->search : '';
        $status = $request->has('status') ? $request->status : 1;

        DB::beginTransaction();
        try {
            $query = Permohonan_pengguna::with('media:id,file_hash,file_path')
                        ->where('id_permohonan', $idPermohonan)
                        ->offset(($page - 1) * $limit)
                        ->limit($limit)
                        ->paginate($limit);
            $arr = $query->toArray();
            // dd($arr);
            $this->pagination = Arr::except($arr, 'data');
            DB::commit();

            foreach ($query as $item) {
                $id_radiasi_array = json_decode($item->id_radiasi, true); // Decode JSON jadi array

                // Cek apakah ada array yang valid dari JSON
                if (!empty($id_radiasi_array)) {
                    // Ambil data dari tabel radiasi berdasarkan array 'id_radiasi'
                    $radiasi_data = Master_radiasi::select('nama_radiasi')->whereIn('id_radiasi', $id_radiasi_array)->get();

                    // Tambahkan hasil radiasi ke dalam response
                    $item->radiasi = $radiasi_data;
                }
            }
            
            return $this->output($query);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function listPengajuan(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 10;
        $page = $request->has('page') ? $request->page : 1;
        $search = $request->has('search') ? $request->search : '';
        $status = $request->has('status') ? $request->status : 1;
        
        DB::beginTransaction();
        try {
            $user = Auth::user();
            $role = $user->getRoleNames()[0];

            $query = Permohonan::with(
                            'layanan_jasa:id_layanan,nama_layanan',
                            'jenisTld:id_jenisTld,name', 
                            'jenis_layanan:id_jenisLayanan,name,parent',
                            'jenis_layanan_parent',
                            'pelanggan:id,name')
                    ->when($role, function($q, $role) use ($status) {
                        // Pengecekan role
                        switch ($role) {
                            case 'Pelanggan':
                                $q->where('created_by', Auth::user()->id);
                                $q->whereIn('status', $status);
                                break;
                            case 'Staff Admin':
                                $q->where('status', 1);
                                break;
                            default:
                                # code...
                                break;
                        }

                        return $q;
                    })
                    ->orderBy('created_at','DESC')
                    ->offset(($page - 1) * $limit)
                    ->limit($limit)
                    ->paginate($limit);

            $arr = $query->toArray();
            $this->pagination = Arr::except($arr, 'data');
            DB::commit();

            return $this->output($query);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function getChildJenisLayanan($idParent)
    {
        DB::beginTransaction();
        $parent = decryptor($idParent);

        try {
            // dd($parent);
            $data = Master_jenisLayanan::with('child')->select('id_jenisLayanan','name')->where('id_jenisLayanan', $parent)->first();
            DB::commit();

            if($data){
                $payload = array(
                    'data' => $data,
                    'msg' => 'Data found'
                );
                return $this->output($payload);
            }else{
                return $this->output(array('msg' => 'Data not found'), 'Fail', 404);
            }

        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function getJenisTld($idJenisLayanan)
    {
        DB::beginTransaction();

        $idJenisLayanan = decryptor($idJenisLayanan);
        try {
            $jenisTld = Master_price::with('jenisTld')
                ->select('id_price', 'id_jenisTld', 'keterangan', 'price', 'qty')
                ->whereHas('jenisTld', function ($query) {
                    $query->where('status', 1);
                })
                ->whereJsonContains('id_jenisLayanan', (int)$idJenisLayanan)
                ->get();
            DB::commit();
            
            return $this->output($jenisTld);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function getPrice(Request $request)
    {
        DB::beginTransaction();

        $validator = $request->validate([
            'idJenisLayanan' => 'required',
            'idJenisTld' => 'required'
        ]);

        $idJenisLayanan = decryptor($request->idJenisLayanan);
        $idJenisTld = decryptor($request->idJenisTld);
        $qty = $request->qty ? $request->qty : 1;

        try {
            $price = Master_price::select('price')
                ->where('id_jenisTld', $idJenisTld)
                ->whereJsonContains('id_jenisLayanan', (int)$idJenisLayanan)
                ->first();
                DB::commit();
            
            return $this->output($price);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function verifPermohonan(Request $request)
    {
        $validator = $request->validate([
            'status' => 'required',
        ]);
        $status = $request->status ? $request->status : 'tidak_lengkap';
        
        DB::beginTransaction();
        try {
            $arrayUpdate = array();
            $idPermohonan = $request->idPermohonan ? decryptor($request->idPermohonan) : false;
            if($status == 'lengkap'){
                $ttd = $request->ttd ? $request->ttd : null;

                $no_kontrak = $this->generateNoKontrak($idPermohonan);

                $arrayUpdate['ttd'] = $ttd;
                $arrayUpdate['ttd_by'] = Auth::user()->id;
                $arrayUpdate['status'] = 2;
                $arrayUpdate['no_kontrak'] = $no_kontrak;
            } else {
                $note = $request->note ? $request->note : '';
                $arrayUpdate['note'] = $note;
                $arrayUpdate['status'] = 90;
            }

            Permohonan::where('id_permohonan', $idPermohonan)->update($arrayUpdate);
            DB::commit();

            return $this->output(array('msg' => 'Success'));
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    private function generateNoKontrak($idPermohonan)
    {
        $permohonan = Permohonan::with('jenis_layanan')->where('id_permohonan', $idPermohonan)->first();
        // Menentukan tipe kontrak
        if($permohonan) {
            $jenisLayanan = substr($permohonan->jenis_layanan->name, 0, 1);
            $type = strtoupper($jenisLayanan);

            // Nama aplikasi
            $appName = 'JKRL';

            // Mengambil bulan sekarang dan mengubah ke dalam format Romawi
            $bulanSekarang = date('n'); // n = format angka bulan tanpa nol
            $romawiBulan = getRomawiBulan($bulanSekarang);

            // Tahun saat ini
            $tahunSekarang = date('Y');

            // Incremental number
            $lastContractNumber = Permohonan::whereNotNull('no_kontrak')
                                    ->whereMonth('created_at', $bulanSekarang)
                                    ->whereYear('created_at', $tahunSekarang)
                                    ->count(); // Ubah dengan pengambilan nomor terakhir dari database
            $increment = str_pad($lastContractNumber + 1, 4, '0', STR_PAD_LEFT);

            // Format nomor kontrak
            $noKontrak = "{$type}-{$increment}/{$appName}/{$romawiBulan}/{$tahunSekarang}";

            return $noKontrak;
        }
    }
    // public function addPermohonan(Request $request)
    // {
    //     DB::beginTransaction();
    //     try {
    //         $layanan_id = $request->layanan_hash ? decryptor($request->layanan_hash) : false;
    //         $desc_biaya = $request->desc_biaya ? $request->desc_biaya : false;
    //         $biaya = $request->biaya ? unmask($request->biaya) : false;
    //         $no_bapeten = $request->no_bapeten ? $request->no_bapeten : false;
    //         $jenis_limbah = $request->jenis_limbah ? $request->jenis_limbah : false;
    //         $sumber_radioaktif = $request->sumber_radioaktif ? $request->sumber_radioaktif : false;
    //         $jumlah = $request->jumlah ? $request->jumlah : false;
    //         $documents = $request->file('documents');

    //         $dokumen_pendukung = "";
    //         if($documents){
    //             $arrMedia = array();
    //             foreach ($documents as $key => $document) {
    //                 $idMedia = $this->media->upload($document, 'permohonan');
    //                 array_push($arrMedia, $idMedia);
    //             }

    //             $dokumen_pendukung = json_encode($arrMedia);
    //         }
    //         $data = array(
    //             'layananjasa_id' => $layanan_id,
    //             'jenis_layanan' => $desc_biaya,
    //             'tarif' => $biaya,
    //             'no_bapeten' => $no_bapeten,
    //             'jenis_limbah' => $jenis_limbah,
    //             'sumber_radioaktif' => $sumber_radioaktif,
    //             'jumlah' => $jumlah,
    //             'dokumen' => $dokumen_pendukung,
    //             'status' => 1,
    //             'flag' => 1,
    //             'tag' => 'pengajuan',
    //             'created_by' => Auth::user()->id
    //         );

    //         $permohonan = Permohonan::create($data);

    //         // save to detail permohonan
    //         if(isset($permohonan)){
    //             // reset status detail to 99
    //             $reset = Detail_permohonan::where('permohonan_id', $permohonan->id)->update(['status' => '99']);

    //             Detail_permohonan::create(array(
    //                 'permohonan_id' => $permohonan->id,
    //                 'status' => 1,
    //                 'flag' => 1,
    //                 'created_by' => Auth::user()->id
    //             ));
    //         }
    //         DB::commit();
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Data berhasil disimpan!'
    //         ], 200);

    //     } catch (\Exception $ex) {
    //         info($ex);
    //         DB::rollBack();
    //         return response()->json([
    //             'success' => false,
    //             'message' => $ex->getMessage()
    //         ], 500);
    //     }
    // }

    /**
     * Display the specified resource.
     */
    // public function show(string $id)
    // {
    //     $idHash = decryptor($id);
    //     $dataPermohonan = Permohonan::with(
    //                         'layananjasa:id,nama_layanan',
    //                         'jadwal:id,permohonan_id,date_mulai,date_selesai',
    //                         'user:id,email,name',
    //                         'jadwal.tbl_lhu', 'jadwal.tbl_lhu.jawaban', 'jadwal.tbl_lhu.jawaban.pertanyaan:id,title',
    //                         'tbl_kip', 'tbl_kip.bukti',
    //                         'signature_1:id,name', 'signature_2:id,name')
    //                     ->where('id', $idHash)
    //                     ->orWhere('no_kontrak', $idHash)
    //                     ->first();

    //     // Mengambil data media
    //     $dokumen = isset($dataPermohonan->dokumen) ? json_decode($dataPermohonan->dokumen) : array();
    //     $media = tbl_media::select('id','file_hash','file_ori','file_size','file_type','file_path','created_at')
    //                         ->whereIn('id', $dokumen)
    //                         ->get();
    //     $dataPermohonan->media = count($media) != 0 ? $media : false;

    //     // Mengambil data media petugas
    //     $detailPermohonan = Detail_permohonan::with('media')->where('permohonan_id', $idHash)->where('status', 1)->first();
    //     $dataPermohonan->detailPermohonan = $detailPermohonan;

    //     return $this->output($dataPermohonan);
    // }

    /**
     * Update the specified resource in storage.
     */
    // public function update(Request $request, string $idHash)
    // {
    //     $idPermohonan = decryptor($idHash);
    //     $status = isset($request->status) ? $request->status : null;
    //     $tag = isset($request->tag) ? $request->tag : null;
    //     $flag = isset($request->flag) ? $request->flag : null;
    //     $jenis_limbah = isset($request->jenis_limbah) ? $request->jenis_limbah : null;
    //     $sumber_radioaktif = isset($request->sumber_radioaktif) ? $request->sumber_radioaktif : null;
    //     $jumlah = isset($request->jumlah) ? $request->jumlah : null;
    //     $nomor_antrian = isset($request->nomor_antrian) ? $request->nomor_antrian : null;
    //     $jadwal_id = isset($request->jadwal_id) ? decryptor($request->jadwal_id) : null;
    //     $no_bapeten = isset($request->no_bapeten) ? $request->no_bapeten : null;
    //     $desc_biaya = isset($request->desc_biaya) ? $request->desc_biaya : null;
    //     $biaya = isset($request->biaya) ? unmask($request->biaya) : null;
    //     $ttd_1 = isset($request->ttd_1) ? $request->ttd_1 : null;
    //     $ttd_1_by = isset($request->ttd_1_by) ? $request->ttd_1_by : null;
    //     $ttd_2 = isset($request->ttd_2) ? $request->ttd_2 : null;
    //     $ttd_2_by = isset($request->ttd_2_by) ? $request->ttd_2_by : null;
    //     $note = isset($request->note) ? $request->note : null;

    //     DB::beginTransaction();
    //     try {
    //         $permohonan = Permohonan::findOrFail($idPermohonan);

    //         $status && $permohonan->status = $status;
    //         $tag && $permohonan->tag = $tag;
    //         $flag && $permohonan->flag = $flag;
    //         $jenis_limbah && $permohonan->jenis_limbah = $jenis_limbah;
    //         $sumber_radioaktif && $permohonan->sumber_radioaktif = $sumber_radioaktif;
    //         $jumlah && $permohonan->jumlah = $jumlah;
    //         $nomor_antrian && $permohonan->nomor_antrian = $nomor_antrian;
    //         $jadwal_id && $permohonan->jadwal_id = $jadwal_id;
    //         $no_bapeten && $permohonan->no_bapeten = $no_bapeten;
    //         $desc_biaya && $permohonan->jenis_layanan = $desc_biaya;
    //         $biaya && $permohonan->tarif = $biaya;
    //         isset($ttd_1) && ($ttd_1 == 'false' ? $permohonan->ttd_1 = null : $permohonan->ttd_1 = $ttd_1);
    //         isset($ttd_1_by) && ($ttd_1_by == 'false' ? $permohonan->ttd_1_by = null : $permohonan->ttd_1_by = decryptor($ttd_1_by));
    //         isset($ttd_2) && ($ttd_2 == 'false' ? $permohonan->ttd_2 = null : $permohonan->ttd_2 = $ttd_2);
    //         isset($ttd_2_by) && ($ttd_2_by == 'false' ? $permohonan->ttd_2_by = null : $permohonan->ttd_2_by = decryptor($ttd_2_by));

    //         $permohonan->update();

    //         // Add log permohonan Front desk
    //         if($ttd_1 && $ttd_1 != 'false') {
    //             $tmp_log = array(
    //                 'permohonan_id' => $idPermohonan,
    //                 'note' => 'Berkas permohonan lengkap',
    //                 'status' => 1,
    //                 'flag' => 1, // Front desk
    //                 'created_by' => Auth::user()->id
    //             );

    //             Detail_permohonan::create($tmp_log);
    //         } else if($note){
    //             $tmp_log = array(
    //                 'permohonan_id' => $idPermohonan,
    //                 'note' => $note,
    //                 'status' => $status ? $status : $permohonan->status,
    //                 'flag' => $permohonan->flag,
    //                 'created_by' => Auth::user()->id
    //             );

    //             Detail_permohonan::create($tmp_log);
    //         }


    //         DB::commit();
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Data berhasil diupdate!'
    //         ], 200);
    //     } catch (\Exception $ex) {
    //         info($ex);
    //         DB::rollBack();
    //         return response()->json([
    //             'success' => false,
    //             'message' => $ex->getMessage()
    //         ], 500);
    //     }
    // }

    /**
     * Remove the specified resource from storage.
     */
    // public function destroy(string $id)
    // {
    //     $idHash = decryptor($id);

    //     Detail_permohonan::where('permohonan_id', $idHash)->update([
    //         'status' => '99'
    //     ]);

    //     $delete = Permohonan::findOrFail($idHash);
    //     $delete->status = '99';
    //     $delete->update();

    //     return response()->json(['message' => 'Berhasil di hapus'], 200);
    // }

    // public function verifikasi_kontrak(Request $request)
    // {
    //     $validator = $request->validate([
    //         'file' => 'required',
    //         'id' => 'required',
    //         'note' => 'required',
    //         'status' => 'required'
    //     ]);

    //     $idPermohonan = decryptor($request->id);

    //     $this->detail->reset($idPermohonan);

    //     $tmp_arr = array(
    //         'permohonan_id' => $idPermohonan,
    //         'note' => $request->note,
    //         'status' => 1,
    //         'flag' => $request->status == 3 ? 3 : 2,
    //         'created_by' => Auth::user()->id
    //     );

    //     // upload Surat
    //     $dokumen = $request->file('file');
    //     if($dokumen){
    //         $tmp_arr['surat_terbitan'] = $this->media->upload($dokumen, 'pelaksana');
    //     }

    //     $noKontrak = 'K'.generate();

    //     $data_permohonan = Permohonan::findOrFail($idPermohonan);
    //     $data_permohonan->flag = $request->status == 3 ? 3 : 2;

    //     $data_permohonan->status = $request->status;

    //     $request->status != 9 ? $data_permohonan->no_kontrak = $noKontrak : false;

    //     $data_permohonan->update();

    //     Detail_permohonan::create($tmp_arr);

    //     return response()->json(['message' => 'success'], 200);
    // }


    // public function verifikasi_fd(Request $request){
    //     $validator = $request->validate([
    //         'id' => 'required',
    //         'status' => 'required'
    //     ]);

    //     $idPermohonan = decryptor($request->id);
    //     $type = isset($request->type) ? $request->type : null;

    //     $data_permohonan = Permohonan::findOrFail($idPermohonan);
    //     $data_permohonan->flag = $request->status == 2 ? 2 : 1;
    //     if($request->status == 9) {
    //         $data_permohonan->status = 9;
    //     }
    //     $data_permohonan->update();

    //     // add to log
    //     $tmp_log = array(
    //         'permohonan_id' => $idPermohonan,
    //         'note' => $request->note,
    //         'status' => 9,
    //         'flag' => 1, // Front desk
    //         'created_by' => Auth::user()->id
    //     );

    //     Detail_permohonan::create($tmp_log);

    //     // // Notifikasi
    //     // $notif = notifikasi(array(
    //     //     'to_user' => $data_permohonan->created_by,
    //     //     'type' => 'Permohonan'
    //     // ), "Permohonan ".$data_permohonan->layananjasa->nama_layanan." di $text");

    //     return response()->json(['message' => 'success'], 200);
    // }

    // public function sendSuratTugas(Request $request)
    // {
    //     $validator = $request->validate([
    //         'file' => 'required',
    //         'no_kontrak' => 'required'
    //     ]);

    //     $lampiran = $request->file('file');
    //     $surat_tugas = null;
    //     if($lampiran){
    //         $surat_tugas = $this->media->upload($lampiran, 'surat_tugas');
    //     }

    //     $data_permohonan = Permohonan::where('no_kontrak', $request->no_kontrak)->first();

    //     $arr = array(
    //         'no_kontrak' => $request->no_kontrak,
    //         'level' => 1,
    //         'active' => 9,
    //         'surat_tugas' => $surat_tugas,
    //         'created_by' => Auth::user()->id
    //     );

    //     $create = tbl_lhu::create($arr);

    //     if($create){
    //         $payload = array(
    //             'message' => 'Berhasil di kirim'
    //         );

    //         return $this->output($payload);
    //     }else{
    //         return response()->json([
    //             'message' => 'Gagal mengirim surat tugas'
    //         ], 400);
    //     }

    // }
}
