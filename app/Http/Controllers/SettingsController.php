<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\RestApi;

use DB;

class SettingsController extends Controller
{
    use RestApi;
    public function toggleRealtime(Request $request) {
        DB::beginTransaction();
        try {
            $user = auth()->user();
            info("User " . $user->id . " toggle realtime notifications");
            $user->realtime_notifications = $request->realtime;
            $user->save();
            DB::commit();

            return $this->output([
                'status' => 'ok',
                'realtime' => $user->realtime_notifications,
                'message' => $user->realtime_notifications ? 'Real-time notifications diaktifkan' : 'Real-time notifications dinonaktifkan'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->error($th->getMessage());
        }
    }
}
