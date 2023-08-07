<?php

use Illuminate\Support\Facades\Session;
use App\Events\NotifikasiEvent;
use App\Models\notifikasi;

if (!function_exists('formatCurrency')) {
    function formatCurrency($amount)
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}

if (!function_exists('generateToken')) {
    function generateToken() {
        $user = Auth::user();
        $_token = Session::get('token');
        if($_token == NULL){
            $_token = $user->createToken('api-token')->plainTextToken;

            Session::put('token', $_token);
            session()->save();
        }

        return Session::get('token');
    }
}

if (!function_exists('notifikasi')) {
    function notifikasi($data, $message) {
        if(!isset($data['to_user']) || !isset($data['type'])){
           return response()->json([
            'message' => "Object 'to_user' dan 'type' tidak boleh kosong"
           ], 400);
        }
        $recipient = $data['to_user'];
        $sender = Auth::user()->id;
        $type = $data['type'];

        $saveNotif = array(
            'recipient' => $recipient,
            'sender' => $sender,
            'message' => $message,
            'type' => $type,
            'status' => 1
        );
        $result = notifikasi::create($saveNotif);

        broadcast(new NotifikasiEvent($result, $message))->toOthers();

        return response()->json([
            'message' => "Notifikasi Terkirim"
        ], 200);
    }
}

if(!function_exists('unmask')){
    function unmask($data){
        $regMask = ['.', ',','-'];
        $unmaskedAmount = str_replace($regMask, '', $data);

        return $unmaskedAmount;
    }
}

if(!function_exists('statusFormat')){
    function statusFormat($feature, $status) {
        $htmlStatus = '';
        $status = (int)$status;
        if($feature == 'jadwal'){
            switch ($status) {
                case 0:
                    $htmlStatus = 'span class="badge text-bg-secondary">Belum ditugaskan</span>';
                    break;
                case 1:
                    $htmlStatus = '<span class="badge text-bg-info">Diajukan</span>';
                    break;
                case 2:
                    $htmlStatus = '<span class="badge text-bg-success">Bersedia</span>';
                    break;
                case 3:
                    $htmlStatus = '<span class="badge text-bg-danger">Tidak bersedia</span>';
                    break;
                default:
                    $htmlStatus = '<span class="badge text-bg-danger">dibatalkan</span>';
                    break;
            }
        }else if($feature == 'permohonan'){
            switch ($status) {
                case 1:
                    $htmlStatus = '<span class="badge text-bg-secondary">Pengajuan</span>';
                    break;
                case 2:
                    $htmlStatus = '<span class="badge text-bg-info">Terverifikasi</span>';
                    break;
                case 3:
                    $htmlStatus = '<span class="badge text-bg-success">Selesai</span>';
                    break;
                case 9:
                    $htmlStatus = '<span class="badge text-bg-danger">Di tolak</span>';
                    break;
            }
        }

        return $htmlStatus;
    }

}

?>
