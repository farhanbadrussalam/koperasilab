<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Traits\RestApi;

use App\Models\Kontrak;

use App\Http\Controllers\MediaController;
use App\Http\Controllers\LogController;
use App\Models\Kontrak_detail;
use App\Models\Kontrak_periode;
use App\Models\Master_pengguna;
use App\Models\Pengiriman;
use App\Models\Permohonan;
use App\Models\Permohonan_dokumen;
use Auth;
use DB;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class KontrakAPI extends Controller
{
    use RestApi;
    protected $media, $log, $pagination;

    public function __construct()
    {
        $this->media = resolve(MediaController::class);
        $this->log = resolve(LogController::class);
    }

    public function listKontrak(Request $request)
    {
        $limit = $request->limit ?? 10;
        $page = $request->page ?? 1;
        $filter = $request->filter ?? [];

        // cek role
        $idPelanggan = false;
        if (Auth::user()->hasRole('Pelanggan')) {
            $idPelanggan = Auth::user()->id;
        }

        DB::beginTransaction();
        try {
            $query = Kontrak::with([
                'pengguna',
                'periode' => function ($q) use ($filter) {
                    if (isset($filter['date_range']))
                        $q->whereBetween('start_date', [$filter['date_range'][0], $filter['date_range'][1]])->whereNull('id_permohonan');

                    $q->whereIn('status', [1, 2]);
                },
                'periode.permohonan',
                'periode.permohonan.jenis_layanan',
                'periode.permohonan.jenis_layanan_parent',
                'periode.permohonan.file_lhu',
                'periode.permohonan.invoice',
                'periode.permohonan.lhu',
                'periode.permohonan.lhu.penyelia_map',
                'periode.permohonan.lhu.penyelia_map.jobs',
                'periode.penyelia',
                'periode.penyelia.penyelia_map',
                'periode.penyelia.penyelia_map.jobs',
                'layanan_jasa:id_layanan,nama_layanan',
                'jenisTld:id_jenisTld,name',
                'jenis_layanan:id_jenisLayanan,name,parent',
                'jenis_layanan_parent',
                'pelanggan:id,id_perusahaan,name',
                'pelanggan.perusahaan',
                'pengiriman:id_pengiriman,id_kontrak,no_resi,status,id_permohonan',
                'pengiriman.detail',
                'pengiriman.permohonan:id_permohonan,periode,tipe_kontrak',
                'tld_aktif:id_tld,digunakan,no_seri_tld,status',
                'kontrak_detail',
                'kontrak_detail.tld_1',
                'kontrak_detail.tld_2',
                'kontrak_detail.entitas' => function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        Master_pengguna::class => ['media_ktp:id,file_hash,file_path', 'divisi']
                    ]);
                },
                'rincian_list_tld' => function ($q) {
                    $q->whereIn('status', [5, 6]);
                }
            ])
                ->withCount('periode')
                ->when($idPelanggan, function ($q, $idPelanggan) {
                    // mengambil id dari history_pic
                    $id_pic = array();
                    foreach (Auth::user()->perusahaan->history_pic as $key => $pic) {
                        array_push($id_pic, $pic->id);
                    }
                    return $q->where('id_pelanggan', $id_pic);
                })
                ->when($filter, function ($q, $filter) {
                    foreach ($filter as $key => $value) {
                        if ($key == 'date_range') {
                            $q->whereHas('periode', function ($p) use ($value) {
                                $p->whereBetween('start_date', [$value[0], $value[1]])->whereNull('id_permohonan');
                            });
                        } else if ($key == 'periode') {
                        } else {
                            $q->where($key, decryptor($value));
                        }
                    }
                });

            if (Auth::user()->status == 2) {
                $query = $query->where('status', '2');
            }

            $query = $query->orderBy('status', 'asc')
                ->orderBy('created_at', 'desc')
                ->offset(($page - 1) * $limit)
                ->limit($limit)
                ->paginate($limit);

            // Filter range periode start_date - end_date
            $arr = $query->toArray();

            $this->pagination = Arr::except($arr, 'data');

            // mencari adendum di setiap periode
            $kontrakIds = $query->pluck('id_kontrak');
            $adendums = Permohonan::whereIn('id_kontrak', $kontrakIds)
                ->where('tipe_kontrak', 'adendum')
                ->get()
                ->groupBy(['id_kontrak', 'periode']);

            foreach ($query->items() as $value) {
                foreach ($value->periode as $v) {
                    $v->adendum = $adendums->get($value->id_kontrak)?->get($v->periode) ?? collect();
                }
            }

            DB::commit();
            return $this->output($query, 200);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), "Fail", 500);
        }
    }

    // private function filter_by_periode($data, $filter){
    //     $dataNew = [];
    //     foreach ($data as $key => $value) {
    //         $arrFilter = array_filter($value['periode'], function($p) use ($filter) {
    //             return $p['permohonan'] == null;
    //         });
    //         $value['periode'] = array_values($arrFilter);
    //         array_push($dataNew, $value);
    //     }

    //     return $dataNew;
    // }

    public function actionKontrak(Request $request)
    {
        $action = $request->action;
        $data = $request->data;
        $id = $request->id;

        DB::beginTransaction();
        try {
            if ($action == "add") {
                $dataKontrak = Kontrak::create($data);
                $id = $dataKontrak->id_kontrak;
            } else if ($action == "edit") {
                Kontrak::where('id_kontrak', $id)->update($data);
            } else if ($action == "delete") {
                Kontrak::where('id_kontrak', $id)->get()->each->delete();
            }

            DB::commit();
            return $this->output(array('id' => $id), 200);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), "Fail", 500);
        }
    }

    public function getKontrakById(string $id)
    {
        $id = decryptor($id);

        DB::beginTransaction();
        try {
            $query = Kontrak::with([
                'periode' => function ($q) {
                    $q->whereIn('status', [1, 2]);
                },
                'periode.permohonan',
                'periode.permohonan.jenis_layanan',
                'periode.permohonan.jenis_layanan_parent',
                'periode.permohonan.file_lhu',
                'periode.permohonan.invoice',
                'periode.permohonan.lhu',
                'periode.permohonan.lhu.penyelia_map',
                'periode.permohonan.lhu.penyelia_map.jobs',
                'periode.penyelia',
                'periode.penyelia.penyelia_map',
                'periode.penyelia.penyelia_map.jobs',
                'invoice',
                'layanan_jasa:id_layanan,nama_layanan',
                'jenisTld:id_jenisTld,name',
                'jenis_layanan:id_jenisLayanan,name,parent',
                'jenis_layanan_parent',
                'pelanggan:id,id_perusahaan,name',
                'pelanggan.perusahaan',
                'pengiriman:id_pengiriman,id_kontrak,no_resi,status,id_permohonan',
                'pengiriman.detail',
                'pengiriman.permohonan:id_permohonan,periode,tipe_kontrak,id_kontrak',
                'tld_aktif:id_tld,digunakan,no_seri_tld,status',
                'kontrak_detail',
                'kontrak_detail.tld_1',
                'kontrak_detail.tld_2',
                'kontrak_detail.entitas' => function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        Master_pengguna::class => ['media_ktp:id,file_hash,file_path', 'divisi']
                    ]);
                },
            ])
                ->withCount('periode')
                ->where('id_kontrak', $id)
                ->first();

            $adendums = Permohonan::with(['permohonan_detail', 'invoice', 'lhu'])
                ->where('id_kontrak', $id)
                ->where('tipe_kontrak', 'adendum')
                ->where('status', '!=', 1)
                ->get()
                ->groupBy('periode');

            foreach ($query->periode as $v) {
                $v->adendum = $adendums->get($v->periode) ?? collect();
            }
            DB::commit();

            return $this->output($query, 200);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), "Fail", 500);
        }
    }

    public function searchKontrak(Request $request)
    {
        DB::beginTransaction();
        try {
            $no_kontrak = $request->has('no_kontrak') ? $request->no_kontrak : false;
            $data = array();

            if (!empty($no_kontrak)) {
                $idPelanggan = Auth::user()->hasRole('Pelanggan') ? Auth::user()->id : false;
                $data = Kontrak::when($idPelanggan, fn($q) => $q->where('id_pelanggan', $idPelanggan))
                    ->where('no_kontrak', 'like', '%' . $no_kontrak . '%')
                    ->get();
            }

            DB::commit();
            return $this->output($data, 200);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), "Fail", 500);
        }
    }

    public function getKontrakTld(Request $request)
    {
        $idKontrak = $request->has('id_kontrak') ? decryptor($request->id_kontrak) : false;

        DB::beginTransaction();
        try {
            $data = Kontrak_detail::with([
                'entitas' => function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        Master_pengguna::class => ['media_ktp:id,file_hash,file_path', 'divisi']
                    ]);
                },
                'tld_1',
                'tld_2'
            ])->where('id_kontrak', $idKontrak)->where('status', 1)->get();

            DB::commit();
            return $this->output($data, 200);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), "Fail", 500);
        }
    }

    public function signKontrak(Request $request)
    {
        $id_kontrak = decryptor($request->id_kontrak);
        $ttdValue = decryptor($request->ttd);
        $ttdBy = decryptor($request->ttd_by);

        if (empty($ttdValue) || empty($ttdBy)) {
            return $this->output(array('msg' => 'Tanda tangan dan nama penandatangan harus diisi'), "Fail", 400);
        }

        // cek apakah kontrak sudah memiliki ttd
        $kontrak_dokumen = Permohonan_dokumen::where('id_kontrak', $id_kontrak)
            ->whereIn('jenis', ['kontrak', 'KontrakPengujian'])
            ->first();

        if (!$kontrak_dokumen) {
            return $this->output(array('msg' => 'Dokumen kontrak tidak ditemukan'), "Fail", 404);
        }

        if ($kontrak_dokumen->ttd != null) {
            return $this->output(array('msg' => 'Kontrak sudah memiliki tanda tangan'), "Fail", 400);
        }

        DB::beginTransaction();
        try {
            $kontrak_dokumen->update(['ttd' => $ttdValue, 'ttd_by' => $ttdBy]);
            DB::commit();
            return $this->output(array('msg' => 'Tanda tangan berhasil disimpan'), "Success", 200);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), "Fail", 500);
        }
    }

    public function getKontrakPeriode($idPeriode)
    {
        $idPeriode = decryptor($idPeriode);

        DB::beginTransaction();
        try {
            $data = Kontrak_periode::with([
                'permohonan',
                'permohonan.jenis_layanan',
                'permohonan.jenis_layanan_parent',
                'permohonan.file_lhu',
                'penyelia',
                'penyelia.penyelia_map',
                'penyelia.penyelia_map.jobs',
            ])->where('id_periode', $idPeriode)->first();

            // mencari adendum di setiap periode
            $adendums = Permohonan::with('permohonan_detail')->where('id_kontrak', $data->id_kontrak)
                ->where('tipe_kontrak', 'adendum')
                ->get()
                ->groupBy(['id_kontrak', 'periode']);

            // mencari pengiriman di setiap periode
            $pengiriman = Pengiriman::with('detail')->where('id_kontrak', $data->id_kontrak)
                ->where('periode', $data->periode)
                ->get();

            $data->adendum = $adendums->get($data->id_kontrak)?->get($data->periode) ?? collect();
            $data->pengiriman = $pengiriman;

            DB::commit();
            return $this->output($data, 200);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), "Fail", 500);
        }
    }

    public function destroyByNoKontrak(Request $request)
    {
        if (env('APP_ENV') === 'production' && (!Auth::check() || !Auth::user()->hasRole('Super Admin'))) {
            return $this->errorRequest(403, 'Unauthorized. API ini hanya boleh digunakan di lingkungan development atau oleh Super Admin.');
        }

        $no_kontrak = $request->no_kontrak ?? $request->query('no_kontrak');
        if (empty($no_kontrak)) {
            return $this->errorRequest(400, 'Parameter no_kontrak wajib diisi.');
        }

        $kontrak = Kontrak::where('no_kontrak', $no_kontrak)->first();
        if (!$kontrak) {
            return $this->errorRequest(404, "Kontrak dengan no_kontrak '{$no_kontrak}' tidak ditemukan.");
        }
        $id_kontrak = $kontrak->id_kontrak;

        $permohonanIds = \App\Models\Permohonan::where('id_kontrak', $id_kontrak)->pluck('id_permohonan')->toArray();
        $penyeliaIds = \App\Models\Penyelia::where('id_kontrak', $id_kontrak)
            ->orWhereIn('id_permohonan', $permohonanIds)
            ->pluck('id_penyelia')
            ->toArray();
        $penyeliaMapIds = \App\Models\Penyelia_map::whereIn('id_penyelia', $penyeliaIds)->pluck('id_map')->toArray();
        $pengirimanIds = \App\Models\Pengiriman::where('id_kontrak', $id_kontrak)
            ->orWhereIn('id_permohonan', $permohonanIds)
            ->pluck('id_pengiriman')
            ->toArray();
        $keuanganIds = \App\Models\Keuangan::whereIn('id_permohonan', $permohonanIds)->pluck('id_keuangan')->toArray();

        $penggunaIdsFromTld = \App\Models\Kontrak_tld::where('id_kontrak', $id_kontrak)->pluck('id_pengguna')->toArray();
        $penggunaIdsFromMap = \App\Models\Kontrak_pengguna::where('id_kontrak', $id_kontrak)->pluck('id_pengguna_divisi')->toArray();
        $allPenggunaIds = array_unique(array_filter(array_merge($penggunaIdsFromTld, $penggunaIdsFromMap)));

        $mediaIds = [];
        if ($kontrak->file_lhu) {
            $mediaIds[] = $kontrak->file_lhu;
        }

        $permohonanMedias = \App\Models\Permohonan::where('id_kontrak', $id_kontrak)->pluck('file_lhu')->filter()->toArray();
        $mediaIds = array_merge($mediaIds, $permohonanMedias);

        $penyeliaDocs = \App\Models\Penyelia::where('id_kontrak', $id_kontrak)
            ->orWhereIn('id_permohonan', $permohonanIds)
            ->pluck('document')
            ->filter()
            ->toArray();
        foreach ($penyeliaDocs as $doc) {
            $decoded = is_array($doc) ? $doc : json_decode($doc, true);
            if (is_array($decoded)) {
                $mediaIds = array_merge($mediaIds, $decoded);
            }
        }

        $keuanganRecords = \App\Models\Keuangan::whereIn('id_permohonan', $permohonanIds)->get();
        foreach ($keuanganRecords as $keu) {
            if (is_array($keu->document_faktur)) {
                $mediaIds = array_merge($mediaIds, $keu->document_faktur);
            }
            if (is_array($keu->bukti_bayar)) {
                $mediaIds = array_merge($mediaIds, $keu->bukti_bayar);
            }
            if (is_array($keu->bukti_bayar_pph)) {
                $mediaIds = array_merge($mediaIds, $keu->bukti_bayar_pph);
            }
        }

        $pengirimanRecords = \App\Models\Pengiriman::where('id_kontrak', $id_kontrak)
            ->orWhereIn('id_permohonan', $permohonanIds)
            ->get();
        foreach ($pengirimanRecords as $peng) {
            if (is_array($peng->bukti_pengiriman)) {
                $mediaIds = array_merge($mediaIds, $peng->bukti_pengiriman);
            }
            if (is_array($peng->bukti_penerima)) {
                $mediaIds = array_merge($mediaIds, $peng->bukti_penerima);
            }
        }

        $mediaIds = array_unique(array_filter($mediaIds));

        DB::beginTransaction();
        try {
            \App\Models\Master_tld::withTrashed()->where('digunakan', $no_kontrak)->update([
                'status' => 0,
                'digunakan' => null
            ]);

            if (!empty($allPenggunaIds)) {
                \App\Models\Master_pengguna::withTrashed()->whereIn('id_pengguna', $allPenggunaIds)->update([
                    'status' => 1
                ]);
            }

            if (!empty($mediaIds)) {
                $medias = \App\Models\Master_media::whereIn('id', $mediaIds)->get();
                foreach ($medias as $media) {
                    if ($media->file_path === 'dokumen/pengguna' || $media->file_path === 'images/avatar') {
                        continue;
                    }
                    $path = 'public/' . $media->file_path . '/' . $media->file_hash;
                    if (\Storage::exists($path)) {
                        \Storage::delete($path);
                    }
                    $media->delete();
                }
            }

            \App\Models\Log_proses::where(function ($q) use ($id_kontrak, $permohonanIds, $keuanganIds, $pengirimanIds, $penyeliaIds) {
                $q->where(function ($sub) use ($id_kontrak) {
                    $sub->where('subject_type', 'App\Models\Kontrak')->where('subject_id', $id_kontrak);
                })->orWhere(function ($sub) use ($permohonanIds) {
                    $sub->where('subject_type', 'App\Models\Permohonan')->whereIn('subject_id', $permohonanIds);
                })->orWhere(function ($sub) use ($keuanganIds) {
                    $sub->where('subject_type', 'App\Models\Keuangan')->whereIn('subject_id', $keuanganIds);
                })->orWhere(function ($sub) use ($pengirimanIds) {
                    $sub->where('subject_type', 'App\Models\Pengiriman')->whereIn('subject_id', $pengirimanIds);
                })->orWhere(function ($sub) use ($penyeliaIds) {
                    $sub->where('subject_type', 'App\Models\Penyelia')->whereIn('subject_id', $penyeliaIds);
                });
            })->delete();

            \App\Models\Log_activity::where(function ($q) use ($id_kontrak, $permohonanIds, $keuanganIds, $pengirimanIds, $penyeliaIds) {
                $q->where(function ($sub) use ($id_kontrak) {
                    $sub->where('subject_type', 'App\Models\Kontrak')->where('subject_id', $id_kontrak);
                })->orWhere(function ($sub) use ($permohonanIds) {
                    $sub->where('subject_type', 'App\Models\Permohonan')->whereIn('subject_id', $permohonanIds);
                })->orWhere(function ($sub) use ($keuanganIds) {
                    $sub->where('subject_type', 'App\Models\Keuangan')->whereIn('subject_id', $keuanganIds);
                })->orWhere(function ($sub) use ($pengirimanIds) {
                    $sub->where('subject_type', 'App\Models\Pengiriman')->whereIn('subject_id', $pengirimanIds);
                })->orWhere(function ($sub) use ($penyeliaIds) {
                    $sub->where('subject_type', 'App\Models\Penyelia')->whereIn('subject_id', $penyeliaIds);
                });
            })->delete();

            if (!empty($keuanganIds)) {
                \App\Models\Log_keuangan::whereIn('id_keuangan', $keuanganIds)->delete();
            }
            if (!empty($pengirimanIds)) {
                \App\Models\Log_pengiriman::whereIn('id_pengiriman', $pengirimanIds)->delete();
            }
            if (!empty($penyeliaIds)) {
                \App\Models\Log_penyelia::whereIn('id_penyelia', $penyeliaIds)->delete();
            }
            if (!empty($permohonanIds)) {
                \App\Models\Log_permohonan::whereIn('id_permohonan', $permohonanIds)->delete();
            }

            if (!empty($penyeliaIds) || !empty($penyeliaMapIds)) {
                \App\Models\Penyelia_petugas::whereIn('id_penyelia', $penyeliaIds)
                    ->orWhereIn('id_map', $penyeliaMapIds)
                    ->delete();
                \App\Models\Penyelia_map::whereIn('id_penyelia', $penyeliaIds)->delete();
            }
            if (!empty($penyeliaIds)) {
                \App\Models\Penyelia::whereIn('id_penyelia', $penyeliaIds)->delete();
            }

            if (!empty($keuanganIds)) {
                \App\Models\Keuangan_diskon::whereIn('id_keuangan', $keuanganIds)->delete();
                \App\Models\Keuangan::whereIn('id_keuangan', $keuanganIds)->delete();
            }

            if (!empty($pengirimanIds)) {
                \App\Models\Pengiriman_detail::whereIn('id_pengiriman', $pengirimanIds)->delete();
                \App\Models\Pengiriman::whereIn('id_pengiriman', $pengirimanIds)->delete();
            }

            if (!empty($permohonanIds)) {
                \App\Models\Permohonan_detail::whereIn('id_permohonan', $permohonanIds)->delete();
                \App\Models\Permohonan_dokumen::whereIn('id_permohonan', $permohonanIds)
                    ->orWhere('id_kontrak', $id_kontrak)
                    ->delete();
                \App\Models\Permohonan_pengguna::whereIn('id_permohonan', $permohonanIds)->delete();
                \App\Models\Permohonan_tandaterima::whereIn('id_permohonan', $permohonanIds)->delete();
                \App\Models\Permohonan_tld::whereIn('id_permohonan', $permohonanIds)->delete();
                \App\Models\Permohonan::whereIn('id_permohonan', $permohonanIds)->delete();
            }

            \App\Models\Kontrak_detail::where('id_kontrak', $id_kontrak)->delete();
            \App\Models\Kontrak_map::where('id_kontrak', $id_kontrak)->delete();
            \App\Models\Kontrak_pengguna::where('id_kontrak', $id_kontrak)->delete();
            \App\Models\Kontrak_periode::where('id_kontrak', $id_kontrak)->delete();
            \App\Models\Kontrak_tld::where('id_kontrak', $id_kontrak)->delete();

            $kontrak->delete();

            DB::commit();
            return $this->output(['msg' => 'Kontrak dan data terkait berhasil dihapus.'], 200);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(['msg' => 'Terjadi kesalahan: ' . $ex->getMessage()], 'Fail', 500);
        }
    }
}
