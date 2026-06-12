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
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function searchTld(Request $request)
    {
        DB::beginTransaction();
        try {
            $jenis = $request->has('jenis') ? $request->jenis : false;
            $kode_lencana = $request->has('kode_lencana') ? $request->kode_lencana : false;
            $limit = 10;
            $data = array();

            if (!empty($kode_lencana)) {
                $data = Master_tld::where('jenis', $jenis)
                    ->where('kode_lencana', 'like', '%' . $kode_lencana . '%')
                    ->limit($limit)
                    ->orderBy('status', 'desc')->get();
            } else {
                $data = Master_tld::limit($limit)->where('jenis', $jenis)->orderBy('status', 'desc')->get();
            }

            DB::commit();
            return $this->output($data, 200);
        } catch (\Exception $ex) {
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

            // if role nya pelanggan
            if (Auth::user()->hasRole('Pelanggan')) {
                $idPerusahaan = Auth::user()->id_perusahaan;
                $data = Master_tld::where('status', 0)->where('kepemilikan', $idPerusahaan)->whereNull('digunakan')->where('jenis', $jenis)->get();
            } else {
                $data = Master_tld::where('status', 0)->whereNull('kepemilikan')->where('jenis', $jenis)->get();
            }


            DB::commit();
            return $this->output($data, 200);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function getById($id)
    {
        DB::beginTransaction();
        try {
            // $id = decryptor($id);
            $data = Master_tld::find($id);
            DB::commit();
            return $this->output($data, 200);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function getData(Request $request)
    {
        DB::beginTransaction();
        try {
            $jenis = $request->has('jenis') ? $request->jenis : false;
            $status = $request->has('status') ? $request->status : false;
            $search = $request->has('search') ? $request->search : false;
            $no_kontrak = $request->has('no_kontrak') ? $request->no_kontrak : false;

            $page = $request->has('page') ? $request->page : 1;
            $limit = $request->has('limit') ? $request->limit : 5;

            // pengecekan role user
            $role = count(Auth::user()->getRoleNames()) > 0 ? true : false;

            // pengecekan tld yang sedang digunakan oleh kontrak
            $cekTldKontrak = false;
            // if (!Auth::user()->hasRole('Pelanggan') && $no_kontrak) {
            //     $cekTldKontrak = Master_tld::where('digunakan', $no_kontrak)->where('status', 0)->first();
            // }

            // dd($cekTldKontrak);
            $data = Master_tld::when($role, function ($query, $role) use ($no_kontrak, $cekTldKontrak) {
                if (Auth::user()->hasRole('Pelanggan')) {
                    return $query->where('kepemilikan', Auth::user()->id_perusahaan)->whereNull('digunakan');
                } else {
                    if (!$cekTldKontrak) {
                        return $query->whereNull('kepemilikan');
                    }
                }
            })
                ->when($cekTldKontrak, function ($query, $cekTldKontrak) use ($no_kontrak) {
                    return $query->where('digunakan', $no_kontrak)->where('status', 0);
                })
                ->when($jenis, function ($query, $jenis) {
                    return $query->where('jenis', $jenis);
                })
                ->when($status, function ($query, $status) {
                    return $query->where('status', $status);
                })
                ->when($search, function ($query, $search) {
                    return $query->where('no_seri_tld', 'like', '%' . $search . '%')->orWhere('merk', 'like', '%' . $search . '%');
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
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function getPenyimpanan(Request $request)
    {
        try {
            $details = \App\Models\Kontrak_detail::with([
                'kontrak.pelanggan.perusahaan',
                'tld_awal',
                'tld_second',
                'entitas'
            ])
            ->where(function($query) {
                $query->where('status_tld_1', 5)
                      ->orWhere('status_tld_2', 5);
            })
            ->where('status', 1)
            ->get();

            $storageTlds = [];

            foreach ($details as $detail) {
                if ($detail->status_tld_1 == 5 && $detail->tld_awal) {
                    $storageTlds[] = [
                        'id_kontrak' => $detail->id_kontrak,
                        'no_kontrak' => $detail->kontrak->no_kontrak ?? '-',
                        'perusahaan' => $detail->kontrak->pelanggan->perusahaan->nama_perusahaan ?? '-',
                        'tld_id' => $detail->tld_awal->id_tld,
                        'no_seri_tld' => $detail->tld_awal->no_seri_tld,
                        'jenis_tld' => $detail->tld_awal->jenis,
                        'periode' => $detail->periode_tld_1,
                        'pengguna' => $detail->entitas->name ?? '-',
                    ];
                }

                if ($detail->status_tld_2 == 5 && $detail->tld_second) {
                    $storageTlds[] = [
                        'id_kontrak' => $detail->id_kontrak,
                        'no_kontrak' => $detail->kontrak->no_kontrak ?? '-',
                        'perusahaan' => $detail->kontrak->pelanggan->perusahaan->nama_perusahaan ?? '-',
                        'tld_id' => $detail->tld_second->id_tld,
                        'no_seri_tld' => $detail->tld_second->no_seri_tld,
                        'jenis_tld' => $detail->tld_second->jenis,
                        'periode' => $detail->periode_tld_2,
                        'pengguna' => $detail->entitas->name ?? '-',
                    ];
                }
            }

            $grouped = [];
            foreach ($storageTlds as $tld) {
                $key = $tld['id_kontrak'] . '_' . $tld['periode'];
                if (!isset($grouped[$key])) {
                    $penyelia = \App\Models\Penyelia::where('id_kontrak', $tld['id_kontrak'])
                        ->where('periode', $tld['periode'])
                        ->first();

                    $grouped[$key] = [
                        'id_kontrak' => $tld['id_kontrak'],
                        'no_kontrak' => $tld['no_kontrak'],
                        'perusahaan' => $tld['perusahaan'],
                        'periode' => $tld['periode'],
                        'penyelia_hash' => $penyelia ? $penyelia->penyelia_hash : null,
                        'tlds' => []
                    ];
                }
                $grouped[$key]['tlds'][] = [
                    'no_seri_tld' => $tld['no_seri_tld'],
                    'jenis_tld' => $tld['jenis_tld'],
                    'pengguna' => $tld['pengguna']
                ];
            }

            return $this->output(array_values($grouped), 200);
        } catch (\Exception $ex) {
            info($ex);
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }
}
