<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Master_pengguna extends Model
{
    use HasFactory;

    protected $table = 'master_pengguna';
    protected $primaryKey = 'id_pengguna';

    protected $fillable = [
        'id_pengguna',
        'id_radiasi',
        'id_perusahaan',
        'kode_lencana',
        'nik',
        'name',
        'id_divisi',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'ktp',
        'keterangan',
        'status',
        'created_by',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        'id_pengguna',
        'id_radiasi',
        'id_perusahaan',
        'ktp'
    ];

    protected $appends = [
        'pengguna_hash'
    ];

    protected $casts = [
        'id_radiasi' => 'array',
        'status' => 'integer'
    ];

    public function getPenggunaHashAttribute()
    {
        return encryptor($this->id_pengguna);
    }

    /**
     * Get the media ktp associated with the Master_pengguna
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function media_ktp()
    {
        return $this->belongsTo(Master_media::class, 'ktp', 'id');
    }

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class, 'id_perusahaan', 'id_perusahaan');
    }

    public function divisi()
    {
        return $this->belongsTo(Master_divisi::class, 'id_divisi', 'id_divisi');
    }

}
