<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Log_permohonan;
use App\Models\Log_keuangan;
use App\Models\Log_penyelia;

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

    public function noteLog($mode, $status, $text = '')
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
                    $note = 'Penyelia - Pengajuan berhasil dibuat';
                    break;
                case 2:
                    $note = 'Penyelia - Start '.($text != '' ? "($text)" : "");
                    break;
                case 3:
                    $note = 'Penyelia - Proses anealing '.($text != '' ? "($text)" : "");
                    break;
                case 4:
                    $note = 'Penyelia - Proses pembacaan '.($text != '' ? "($text)" : "");
                    break;
                case 5:
                    $note = 'Penyelia - Proses penerbitan LHU '.($text != '' ? "($text)" : "");
                    break;
                case 6:
                    $note = 'Penyelia - Selesai '.($text != '' ? "($text)" : "");
                    break;
                
                default:
                    $note = $text;
                    break;
            }
        }

        return $note;
    }
}
