<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tbl_lhu extends Model
{
    use HasFactory;

    protected $table = 'tbl_lhu';
    protected $fillable = [
        'no_kontrak',
        'id_jadwal',
        'tgl_selesai',
        'level',
        'active',
        'surat_tugas',
        'document',
        'ttd_1',
        'ttd_1_by',
        'ttd_2',
        'ttd_2_by',
        'created_by'
    ];
    protected $hidden = [
        'id'
    ];
    protected $appends = [
        'lhu_hash'
    ];

    public function getLhuHashAttribute()
    {
        return encryptor($this->id);
    }

    public function signature_1(){
        return $this->belongsTo(User::class, 'ttd_1_by', 'id');
    }

    public function jadwal(){
        return $this->belongsTo(jadwal::class, 'id_jadwal', 'id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function jawaban(){
        return $this->hasMany(Jawaban_lhu::class, 'lhu_id', 'id');
    }
}
