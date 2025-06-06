<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_perusahaan',
        'name',
        'jobs',
        'jabatan',
        'telepon',
        'avatar',
        'nik',
        'jenis_kelamin',
        'status',
        'email',
        'password',
        'google_id',
        'email_verified_at',
        'ttd'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'id_perusahaan',
        'password',
        'remember_token',
        'email_verified_at',
        'google_id',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at'
    ];

    protected $appends = [
        'user_hash',
        'satuankerja_hash'
    ];

    public function getUserHashAttribute()
    {
        return encryptor($this->id);
    }

    public function getSatuankerjaHashAttribute()
    {
        return encryptor($this->satuankerja_id);
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'jobs' => 'json',
        'status' => 'integer'
    ];

    public function perusahaan(){
        return $this->hasOne(Perusahaan::class, 'id_perusahaan', 'id_perusahaan');
    }


    public function satuankerja(){
        return $this->hasOne(Satuan_kerja::class, 'id', 'satuankerja_id');
    }
    public function profile(){
        return $this->hasOne(profile::class, 'user_id', 'id');
    }
}
