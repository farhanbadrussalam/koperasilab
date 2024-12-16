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
                    $note = 'Penyelia - Permohonan tanda tangan manager ';
                    break;
                case 3:
                    $note = 'Penyelia - Selesai';
                    break;
                case 11:
                    $note = 'Penyelia - Proses Pendataan TLD '.($text != '' ? "($text)" : "");
                    break;
                case 12:
                    $note = 'Penyelia - Proses Pembacaan TLD '.($text != '' ? "($text)" : "");
                    break;
                case 13:
                    $note = 'Penyelia - Proses Penyimpanan TLD '.($text != '' ? "($text)" : "");
                    break;
                case 14:
                    $note = 'Penyelia - Proses Anealing '.($text != '' ? "($text)" : "");
                    break;
                case 15:
                    $note = 'Penyelia - Proses Labeling '.($text != '' ? "($text)" : "");
                    break;
                case 16:
                    $note = 'Penyelia - Proses Penyeliaan LHU '.($text != '' ? "($text)" : "");
                    break;
                case 17:
                    $note = 'Penyelia - Proses Pendatanganan LHU '.($text != '' ? "($text)" : "");
                    break;
                case 18:
                    $note = 'Penyelia - Proses Penerbitan LHU '.($text != '' ? "($text)" : "");
                    break;
                
                default:
                    $note = $text;
                    break;
            }
        }

        return $note;
    }
}
