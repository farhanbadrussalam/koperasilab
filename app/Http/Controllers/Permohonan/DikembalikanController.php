<?php

namespace App\Http\Controllers\Permohonan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DikembalikanController extends Controller
{
    /**
     * Display a listing of the resource.
     */

     public function index()
     {
         $data = [
             'title' => 'Dikembalikan',
             'module' => 'permohonan-dikembalikan'
         ];
         return view('pages.permohonan.dikembalikan.index', $data);
     }
}
