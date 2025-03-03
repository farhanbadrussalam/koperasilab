<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Log_permohonan;
use App\Models\Log_keuangan;
use App\Models\Log_penyelia;

use App\Models\Master_jobs;

class LogController extends Controller
{
    public function addLog($mode, $params = array()){
        $query = false;
        switch ($mode) {
            case 'permohonan':
                $query = Log_permohonan::create($params);
                break;
            case 'keuangan':
                $query = Log_keuangan::create($params);
                break;
            case 'penyelia':
                $query = Log_penyelia::create($params);
                break;
            default:
                # code...
                break;
        }

        return $query;
    }

    public function getLog($mode, $where = array()){
        $query = false;
        switch ($mode) {
            case 'penyelia':
                $query = Log_penyelia::where($where)->first();
                break;
        }

        return $query;
    }

    public function noteLog($mode, $status, $jenis = '', $text = '')
    {
        $note = '';
        if($mode == 'keuangan'){
            switch ($status) {
                case 1:
                    $note = 'Keuangan - Pengajuan berhasil dibuat';
                    break;
                case 2:
                    $note = 'Keuangan - Invoice berhasil dibuat';
                    break;
                case 3:
                    $note = 'Keuangan - Invoice ditandatangani oleh general manager';
                    break;
                case 4:
                    $note = 'Pelanggan - Invoice dibayar oleh pelanggan';
                    break;
                case 5:
                    $note = 'Keuangan - Invoice diterima';
                    break;
                case 90:
                    $note = 'Keuangan - Invoice ditolak '.($text != '' ? "($text)" : "");
                    break;
                case 91:
                    $note = 'Keuangan - Invoice ditolak '.($text != '' ? "($text)" : "");
                    break;
            }
        } else if ($mode == 'permohonan') {
            switch ($status) {
                case 1:
                    $note = 'Permohonan - Pengajuan berhasil dibuat';
                    break;
                case 2:
                    $note = 'Permohonan - Pengajuan berhasil diverifikasi';
                    break;
                case 90:
                    $note = 'Permohonan - Pengajuan ditolak '.($text != '' ? "($text)" : "");
                    break;
                
                default:
                    # code...
                    break;
            }
        } else if ($mode == 'penyelia'){
            switch ($status) {
                case 1:
                    $note = 'Pengajuan dibuat';
                    break;
                case 2:
                    if($jenis == 'updated'){
                        $note = 'Surat tugas di perbaharui';
                    }else{
                        $note = 'Surat tugas di buat';
                    }
                    break;
                case 3:
                    $note = 'Proses Selesai';
                    break;
                
                default:
                    $jobs = Master_jobs::where('status',$status)->first();
                    if($jobs){
                        $note = "Proses ".$jobs->name;
                    }
                    break;
            }
        }

        return $note;
    }
}
