<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Perusahaan;
use Illuminate\Http\Request;
use App\Models\Users_request;
use App\Models\User;

use App\Services\Notifier;

use DB;

class ApprovalPelangganAPI extends Controller
{
    public function list(Request $request)
    {
        $limit = $request->limit ?? 10;
        // Filter hanya yang statusnya "pending" atau membutuhkan approval
        $query = Users_request::with([
                'perusahaan',
                'perusahaan.alamat',
                'user',
                'user.profile.suratkuasa',
                'perusahaan'
            ])
            ->where('status', 1);

        $data = $query->paginate($limit);

        return response()->json([
            'meta' => ['code' => 200, 'message' => 'Success'],
            'data' => $data->items(),
            'pagination' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'total' => $data->total(),
            ]
        ]);
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
            DB::commit();

            return response()->json(['meta' => ['code' => 200, 'message' => 'Berhasil verifikasi']]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['meta' => ['code' => 500, 'message' => $e->getMessage()]], 500);
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
                return response()->json(['meta' => ['code' => 404, 'message' => 'Data tidak ditemukan']], 404);
            }

            $userRequest->status = 90; // Mengubah status menjadi ditolak (misal: 3)
            $userRequest->save();

            $dataNotif = array(
                'pesan' => 'Pengajuan pendaftaran pelanggan Anda ditolak. Alasan: ' . $request->catatan,
                'event_id' => (int) $id_request,
                'event' => 'approval_pelanggan'
            );
            // dd($userRequest->id_user, $dataNotif);
            Notifier::send([$userRequest->id_user], $dataNotif);
            DB::commit();

            return response()->json(['meta' => ['code' => 200, 'message' => 'Berhasil menolak pelanggan']]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['meta' => ['code' => 500, 'message' => $e->getMessage()]], 500);
        }
    }
}
