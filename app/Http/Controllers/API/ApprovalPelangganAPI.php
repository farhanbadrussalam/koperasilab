<?php

namespace App\Http\Controllers\API;

use App\Traits\RestApi;
use App\Http\Controllers\Controller;
use App\Http\Controllers\LogController;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Models\Users_request;
use App\Models\Perusahaan;
use App\Models\User;

use App\Services\Notifier;

use DB;

class ApprovalPelangganAPI extends Controller
{
    use RestApi;
    protected $log, $pagination;

    public function __construct()
    {
        $this->log = resolve(LogController::class);
    }

    public function list(Request $request)
    {
        $limit = $request->limit ?? 10;
        $page = $request->page ?? 1;
        // Filter hanya yang statusnya "pending" atau membutuhkan approval
        $query = Users_request::with([
            'perusahaan',
            'perusahaan.alamat',
            'user',
            'user.profile.suratkuasa',
            'perusahaan'
        ])
        ->where('status', 1)
        ->offset(($page - 1) * $limit)
        ->limit($limit)
        ->paginate($limit);

        $arr = $query->toArray();
        $this->pagination = Arr::except($arr, 'data');

        return $this->output($query, 200);
    }

    public function verifikasi(Request $request)
    {
        $request->validate([
            'id_request' => 'required',
        ]);

        $id_request = decryptor($request->id_request);

        DB::beginTransaction();

        try {

            $userRequest = Users_request::find($id_request);

            $userRequest->status = 2;
            $userRequest->verify_at = date('Y-m-d H:i:s');
            $userRequest->save();

            $user = User::find($userRequest->id_user);
            $user->id_perusahaan = $userRequest->id_perusahaan;
            $user->save();

            if ($request->has('kode_perusahaan')) {
                $perusahaan = Perusahaan::find($userRequest->id_perusahaan);
                $perusahaan->kode_perusahaan = $request->kode_perusahaan;
                $perusahaan->save();
            }

            // Kirim notifikasi ke user
            $dataNotif = array(
                'pesan' => 'Pengajuan pendaftaran pelanggan Anda disetujui',
                'event_id' => (int) $id_request,
                'event' => 'approval_pelanggan'
            );
            Notifier::send([$userRequest->id_user], $dataNotif);

            // Kirim Log
            $this->log->addLog('APPROVAL_PELANGGAN', 'users_request', $userRequest, array(
                'description' => 'Pengajuan pendaftaran pelanggan disetujui',
                'properties' => array(
                    'id_user' => $userRequest->id_user
                )
            ));

            DB::commit();

            return $this->output(array('msg' => 'Pengajuan pendaftaran pelanggan disetujui'));
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), "Fail", 500);
        }
    }

    public function tolak(Request $request)
    {
        $request->validate([
            'id_request' => 'required',
            'catatan' => 'required'
        ]);

        DB::beginTransaction();

        try {
            $id_request = decryptor($request->id_request);
            $userRequest = Users_request::find($id_request);

            if (!$userRequest) {
                return $this->errorRequest(404, 'Data tidak ditemukan');
            }

            $userRequest->status = 90; // Mengubah status menjadi ditolak (misal: 3)
            $userRequest->save();

            // Kirim notifikasi ke user
            $dataNotif = array(
                'pesan' => 'Pengajuan pendaftaran pelanggan Anda ditolak. Alasan: ' . $request->catatan,
                'event_id' => (int) $id_request,
                'event' => 'approval_pelanggan'
            );
            Notifier::send([$userRequest->id_user], $dataNotif);

            // Kirim log
            $this->log->addLog('APPROVAL_PELANGGAN', 'users_request', $userRequest, array(
                'description' => 'Pengajuan pendaftaran pelanggan ditolak',
                'properties' => array(
                    'catatan' => $request->catatan,
                    'id_perusahaan' => $userRequest->id_perusahaan
                )
            ));
            DB::commit();

            return $this->output(array('msg' => 'Pengajuan pendaftaran pelanggan ditolak'));
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), "Fail", 500);
        }
    }
}
