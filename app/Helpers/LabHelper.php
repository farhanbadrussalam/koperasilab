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

?>
