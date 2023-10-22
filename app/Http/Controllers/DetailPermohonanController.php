<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Detail_permohonan;

class DetailPermohonanController extends Controller
{
    public function reset($idPermohonan)
    {
        Detail_permohonan::where('permohonan_id', $idPermohonan)->update(['status' => '99']);
    }
}
