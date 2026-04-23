<?php

namespace App\Observers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\Log_activity;
use App\Models\Log_proses;

class GlobalObserver
{
    protected $request;
    protected $EXCLUDED_TABLES;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->EXCLUDED_TABLES = [
            'keuangan', 'permohonan',
            'penyelia', 'penyelia_map',
            'pengiriman'
        ];
    }
    public function created(Model $model)
    {
        // mengambil value
        $table_name = $model->getTable();

        if(in_array($table_name, $this->EXCLUDED_TABLES)){
            $note = $this->noteLog($table_name, $model->status);
            $this->catatLog($model, 'CREATE', $table_name, $note, null, 'proses');
        }

        $this->catatLog($model, 'CREATE', 'crud', 'Data baru telah ditambahkan');
    }

    public function updated(Model $model)
    {
        // Cek apa yang berubah
        $changes = $model->getChanges();
        $original = $model->getOriginal();

        $table_name = $model->getTable();

        if(in_array($table_name, $this->EXCLUDED_TABLES)) {
            if(in_array('status', array_keys($changes))) {
                $note = $this->noteLog($table_name, $changes['status'], $original['status']);

                if($table_name == 'penyelia_map') {
                    $table_name = 'penyelia';
                }

                if(in_array('note', array_keys($changes))) {
                    $detail = json_encode([
                        'note' => $changes['note']
                    ]);
                } else {
                    $detail = null;
                }

                $this->catatLog($model, 'UPDATE', $table_name, $note, $detail, 'proses');
            }
        }

        $detail = json_encode([
            'perubahan' => $changes,
            'sebelumnya' => array_intersect_key($original, $changes)
        ]);

        $this->catatLog($model, 'UPDATE', 'crud', "Mengubah data", $detail);
    }

    public function deleted(Model $model)
    {
        $this->catatLog($model, 'DELETE', 'crud', 'Data telah dihapus');
    }

    private function catatLog($model, $aksi, $type, $pesan, $detail = null, $target = 'info')
    {
        // mengambil value primary key di model
        $user = Auth::user();
        $body = [
            'log_name'  => $aksi,
            'log_type'  => $type,
            'description' => $pesan,
            'subject_type' => get_class($model),
            'subject_id' => $model->getKey(),
            'causer_type' => $user ? get_class($user) : null,
            'causer_id' => Auth::id(),
            'ip_address' => $this->request->getClientIp(),
            'user_agent' => $this->request->header('User-Agent'),
            'properties' => $detail
        ];
        if($target == 'proses') {
            Log_proses::create($body);
        } else {
            Log_activity::create($body);
        }
    }

    private function noteLog($mode, $status, $statusBefore = null)
    {
        return match ($mode) {
            'keuangan'      => $this->getKeuanganLog($status),
            'permohonan'    => $this->getPermohonanLog($status),
            'penyelia'      => $this->getPenyeliaLog($status, $statusBefore),
            'penyelia_map'  => $this->getPenyeliaMapLog($status, $statusBefore),
            'pengiriman'    => $this->getPengirimanLog($status, $statusBefore),
            'kontrak_detail' => $this->getKontrakDetailLog($status, $statusBefore),
            default         => 'Aktivitas sistem tidak terdefinisi.',
        };
    }

    private function getKontrakDetailLog($status, $statusBefore)
    {
        return match ((int) $status) {
            1 => 'Permohonan layanan baru berhasil didaftarkan.',
            2 => 'Dokumen permohonan telah diverifikasi oleh admin.',
            3 => 'Permohonan sedang dalam tahap penyeliaan.',
            4 => 'Permohonan telah selesai.',
            default => 'Status permohonan tidak dikenali.',
        };
    }

    private function getKeuanganLog($status)
    {
        return match ((int) $status) {
            1 => 'Draf pengajuan keuangan berhasil dibuat.',
            2 => 'Invoice tagihan telah diterbitkan.',
            3 => 'Invoice telah ditandatangani oleh General Manager.',
            4 => 'Pembayaran tagihan telah dikirim dari pelanggan.',
            5 => 'Invoice telah diverifikasi dan diterima.',
            6 => 'Invoice telah selesai.',
            7 => 'Terbitkan faktur pajak',
            90, 91 => "Pengajuan invoice ditolak.",
            default => 'Status keuangan tidak dikenali.',
        };
    }

    private function getPermohonanLog($status)
    {
        return match ((int) $status) {
            1 => 'Permohonan layanan baru berhasil didaftarkan.',
            2 => 'Dokumen permohonan telah diverifikasi oleh admin.',
            3 => 'Permohonan sedang dalam tahap penyeliaan.',
            4 => 'Permohonan sedang dalam tahap pengiriman.',
            5 => 'Permohonan telah selesai.',
            80 => 'Permohonan dalam draf.',
            90 => "Permohonan dikembalikan atau ditolak.",
            default => 'Status permohonan tidak dikenali.',
        };
    }

    private function getPenyeliaLog($status, $statusBefore)
    {
        if($statusBefore){
            if($statusBefore == 6 && $status == 1){
                return 'Surat Pengujian telah disetujui.';
            } else if($statusBefore == 2 && $status == 1){
                return 'Surat Tugas telah dihapus.';
            }
        }

        return match ((int) $status) {
            1 => 'Proses penyeliaan dimulai.',
            2 => 'Surat Tugas baru telah diterbitkan.',
            3 => 'Seluruh rangkaian tugas penyeliaan telah selesai.',
            5 => 'Proses Pengajuan Surat Pengujian.',
            6 => 'Surat Pengujian butuh tindak lanjut manager.',
            7 => 'Surat Pengujian ditolak.',
            10 => 'Proses LHU dimulai.',
            default => 'Status penyeliaan tidak dikenali.',
        };
    }

    private function getPenyeliaMapLog($status, $statusBefore)
    {
        if($statusBefore) {
            if($statusBefore == 1 && $status == 0) {
                return 'return';
            }
        }

        return match ((int) $status) {
            0 => 'created',
            1 => 'start',
            2 => 'finish',
            default => 'Status penyeliaan tidak dikenali.',
        };
    }

    private function getPengirimanLog($status, $statusBefore)
    {
        if($statusBefore) {
            if($statusBefore == 1 && $status == 3) {
                return 'Pengiriman batal dikirimkan.';
            }
        }

        return match ((int) $status) {
            1 => 'Pengiriman dibuat',
            2 => 'Pengiriman selesai.',
            3 => 'Masuk draft.',
            default => 'Status pengiriman tidak dikenali.',
        };
    }
}
