<?php

namespace App\Observers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\Log_activity;

class GlobalObserver
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public function created(Model $model)
    {
        $this->catatLog($model, 'CREATE', 'Data baru telah ditambahkan');
    }

    public function updated(Model $model)
    {
        // Cek apa yang berubah
        $changes = $model->getChanges();
        $original = $model->getOriginal();

        $detail = json_encode([
            'perubahan' => $changes,
            'sebelumnya' => array_intersect_key($original, $changes)
        ]);

        $this->catatLog($model, 'UPDATE', "Mengubah data", $detail);
    }

    public function deleted(Model $model)
    {
        $this->catatLog($model, 'DELETE', 'Data telah dihapus');
    }

    private function catatLog($model, $aksi, $pesan, $detail = null)
    {
        // mengambil value primary key di model
        Log_activity::create([
            'log_name'  => $aksi,
            'log_type'  => 'crud',
            'description' => $pesan,
            'subject_type' => get_class($model),
            'subject_id' => $model->getKey(),
            'causer_type' => get_class(Auth::user()),
            'causer_id' => Auth::id(),
            'ip_address' => $this->request->getClientIp(),
            'user_agent' => $this->request->header('User-Agent'),
            'properties' => $detail
        ]);
    }
}
