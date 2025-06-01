<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Master_divisi extends Model
{
    use HasFactory;

    protected $table = 'master_divisi';
    protected $primaryKey = 'id_divisi';

    protected $fillable = [
        'id_divisi',
        'kode_lencana',
        'id_perusahaan',
        'name',
        'status',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        // 'created_at',
        // 'updated_at',
    ];

    protected $appends = [
        'divisi_hash'
    ];

    protected $casts = [
        'status' => 'integer',
        'id_divisi' => 'integer',
        'id_perusahaan' => 'integer',
        'created_by' => 'integer',
    ];

    public function getDivisiHashAttribute()
    {
        return encryptor($this->id_divisi);
    }

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class, 'id_perusahaan', 'id_perusahaan');
    }
}
