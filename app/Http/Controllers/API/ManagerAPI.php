<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Traits\RestApi;

use App\Models\Keuangan;


use Auth;
use DB;

class ManagerAPI extends Controller
{
    use RestApi;

    public function listManager(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 10;
        $page = $request->has('page') ? $request->page : 1;
        $search = $request->has('search') ? $request->search : '';

        $filter = $request->has('filter') ? $request->filter : [];

        DB::beginTransaction();
        try {
            $query = Keuangan::with(
                'permohonan',
                'permohonan.layanan_jasa:id_layanan,nama_layanan',
                'permohonan.jenisTld:id_jenisTld,name', 
                'permohonan.jenis_layanan:id_jenisLayanan,name,parent',
                'permohonan.jenis_layanan_parent',
                'permohonan.pelanggan',
                'permohonan.pelanggan.perusahaan',
                'permohonan.kontrak',
                'diskon'
            )
            ->when($filter, function($q, $filter) {
                return $q->whereHas('permohonan', function($p) use ($filter, $q) {
                    foreach ($filter as $key => $value) {
                        if($key == 'status') {
                            $q->where($key, decryptor($value));
                        }else{
                            $p->where($key, decryptor($value));
                        }
                    }
                });
            })
            ->where('status', '!=', 1)
            ->orderBy('created_at','DESC')
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->paginate($limit);

            $arr = $query->toArray();
            $this->pagination = Arr::except($arr, 'data');
            DB::commit();

            return $this->output($query);
        }catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }   
    }

}
