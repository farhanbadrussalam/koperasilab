<?php

use Illuminate\Support\Facades\Session;

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

?>
