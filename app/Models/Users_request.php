<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Users_request extends Model
{
    use HasFactory;

    protected $table = 'users_request';
    protected $primaryKey = 'id';

    protected $guarded = [
        'id'
    ];

    protected $appends = [
        'request_user_hash',
        'logs'
    ];

    public function getRequestUserHashAttribute()
    {
        return $this->id ? encryptor($this->id) : null;
    }

    public function getLogsAttribute()
    {
        $request = Log_proses::where('log_name', 'APPROVAL_PELANGGAN')
            ->where('subject_id', $this->id)
            ->orderBy('created_at', 'desc')
            ->get();
        return $request;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class, 'id_perusahaan', 'id_perusahaan');
    }
}
