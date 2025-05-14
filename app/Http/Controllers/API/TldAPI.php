<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

use App\Models\Master_tld;
use App\Traits\RestApi;

use DB;
use Auth;

class TldAPI extends Controller
{
    use RestApi;

    public function action(Request $request)
    {
        DB::beginTransaction();
        try {
            $id = $request->id ? decryptor($request->id) : false;
            $kode = $request->has('kode') ? $request->kode : false;
            $jenis = $request->has('jenis') ? $request->jenis : false;
            $status = $request->has('status') ? $request->status : false;

            $data = array();

            $kode ? $data['kode'] = $kode : false;
            $jenis ? $data['jenis'] = $jenis : false;
            $status ? $data['status'] = $status : false;

            $id && $data['id'] = $id;

            //save to db
            $tld = Master_tld::updateOrCreate(
                ['id_tld' => $id],
                $data
            );

            DB::commit();
            return $this->output(array('msg' => 'Data berhasil disimpan!', 'id' => $tld->tld_hash));
        } catch (\Exception $ex ) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function searchTld(Request $request){
        DB::beginTransaction();
        try {
            $jenis = $request->has('jenis') ? $request->jenis : false;
            $kode_lencana = $request->has('kode_lencana') ? $request->kode_lencana : false;
            $limit = 10;
            $data = array();

            if(!empty($kode_lencana)){
                $data = Master_tld::where('jenis', $jenis)
                    ->where('kode_lencana', 'like', '%'.$kode_lencana.'%')
                    ->limit($limit)
                    ->orderBy('status', 'desc')->get();
            } else {
                $data = Master_tld::limit($limit)->where('jenis', $jenis)->orderBy('status', 'desc')->get();
            }

            DB::commit();
            return $this->output($data, 200);
        } catch (\Exception $ex ) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function searchTldNotUsed(Request $request)
    {
        $request->validate([
            'jenis' => 'required'
        ]);

        DB::beginTransaction();
        try {
            $jenis = $request->has('jenis') ? $request->jenis : false;

            $data = Master_tld::where('status', 0)->whereNull('kepemilikan')->where('jenis', $jenis)->get();

            DB::commit();
            return $this->output($data, 200);
        } catch (\Exception $ex ) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function getById($id) {
        DB::beginTransaction();
        try {
            // $id = decryptor($id);
            $data = Master_tld::find($id);
            DB::commit();
            return $this->output($data, 200);
        } catch (\Exception $ex ) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function getData(Request $request) {
        DB::beginTransaction();
        try {
            $jenis = $request->has('jenis') ? $request->jenis : false;
            $status = $request->has('status') ? $request->status : false;
            $search = $request->has('search') ? $request->search : false;
            $no_kontrak = $request->has('no_kontrak') ? $request->no_kontrak : false;

            $page = $request->has('page') ? $request->page : 1;
            $limit = $request->has('limit') ? $request->limit : 5;

            // pengecekan role user
            $role = Auth::user()->getRoleNames()[0];

            // pengecekan tld yang sedang digunakan oleh kontrak
            $cekTldKontrak = false;
            if($role != 'Pelanggan' && $no_kontrak){
                $cekTldKontrak = Master_tld::where('digunakan', $no_kontrak)->where('status', 0)->first();
            }

            $data = Master_tld::when($role, function($query, $role){
                if($role == 'Pelanggan'){
                    return $query->where('kepemilikan', Auth::user()->id_perusahaan);
                }else {
                    return $query->whereNull('kepemilikan');
                }
            })
            ->when($cekTldKontrak, function($query, $cekTldKontrak) use ($no_kontrak){
                return $query->where('digunakan', $no_kontrak)->where('status', 0);
            })
            ->when($jenis, function($query, $jenis){
                return $query->where('jenis', $jenis);
            })
            ->when($status, function($query, $status){
                return $query->where('status', $status);
            })
            ->when($search, function($query, $search){
                return $query->where('no_seri_tld', 'like', '%'.$search.'%')->orWhere('merk', 'like', '%'.$search.'%');
            })
            ->orderBy('status', 'asc')
            ->orderBy('jenis', 'desc')
            // ->orderBy('tanggal_pengadaan', 'desc')
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->paginate($limit);

            $arr = $data->toArray();
            $this->pagination = Arr::except($arr, 'data');
            DB::commit();

            return $this->output($data, 200);
        } catch (\Exception $ex ) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }
}
