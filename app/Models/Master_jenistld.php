<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Master_jenistld extends Model
{
    use HasFactory;
    protected $table = 'master_jenistld';

    protected $fillable = [
        'name',
        'status'
    ];

    protected $hidden = [
        'id_jenisTld'
    ];

    protected $appends = [
        'jenis_tld_hash'
    ];

    protected $casts = [
        'status' => 'integer'
    ];

    public function getJenisTldHashAttribute()
    {
        return encryptor($this->id_jenisTld);
    }

}
