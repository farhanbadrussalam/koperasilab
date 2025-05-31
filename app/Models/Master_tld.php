<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Master_tld extends Model
{
    use HasFactory;

    protected $table = 'master_tld';

    protected $primaryKey = 'id_tld';

    public $timestamps = false;

    protected $fillable = [
        'no_seri_tld',
        'merk',
        'jenis',
        'status',
        'tanggal_pengadaan',
        'kepemilikan',
        'digunakan'
    ];

    protected $hidden = [
        'id_tld'
    ];

    protected $appends = [
        'tld_hash'
    ];

    protected $casts = [
        'status' => 'integer'
    ];

    public function getTldHashAttribute()
    {
        return encryptor($this->id_tld);
    }

    public function pemilik()
    {
        return $this->belongsTo(Perusahaan::class, 'kepemilikan', 'id_perusahaan');
    }
}
