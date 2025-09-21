<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

/**
  * @method \Illuminate\Support\Collection<int,string> getRoleNames()
  * @method bool hasRole(string|array $roles)
  * @method bool hasAnyRole(string|array $roles)
  * @method bool hasAllRoles(array $roles)
  *
  * @property-read \Illuminate\Database\Eloquent\Collection<int,\Spatie\Permission\Models\Role> $roles
  */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'satuankerja_id',
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
        'satuankerja_id',
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
        'satuankerja'
    ];

    public function getUserHashAttribute()
    {
        return $this->id ? encryptor($this->id) : null;
    }

    public function getSatuankerjaAttribute()
    {
        $decodedIds = $this->satuankerja_id;
        $decodedIds = is_array($decodedIds) ? $decodedIds : [];

        return Satuan_kerja::whereIn('id', $decodedIds)->get();
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
        'status' => 'integer',
        'satuankerja_id' => 'json'
    ];

    public function perusahaan(){
        return $this->hasOne(Perusahaan::class, 'id_perusahaan', 'id_perusahaan');
    }
    public function profile(){
        return $this->hasOne(Profile::class, 'user_id', 'id');
    }
}
