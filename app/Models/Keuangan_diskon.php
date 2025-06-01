<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keuangan_diskon extends Model
{
    use HasFactory;

    protected $table = "keuangan_diskon";
    public $timestamps = false;

    protected $fillable = [
        'id_keuangan',
        'name',
        'diskon'
    ];

    protected $hidden = [
        'id_diskon',
        'id_keuangan'
    ];

    protected $appends = [
        'keuangan_hash',
        'diskon_hash'
    ];

    protected $casts = [
        'diskon' => 'integer',
        'id_diskon' => 'integer',
        'id_keuangan' => 'integer'
    ];

    public function getKeuanganHashAttribute()
    {
        return encryptor($this->id_keuangan);
    }

    public function getDiskonHashAttribute()
    {
        return encryptor($this->id_diskon);
    }
}
