<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Traits\RestApi;

use App\Models\Permohonan;
use App\Models\Permohonan_dokumen;
use App\Models\Permohonan_pengguna;
use App\Models\Permohonan_tandaterima;
use App\Models\Master_layanan_jasa;
use App\Models\Master_jenisLayanan;
use App\Models\Master_media;
use App\Models\Master_radiasi;
use App\Models\Master_price;
use App\Models\Master_jenistld;
use App\Models\Master_tld;
use App\Models\Kontrak;
use App\Models\Kontrak_pengguna;
use App\Models\Kontrak_periode;

use App\Http\Controllers\MediaController;
use App\Http\Controllers\LogController;

use Auth;
use DB;
use Log;

class PermohonanAPI extends Controller
{
    use RestApi;

    public function __construct(){
        $this->media = resolve(MediaController::class);
        $this->log = resolve(LogController::class);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function tambahPengajuan(Request $request)
    {
        DB::beginTransaction();
        try {
            $idPermohonan = $request->idPermohonan ? decryptor($request->idPermohonan) : false;
            $idLayanan = $request->idLayanan ? decryptor($request->idLayanan) : false;
            $jenisLayanan1 = $request->jenisLayanan1 ? decryptor($request->jenisLayanan1) : false;
            $jenisLayanan2 = $request->jenisLayanan2 ? decryptor($request->jenisLayanan2) : false;

            $tipeKontrak = $request->tipeKontrak ? $request->tipeKontrak : false;
            $idKontrak = $request->idKontrak ? decryptor($request->idKontrak) : null;
            $jenisTld = $request->jenisTld ? decryptor($request->jenisTld) : null;
            $periode = $request->periode ? $request->periode : false;
            $jumlahPengguna = $request->jumlahPengguna ? $request->jumlahPengguna : false;
            $jumlahKontrol = $request->jumlahKontrol ? $request->jumlahKontrol : false;
            $totalHarga = $request->totalHarga ? $request->totalHarga : false;
            $hargaLayanan = $request->hargaLayanan ? $request->hargaLayanan : false;
            $dataTld = $request->dataTld ? json_decode($request->dataTld) : false;
            $createBy = $request->createBy ? decryptor($request->createBy) : false;
            $status = $request->status ? $request->status : 1;
            $periodePemakaian = $request->periodePemakaian ? $request->periodePemakaian : false;
            $pic = $request->pic ? $request->pic : false;
            $noHp = $request->noHp ? unmask($request->noHp) : false;
            $alamat = $request->alamat ? decryptor($request->alamat) : false;
            
            if ($periodePemakaian) {
                if (is_string($periodePemakaian)) {
                    $periodePemakaian = json_decode($periodePemakaian, true); // Use true for associative array
                }
                // Add validation to ensure $periodePemakaian is now an array after decoding if needed
                if (!is_array($periodePemakaian) && $periodePemakaian != false) {
                  throw new \Exception("Invalid periodePemakaian format. Must be a JSON string or an array.");
                }
            } else {
                $periodePemakaian = false;
            }
            

            $data = array();

            $idLayanan && $data['id_layanan'] = $idLayanan;
            $jenisLayanan1 && $data['jenis_layanan_1'] = $jenisLayanan1;
            $jenisLayanan2 && $data['jenis_layanan_2'] = $jenisLayanan2;

            $tipeKontrak && $data['tipe_kontrak'] = $tipeKontrak;
            $idKontrak && $data['id_kontrak'] = $idKontrak;
            $periodePemakaian && $data['periode_pemakaian'] = $periodePemakaian;
            $periode && $data['periode'] = $periode;
            $jenisTld && $data['jenis_tld'] = $jenisTld;
            $jumlahPengguna && $data['jumlah_pengguna'] = $jumlahPengguna;
            $jumlahKontrol && $data['jumlah_kontrol'] = $jumlahKontrol;
            $totalHarga && $data['total_harga'] = unmask($totalHarga);
            $hargaLayanan && $data['harga_layanan'] = $hargaLayanan;
            $dataTld && $data['list_tld'] = $dataTld;
            $createBy && $data['created_by'] = $createBy;
            $pic && $data['pic'] = $pic;
            $noHp && $data['no_hp'] = $noHp;
            $alamat && $data['id_alamat'] = $alamat;

            $status && $data['status'] = $status;
            $data['flag_read'] = 0;

            // jika tipe kontraknya adalah "kontrak lama" akan mengambil data dari kontrak sebelumnya
            if($tipeKontrak == 'kontrak lama'){
                $kontrak = Kontrak::with('pengguna','periode')->find($idKontrak);
                if($kontrak){
                    $data['id_layanan'] = $kontrak->id_layanan;
                    $data['jenis_tld'] = $kontrak->jenis_tld;
                    $data['jumlah_pengguna'] = $kontrak->jumlah_pengguna;
                    $data['jumlah_kontrol'] = $kontrak->jumlah_kontrol;
                    $data['total_harga'] = $kontrak->total_harga;
                    $data['harga_layanan'] = $kontrak->harga_layanan;
                }
            }
            // dd($data);
            // Save to db
            $permohonan = Permohonan::updateOrCreate(
                ['id_permohonan' => $idPermohonan],
                $data
            );

            if($tipeKontrak == 'kontrak lama'){
                Kontrak_periode::where('id_kontrak', $idKontrak)
                    ->where('periode', $periode)
                    ->update(array('id_permohonan' => $permohonan->id_permohonan));
            }

            DB::commit();

            if($permohonan) {
                // tambah log permohonan
                $note = $this->log->noteLog('permohonan', 1);
                $this->log->addLog('permohonan', array(
                    'id_permohonan' => $idPermohonan,
                    'status' => 1,
                    'note' => $note,
                    'created_by' => Auth::user()->id
                ));

                return $this->output(array('msg' => 'Data berhasil disimpan!', 'id' => $permohonan->permohonan_hash));
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
                $idRadiasi = decryptor($value);
                array_push($radiasi, $idRadiasi ? $idRadiasi : $value);
            }

            $ktp = $request->file('ktp');
    
            $file_ktp = $this->media->upload($ktp, 'permohonan');
            
            $create = Permohonan_pengguna::create(array(
                'id_permohonan' => $idPermohonan,
                'nama' => $nama,
                'posisi' => $divisi,
                'id_radiasi' => $radiasi,
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

    public function tambahTandaterima(Request $request)
    {
        $validator = $request->validate([
            'idPermohonan' => 'required'
        ]);
        
        $tandaterima = $request->tandaterima ? json_decode($request->tandaterima) : [];
        $idPermohonan = decryptor($request->idPermohonan);
        DB::beginTransaction();
        try {
            foreach ($tandaterima as $value) {
                $params = array(
                    'id_permohonan' => $idPermohonan,
                    'id_pertanyaan' => decryptor($value->id),
                    'jawaban' => $value->answer,
                    'note' => $value->note,
                    'created_by' => Auth::user()->id
                );
    
                Permohonan_tandaterima::create($params);
            }
            $dataTandaterima = Permohonan_tandaterima::where('id_permohonan', $idPermohonan)->get();

            // Simpan dokumen tandaterima
            $document = Permohonan_dokumen::create(array(
                'id_permohonan' => $idPermohonan,
                'created_by' => Auth::user()->id,
                'nama' => 'Tanda Terima Pengujian',
                'jenis' => 'tandaterima',
                'status' => 1,
                'nomer' => generateNoDokumen('tandaterima')
            ));
            DB::commit();

            return $this->output(array('msg' => 'Data berhasil disimpan', 'information' => $dataTandaterima));
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
            // menghilangkan id_permohonan yang ada di tabel kontrak periode
            Kontrak_periode::where('id_permohonan', $id)->update(array('id_permohonan' => null));
            
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

    public function destroyTandaterima(string $id)
    {
        $id = decryptor($id);

        DB::beginTransaction();
        try {
            Permohonan_dokumen::where('id_permohonan', $id)->where('jenis', 'tandaterima')->delete();
            $delete = Permohonan_tandaterima::where('id_permohonan', $id)->delete();
            DB::commit();

            if($delete){
                return $this->output(array('msg' => 'Data berhasil dihapus'));
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
            // mengambil tipe_kontrak yang ada di tabel permohonan untuk di kondisikan
            // jika kontrak baru akan menggunakan permohonan_pengguna jika kontrak lama akan menggunakan kontrak_pengguna
            $permohonan = Permohonan::select('tipe_kontrak','id_kontrak')->where('id_permohonan', $idPermohonan)->first();

            if($permohonan->tipe_kontrak == 'kontrak lama'){
                $query = Kontrak_pengguna::with('media:id,file_hash,file_path')
                        ->where('id_kontrak', $permohonan->id_kontrak)
                        ->offset(($page - 1) * $limit)
                        ->limit($limit)
                        ->paginate($limit);
            }else{
                $query = Permohonan_pengguna::with('media:id,file_hash,file_path')
                            ->where('id_permohonan', $idPermohonan)
                            ->offset(($page - 1) * $limit)
                            ->limit($limit)
                            ->paginate($limit);
            }
            $arr = $query->toArray();
            
            $this->pagination = Arr::except($arr, 'data');
            $resTld = $this->searchTldNotUsed('pengguna');
            $noTld = 0;

            foreach ($query as $item) {
                // mengecek informasi tld
                if(!$item->id_tld){
                    if($resTld['data'][$noTld]){
                        $item->tld = $resTld['data'][$noTld];
                    }else{
                        $item->tld = false;
                    }
                    $noTld++;
                }

                // mengambil data radiasi
                $id_radiasi_array = $item->id_radiasi; // Decode JSON jadi array

                // Cek apakah ada array yang valid dari JSON
                if (!empty($id_radiasi_array)) {
                    // Ambil data dari tabel radiasi berdasarkan array 'id_radiasi'
                    $arrDataRadiasi = array();
                    foreach ($id_radiasi_array as $key => $value) {
                        $nama_radiasi = "";
                        $radiasi_data = Master_radiasi::select('nama_radiasi')->where('id_radiasi', $value)->first();
                        if($radiasi_data){
                            $nama_radiasi = $radiasi_data->nama_radiasi;
                        }else{
                            $nama_radiasi = $value;
                        }
                        array_push($arrDataRadiasi, $nama_radiasi);
                    }

                    // Tambahkan hasil radiasi ke dalam response
                    $item->radiasi = $arrDataRadiasi;
                }else{

                }
            }
            DB::commit();
            
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
                        'pelanggan:id,name',
                        'kontrak',
                        'kontrak.layanan_jasa:id_layanan,nama_layanan',
                        'kontrak.jenisTld:id_jenisTld,name', 
                        'kontrak.jenis_layanan:id_jenisLayanan,name,parent',
                        'kontrak.jenis_layanan_parent',
                    )
                    ->when($role, function($q, $role) use ($status) {
                        // Pengecekan role
                        switch ($role) {
                            case 'Pelanggan':
                                $q->where('created_by', Auth::user()->id);
                                $q->whereIn('status', $status);
                                break;
                            case 'Staff Admin':
                                // $q->where('status', 1);
                                break;
                            case 'Staff keuangan':
                                // $q->whereIn('status', [2]);
                                break;
                            default:
                                # code...
                                break;
                        }

                        return $q;
                    })
                    ->where('status', '!=', 11)
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

    public function getPengajuanById($id)
    {
        DB::beginTransaction();
        try {
            $id = decryptor($id);
            $query = Permohonan::with(
                'layanan_jasa:id_layanan,nama_layanan',
                'jenisTld:id_jenisTld,name', 
                'jenis_layanan:id_jenisLayanan,name,parent',
                'jenis_layanan_parent',
                'pelanggan:id,name,id_perusahaan',
                'pelanggan.perusahaan',
                'kontrak',
                'kontrak.periode',
                'kontrak.jenis_layanan',
                'kontrak.layanan_jasa:id_layanan,nama_layanan',
                'kontrak.jenisTld:id_jenisTld,name',
                'pengguna',
                'pengguna.media',
                'tandaterima',
                'dokumen',
                'invoice'
            )->where('id_permohonan', $id)->first();
            DB::commit();

            foreach ($query->pengguna as $item) {
                $id_radiasi_array = $item->id_radiasi; // Decode JSON jadi array

                // Cek apakah ada array yang valid dari JSON
                if (!empty($id_radiasi_array)) {
                    // Ambil data dari tabel radiasi berdasarkan array 'id_radiasi'
                    $arrDataRadiasi = array();
                    foreach ($id_radiasi_array as $key => $value) {
                        $nama_radiasi = "";
                        $radiasi_data = Master_radiasi::select('nama_radiasi')->where('id_radiasi', $value)->first();
                        if($radiasi_data){
                            $nama_radiasi = $radiasi_data->nama_radiasi;
                        }else{
                            $nama_radiasi = $value;
                        }
                        array_push($arrDataRadiasi, $nama_radiasi);
                    }

                    // Tambahkan hasil radiasi ke dalam response
                    $item->radiasi = $arrDataRadiasi;
                }
            }

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
            $data = Master_jenisLayanan::with(['child' => function ($query) {
                $query->where('status', 1);
            }])
            ->select('id_jenisLayanan','name')
            ->where('id_jenisLayanan', $parent)
            ->first();
            
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
            $dataPermohonan = Permohonan::with('pengguna')->where('id_permohonan', $idPermohonan)->first();
            if($dataPermohonan){
                if($status == 'lengkap'){
                    $ttd = $request->ttd ? $request->ttd : null;
                    $fileLhu = $request->file('fileLhu') ?? false;
                    $fileLhu && $fileLhu = $this->media->upload($fileLhu, 'permohonan');
                    $tldKontrol = $request->tldKontrol ? json_decode($request->tldKontrol) : [];
                    $no_kontrak = null;
    
                    // mengecek apakah harus generate kontrak atau tidak
                    switch ($dataPermohonan->jenis_layanan_2) {
                        case 2: // kontrak - sewa
                        case 3: // kontrak - Evaluasi
                        case 5: // evaluasi - Dengan kontrak
                        case 8: // zero cek - Dengan kontrak
                            if($dataPermohonan->tipe_kontrak == 'kontrak baru'){
                                $no_kontrak = $this->generateNoKontrak($idPermohonan);
                            }
                            break;
                    }

                    // menambahkan tld
                    if($dataPermohonan->tipe_kontrak == 'kontrak baru'){
                        $listTld = $request->listTld ? json_decode($request->listTld) : [];
                        foreach ($listTld as $item) {
                            $id = decryptor($item->tld) ? (['id_tld' => decryptor($item->tld)]) : ['kode_lencana' => $item->tld];
                            // dd($id);
                            $tldData = Master_tld::updateOrCreate($id, [
                                'status' => 1,
                                'jenis' => 'pengguna',
                            ]);
                            Permohonan_pengguna::where('id_pengguna', decryptor($item->id))->update(['id_tld' => $tldData->id_tld]);
                        }
                    }

                    $arrayUpdate['list_tld'] = array();
                    foreach ($tldKontrol as $key => $value) {
                        $id = decryptor($value->tld) ? (['id_tld' => decryptor($value->tld)]) : ['kode_lencana' => $value->tld];
                        
                        $kontrol = Master_tld::updateOrCreate($id, [
                            'status' => 1,
                            'jenis' => 'kontrol',
                        ]);
                        $arrayUpdate['list_tld'][$key] = (int) decryptor($kontrol->tld_hash);
                    }
    
                    $arrayUpdate['ttd'] = $ttd;
                    $arrayUpdate['ttd_by'] = Auth::user()->id;
                    $arrayUpdate['verify_at'] = date('Y-m-d H:i:s');
                    $arrayUpdate['status'] = 2; // pengajuan di setujui oleh front desk

                    $fileLhu && $arrayUpdate['file_lhu'] = $fileLhu->getIdMedia();

                    $dataPermohonan->update($arrayUpdate);

                    $dataPermohonan = Permohonan::with(
                        'kontrak',
                        'jenis_layanan_parent',
                        'jenisTld',
                        'layanan_jasa',
                    )->find($idPermohonan);
                    // Memindahkan Permohonan ke tabel kontrak
                    switch ($dataPermohonan->jenis_layanan_1) {
                        case 1: // Kontrak
                        case 7: // Zero cek
                            $params = array(
                                'id_layanan' => $dataPermohonan->id_layanan,
                                'jenis_layanan_1' => $dataPermohonan->jenis_layanan_1,
                                'jenis_layanan_2' => $dataPermohonan->jenis_layanan_2,
                                'tipe_kontrak' => $dataPermohonan->tipe_kontrak,
                                'no_kontrak' => $no_kontrak,
                                'jenis_tld' => $dataPermohonan->jenis_tld,
                                'jumlah_pengguna' => $dataPermohonan->jumlah_pengguna,
                                'jumlah_kontrol' => $dataPermohonan->jumlah_kontrol,
                                'total_harga' => $dataPermohonan->total_harga,
                                'harga_layanan' => $dataPermohonan->harga_layanan,
                                'list_tld' => $dataPermohonan->list_tld,
                                'ttd' => $dataPermohonan->ttd,
                                'ttd_by' => $dataPermohonan->ttd_by,
                                'status' => 1,
                                'note' => $dataPermohonan->note,
                                'file_lhu' => $dataPermohonan->file_lhu,
                                'id_pelanggan' => $dataPermohonan->created_by,
                                'created_by' => Auth::user()->id
                            );
                            $dataKontrak = Kontrak::create($params);

                            foreach ($dataPermohonan->pengguna as $key => $value) {
                                $paramsPengguna = array(
                                    'id_kontrak' => $dataKontrak->id_kontrak,
                                    'id_tld' => $value->id_tld,
                                    'nama' => $value->nama,
                                    'posisi' => $value->posisi,
                                    'id_radiasi' => $value->id_radiasi,
                                    'file_ktp' => $value->file_ktp,
                                    'status' => $value->status,
                                    'created_by' => Auth::user()->id
                                );

                                Kontrak_pengguna::create($paramsPengguna);
                            }

                            // Tambah periode
                            if($dataPermohonan->periode_pemakaian){
                                // zero cek
                                Kontrak_periode::create(array(
                                    'id_kontrak' => $dataKontrak->id_kontrak,
                                    'periode' => 0,
                                    'start_date' => null,
                                    'end_date' => null,
                                    'status' => 1,
                                    'id_permohonan' => $dataPermohonan->id_permohonan,
                                    'created_by' => Auth::user()->id,
                                    'created_at' => date('Y-m-d H:i:s')
                                ));

                                foreach ($dataPermohonan->periode_pemakaian as $key => $value) {
                                    $periode = $key + 1;

                                    $paramsPeriode = array(
                                        'id_kontrak' => $dataKontrak->id_kontrak,
                                        'periode' => $periode,
                                        'start_date' => $value['start_date'],
                                        'end_date' => $value['end_date'],
                                        'status' => 1,
                                        'id_permohonan' => null,
                                        'created_by' => Auth::user()->id,
                                        'created_at' => date('Y-m-d H:i:s')
                                    );
                                    Kontrak_periode::create($paramsPeriode);
                                }
                            }

                            // Menambahkan id_kontrak ke table permohonan 
                            $dataPermohonan->update(array('id_kontrak' => $dataKontrak->id_kontrak));

                            // menambahkan dokumen perjanjian 
                            $data = array(
                                'id_permohonan' => $idPermohonan,
                                'created_by' => Auth::user()->id,
                                'nama' => 'Perjanjian-'.$dataPermohonan->jenis_layanan_parent->name.'-'.$dataPermohonan->layanan_jasa->nama_layanan.'-'.$dataPermohonan->jenisTld->name.'-'.convert_date($arrayUpdate['verify_at'], 6),
                                'jenis' => 'perjanjian',
                                'status' => 1,
                                'nomer' => $no_kontrak
                            );
                            $document = Permohonan_dokumen::create($data);
                            break;
                    }

                    /* 
                        JENIS LAYANAN

                        2 Kontrak - Sewa (upload document lhu zero cek dan disimpan ke file_lhu)
                        3 Kontrak - Evaluasi
                        5 Evaluasi - Dengan kontrak
                        6 Evaluasi - Tanpa kontrak
                        8 Zero cek - Dengan kontrak
                        9 Zero cek - Tanpa kontrak
                    */

                    // proses ke invoice 
                    $arrValidInvoice = [2, 3, 6];
                    if(in_array($dataPermohonan->jenis_layanan_2, $arrValidInvoice)){
                        $invoiceData = $this->createInvoice($dataPermohonan->permohonan_hash);

                        if(!$invoiceData){
                            throw new \Exception('Gagal membuat invoice');
                        }
                    }

                    // Proses ke penyelia
                    $arrValidPenyelia = [2, 3, 5, 6];
                    if(in_array($dataPermohonan->jenis_layanan_2, $arrValidPenyelia)){
                        $penyeliaData = $this->createPenyelia($dataPermohonan->permohonan_hash);

                        if(!$penyeliaData){
                            throw new \Exception('Gagal membuat penyelia');
                        }
                    }

                } else {
                    $note = $request->note ? $request->note : '';
                    $arrayUpdate['note'] = $note;
                    $arrayUpdate['status'] = 90; // Pengajuan di tolak oleh front desk
                    $dataPermohonan->update($arrayUpdate);
                }
    
                DB::commit();
            }

            $fileLhu && $fileLhu->store();
            return $this->output(array('msg' => 'Success'));
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function uploadLhuZeroCek(Request $request)
    {
        $validator = $request->validate([
            'idHash' => 'required',
            'file' => 'required|file'
        ]);

        DB::beginTransaction();
        try {
            $idPermohonan = decryptor($request->idHash);
            $file = $request->file('file');

            $fileUpload = $this->media->upload($file, 'permohonan');
            $dataPermohonan = Permohonan::find($idPermohonan);

            if(isset($dataPermohonan)){
                $dataPermohonan->update(array('file_lhu' => $fileUpload->getIdMedia()));
                DB::commit();

                if($dataPermohonan){
                    $fileUpload->store();
                    // ambil media Document Lhu
                    $mediaDocLhu = $this->media->get($fileUpload->getIdMedia());
                    return $this->output(array('msg' => 'LHU berhasil diupload', 'data' => $mediaDocLhu));
                }

                return $this->output(array('msg' => 'LHU gagal diupload'), 'Fail', 400);
            }

            return $this->output(array('msg' => 'data not found'), 'Fail', 400);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function destroyLhuZero($idPermohonan, $idMedia){
        $idMedia = decryptor($idMedia);
        $idPermohonan = decryptor($idPermohonan);

        DB::beginTransaction();
        try {
            $dataPermohonan = Permohonan::find($idPermohonan);
            if(isset($dataPermohonan)){
                $update = $dataPermohonan->update(array('file_lhu' => null));
                $this->media->destroy($idMedia);

                DB::commit();

                if($update){
                    return $this->output(array('msg' => 'LHU berhasil dihapus'));
                }

                return $this->output(array('msg' => 'LHU gagal dihapus'), 'Fail', 400);
            }

            return $this->output(array('msg' => 'data not found'), 'Fail', 400);
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
            $lastContractNumber = Kontrak::whereNotNull('no_kontrak')
                                    ->whereMonth('created_at', $bulanSekarang)
                                    ->whereYear('created_at', $tahunSekarang)
                                    ->count(); // Ubah dengan pengambilan nomor terakhir dari database
            $increment = str_pad($lastContractNumber + 1, 4, '0', STR_PAD_LEFT);

            // Format nomor kontrak
            $noKontrak = "{$type}-{$increment}/{$appName}/{$romawiBulan}/{$tahunSekarang}";

            return $noKontrak;
        }
    }

    private function createInvoice($idPermohonan){
        $params = [
            'idPermohonan' => $idPermohonan,
            'status' => 1
        ];

        // Make a request to your keuanganAction endpoint
        $keuanganResponse = app()->handle(Request::create(url('api/v1/keuangan/action'), 'POST', $params));

        // Check the response for success/failure
        if ($keuanganResponse->getStatusCode() == 200) {
            // Invoice creation successful - you can log or further process if needed
            $invoiceData = json_decode($keuanganResponse->getContent(), true);
            // ... process $invoiceData
            return $invoiceData;
        } else {
            // Handle invoice creation failure appropriately (log, rollback, etc.)
            Log::error("Invoice creation failed: " . $keuanganResponse->getContent());
            // ... consider throwing an exception or other error handling
        }
    }

    private function createPenyelia($idPermohonan){
        $params = [
            'idPermohonan' => $idPermohonan,
            'status' => 1
        ];

        // Make a request to your keuanganAction endpoint
        $penyeliaResponse = app()->handle(Request::create(url('api/v1/penyelia/action'), 'POST', $params));

        // Check the response for success/failure
        if ($penyeliaResponse->getStatusCode() == 200) {
            // Invoice creation successful - you can log or further process if needed
            $penyeliaData = json_decode($penyeliaResponse->getContent(), true);
            // ... process $penyeliaData
            return $penyeliaData;
        } else {
            // Handle invoice creation failure appropriately (log, rollback, etc.)
            Log::error("Penyelia creation failed: " . $penyeliaResponse->getContent());
            // ... consider throwing an exception or other error handling
        }
    
    }

    private function searchTldNotUsed($jenis){
        $params = [
            'jenis' => $jenis
        ];
        // Make a request to your keuanganAction endpoint
        $TldResponse = app()->handle(Request::create(url('api/v1/tld/searchTldNotUsed'), 'GET', $params));

        // Check the Tldresponse for success/failure
        if ($TldResponse->getStatusCode() == 200) {
            // Invoice creation successful - you can log or further process if needed
            $TldData = json_decode($TldResponse->getContent(), true);
            // ... process $TldData
            return $TldData;
        } else {
            // Handle invoice creation failure appropriately (log, rollback, etc.)
            Log::error("failed: " . $TldResponse->getContent());
            // ... consider throwing an exception or other error handling
        }
    }
}