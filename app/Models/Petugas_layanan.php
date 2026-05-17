<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read mixed $petugas_hash
 * @property-read \App\Models\tbl_lab|null $lab
 * @property-read \App\Models\User|null $petugas
 * @property-read \App\Models\Satuan_kerja|null $satuankerja
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Petugas_layanan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Petugas_layanan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Petugas_layanan query()
 * @mixin \Eloquent
 */
class Petugas_layanan extends Model
{
    use HasFactory;

    protected $table = 'petugas_layanan';

    protected $fillable = [
        'lab_id',
        'satuankerja_id',
        'user_id',
        'status_verif',
        'status',
        'created_by'
    ];

    protected $hidden = [
        'id',
        'satuankerja_id',
        'user_id',
        'lab_id',
        'created_by'
    ];

    protected $appends = [
        'petugas_hash'
    ];

    public function getPetugasHashAttribute()
    {
        return $this->id ? encryptor($this->id) : null;
    }

    public function lab(){
        return $this->belongsTo(tbl_lab::class, 'lab_id', 'id');
    }

    public function satuankerja(){
        return $this->belongsTo(Satuan_kerja::class, 'satuankerja_id', 'id');
    }

    public function petugas(){
        return $this->belongsTo(User::class, 'user_id', 'id')->withTrashed();
    }

    public function user(){
        return $this->belongsTo(User::class, 'created_by', 'id')->withTrashed();
    }
}
