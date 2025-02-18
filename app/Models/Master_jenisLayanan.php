<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Master_jenisLayanan extends Model
{
    use HasFactory;
    protected $table = 'master_jenisLayanan';

    protected $fillable = [
        'id_jenisLayanan',
        'name',
        'jobs',
        'status'
    ];

    protected $hidden = [
        'parent'
    ];

    protected $appends = [
        'jenis_layanan_hash',
        'parent_hash'
    ];

    protected $casts = [
        'jobs' => 'array'
    ];

    public function getJenisLayananHashAttribute()
    {
        return encryptor($this->id_jenisLayanan);
    }

    public function getParentHashAttribute()
    {
        return $this->parent ? encryptor($this->parent) : null;
    }

    public function child()
    {
        return $this->hasMany(Master_jenisLayanan::class, 'parent', 'id_jenisLayanan');
    }
}
