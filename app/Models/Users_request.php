<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $id_user
 * @property int|null $id_perusahaan
 * @property int|null $status 1=pending 2=approve, 99=tidakvalid
 * @property string|null $jenis
 * @property string|null $verify_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $logs
 * @property-read mixed $request_user_hash
 * @property-read \App\Models\Perusahaan|null $perusahaan
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Users_request newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Users_request newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Users_request query()
 * @method static \Illuminate\Database\Eloquent\Builder|Users_request whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Users_request whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Users_request whereIdPerusahaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Users_request whereIdUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Users_request whereJenis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Users_request whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Users_request whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Users_request whereVerifyAt($value)
 * @mixin \Eloquent
 */
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
        return $this->belongsTo(User::class, 'id_user', 'id')->withTrashed();
    }

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class, 'id_perusahaan', 'id_perusahaan')->withTrashed();
    }
}
