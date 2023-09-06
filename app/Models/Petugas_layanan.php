<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        return encryptor($this->id);
    }

    public function lab(){
        return $this->belongsTo(tbl_lab::class, 'lab_id', 'id');
    }

    public function satuankerja(){
        return $this->belongsTo(Satuan_kerja::class, 'satuankerja_id', 'id');
    }

    public function petugas(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
