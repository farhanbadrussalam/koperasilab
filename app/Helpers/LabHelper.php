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
                    $htmlStatus = '
                    <div class="d-flex align-items-center">
                        <div><div class="me-1 dot bg-secondary"></div></div>
                        <span class="subbody-medium text-submain text-truncate">Belum ditugaskan</span>
                    </div>
                    ';
                    break;
                case 1:
                    $htmlStatus = '
                    <div class="d-flex align-items-center">
                        <div><div class="me-1 dot bg-info"></div></div>
                        <span class="subbody-medium text-submain text-truncate">Diajukan</span>
                    </div>
                    ';
                    break;
                case 2:
                    $htmlStatus = '
                    <div class="d-flex align-items-center">
                        <div><div class="me-1 dot bg-success"></div></div>
                        <span class="subbody-medium text-submain text-truncate">Bersedia</span>
                    </div>
                    ';
                    break;
                case 3:
                    $htmlStatus = '
                    <div class="d-flex align-items-center">
                        <div><div class="me-1 dot bg-danger"></div></div>
                        <span class="subbody-medium text-submain text-truncate">Tidak bersedia</span>
                    </div>
                    ';
                    break;
                default:
                    $htmlStatus = '
                    <div class="d-flex align-items-center">
                        <div><div class="me-1 dot bg-danger"></div></div>
                        <span class="subbody-medium text-submain text-truncate">Dibatalkan</span>
                    </div>
                    ';
                    break;
            }
        }else if($feature == 'permohonan'){
            switch ($status) {
                case 1:
                    $htmlStatus = '
                    <div class="d-flex align-items-center">
                        <div><div class="me-1 dot bg-secondary"></div></div>
                        <span class="subbody-medium text-submain text-truncate">Pengajuan</span>
                    </div>
                    ';
                    break;
                case 2:
                    $htmlStatus = '
                    <div class="d-flex align-items-center">
                        <div><div class="me-1 dot bg-info"></div></div>
                        <span class="subbody-medium text-submain text-truncate">Terverifikasi</span>
                    </div>
                    ';
                    break;
                case 3:
                    $htmlStatus = '
                    <div class="d-flex align-items-center">
                        <div><div class="me-1 dot bg-success"></div></div>
                        <span class="subbody-medium text-submain text-truncate">Selesai</span>
                    </div>
                    ';
                    break;
                case 9:
                    $htmlStatus = '
                    <div class="d-flex align-items-center">
                        <div><div class="me-1 dot bg-danger"></div></div>
                        <span class="subbody-medium text-submain text-truncate">Ditolak</span>
                    </div>
                    ';
                    break;
            }
        }

        return $htmlStatus;
    }

}

if (!function_exists('formatBytes')) {
	function formatBytes($size, $precision = 2)
	{
		$base = log($size, 1024);
		$suffixes = array('B', 'KB', 'MB', 'GB', 'TB');

		return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
	}
}

if (!function_exists('iconDocument')){
    function iconDocument($type) {
        $icon = '';
        switch ($type) {
            case 'application/pdf':
                $icon = 'pdf-icon.svg';
                break;

            default:
                $icon = 'other-icon.svg';
                break;
        }
        return $icon;
    }
}

?>
