<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PltAssignment extends Model
{
    use HasFactory;

    protected $table = 'plt_assignments';

    protected $fillable = [
        'original_user_id',
        'plt_user_id',
        'role_name',
        'start_date',
        'end_date',
        'surat_tugas_path',
        'status',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'status' => 'integer',
    ];

    /**
     * Relasi ke Manajer Pengganti/PLT
     */
    public function pltUser()
    {
        return $this->belongsTo(User::class, 'plt_user_id');
    }

    /**
     * Relasi ke Manajer Asli
     */
    public function originalUser()
    {
        return $this->belongsTo(User::class, 'original_user_id');
    }

    /**
     * Scope untuk mendapatkan PLT yang sedang aktif saat ini
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1)
                     ->where('start_date', '<=', now())
                     ->where('end_date', '>=', now());
    }
}
