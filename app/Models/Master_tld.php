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
        'kode_lencana',
        'jenis',
        'status',
    ];

    protected $hidden = [
        'id_tld'
    ];

    protected $appends = [
        'tld_hash'
    ];

    public function getTldHashAttribute()
    {
        return encryptor($this->id_tld);
    }
}
