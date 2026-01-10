<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable; // Gunakan MassPrunable agar lebih cepat untuk data besar
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Log_proses extends Model
{
    use HasFactory, MassPrunable;

    protected $table = 'logs_proses';

    // Kita gunakan guarded id agar semua field lain bisa diisi (mass assignment)
    protected $guarded = ['id'];

    /**
     * Konversi otomatis kolom JSON ke Array PHP.
     * Ini PENTING agar Anda bisa memanggil $log->properties['old'] tanpa json_decode manual.
     */
    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Relasi ke Target (Subject).
     * Contoh: Permohonan, Invoice, Tld, dll.
     * Mengembalikan object model yang sedang di-log.
     */
    public function subject()
    {
        return $this->morphTo();
    }

    /**
     * Relasi ke Pelaku (Causer).
     * Biasanya User, tapi dibuat morphTo jaga-jaga jika ada sistem lain/bot.
     */
    public function causer()
    {
        return $this->morphTo();
    }

    /**
     * Scope helper untuk memfilter log berdasarkan User tertentu.
     * Cara pakai: ActivityLog::causedBy($user)->get();
     */
    public function scopeCausedBy(Builder $query, Model $causer)
    {
        return $query->where('causer_type', $causer->getMorphClass())
                     ->where('causer_id', $causer->getKey());
    }

    /**
     * Scope helper untuk memfilter log berdasarkan Subject tertentu.
     * Cara pakai: ActivityLog::forSubject($permohonan)->get();
     */
    public function scopeForSubject(Builder $query, Model $subject)
    {
        return $query->where('subject_type', $subject->getMorphClass())
                     ->where('subject_id', $subject->getKey());
    }

    /**
     * Fitur PRUNABLE (Pembersihan Otomatis).
     * Menentukan query data mana yang boleh dihapus otomatis.
     * Contoh: Hapus log yang lebih tua dari 1 tahun.
     */
    public function prunable()
    {
        // Ganti 'subYear()' dengan 'subMonths(3)' jika ingin per 3 bulan.
        return static::where('created_at', '<=', now()->subYear());
    }
}
