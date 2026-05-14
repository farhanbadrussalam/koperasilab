<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

/**
 * @method \Illuminate\Support\Collection<int,string> getRoleNames()
 * @method bool hasRole(string|array $roles)
 * @method bool hasAnyRole(string|array $roles)
 * @method bool hasAllRoles(array $roles)
 * @property-read \Illuminate\Database\Eloquent\Collection<int,\Spatie\Permission\Models\Role> $roles
 * @property int $id
 * @property array|null $satuankerja_id
 * @property int|null $id_perusahaan
 * @property string $name
 * @property int|null $status
 * @property string|null $jabatan
 * @property array|null $jobs
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string|null $google_id
 * @property mixed|null $password
 * @property string|null $ttd
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property string|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property int|null $realtime_notifications
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $selesai_at
 * @property-read \App\Models\Profile|null $profile
 * @property-read mixed $satuankerja
 * @property-read mixed $user_hash
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \App\Models\Perusahaan|null $perusahaan
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User role($roles, $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGoogleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIdPerusahaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereJabatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereJobs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRealtimeNotifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSatuankerjaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSelesaiAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTtd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTwoFactorConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTwoFactorRecoveryCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTwoFactorSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @property int|null $verifikasi_perusahaan 1=valid, 2=tidakvalid, null=belum diverifikasi
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read mixed $request_verify_instansi
 * @property-read mixed $ttd_hash
 * @property-read mixed $ttd_image
 * @property-read \App\Models\Penyelia_petugas|null $penyelia_petugas
 * @property-read \App\Models\Master_ttd|null $tld
 * @method static \Illuminate\Database\Eloquent\Builder|User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereVerifikasiPerusahaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|User withoutTrashed()
 * @mixin \Eloquent
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

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
        'ttd',
        'selesai_at',
        'realtime_notifications',
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
        'satuankerja',
        'profile',
        'ttd_image',
        'ttd_hash',
        'request_verify_instansi'
    ];

    public function tld()
    {
        return $this->hasOne(Master_ttd::class, 'user_id')->where('status', 1)->withTrashed();
    }
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

    public function getTtdHashAttribute()
    {
        return $this->ttd ? encryptor($this->ttd) : null;
    }

    public function getProfileAttribute()
    {
        return Profile::where('user_id', $this->id)->first();
    }

    public function getRequestVerifyInstansiAttribute()
    {
        $request = Users_request::with('perusahaan')->where('id_user', $this->id)->whereIn('status',[1, 90])->first();
        return $request;
    }

    public function getTtdImageAttribute()
    {
        // Cek apakah user punya record TTD
        if ($this->ttd) {
            $ttd = Master_ttd::where('id', $this->ttd)->first();
            // Convert Binary kembali ke Base64 String
            if($ttd) {
                $base64 = $ttd->image_blob;
                return "data:image/png;base64,{$base64}";
            }
        }

        return null; // Atau return path gambar default
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
        'satuankerja_id' => 'json',
        'realtime_notifications' => 'integer'
    ];

    public function perusahaan(){
        return $this->hasOne(Perusahaan::class, 'id_perusahaan', 'id_perusahaan')->withTrashed();
    }
    public function profile(){
        return $this->hasOne(Profile::class, 'user_id', 'id')->withTrashed();
    }

    public function penyelia_petugas(){
        return $this->hasOne(Penyelia_petugas::class, 'id_user', 'id')->with('map_active');
    }


}
