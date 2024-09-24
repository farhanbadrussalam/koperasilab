<?php

namespace App\Http\Controllers\Permohonan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    /**
     * Display a listing of the resource.
     */

     public function index()
     {
         $data = [
             'title' => 'Pembayaran',
             'module' => 'permohonan-pembayaran',
             'type' => 'list'
         ];
         return view('pages.permohonan.pembayaran.index', $data);
     }
}
