<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable; // Gunakan MassPrunable agar lebih cepat untuk data besar
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property string $log_name
 * @property string|null $log_type
 * @property string|null $causer_id
 * @property string|null $causer_type
 * @property string|null $subject_type
 * @property string|null $subject_id
 * @property string $description
 * @property array|null $properties
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|\Eloquent|null $causer
 * @property-read Model|\Eloquent|null $subject
 * @method static Builder|Log_activity causedBy(\Illuminate\Database\Eloquent\Model $causer)
 * @method static Builder|Log_activity forSubject(\Illuminate\Database\Eloquent\Model $subject)
 * @method static Builder|Log_activity newModelQuery()
 * @method static Builder|Log_activity newQuery()
 * @method static Builder|Log_activity query()
 * @method static Builder|Log_activity whereCauserId($value)
 * @method static Builder|Log_activity whereCauserType($value)
 * @method static Builder|Log_activity whereCreatedAt($value)
 * @method static Builder|Log_activity whereDescription($value)
 * @method static Builder|Log_activity whereId($value)
 * @method static Builder|Log_activity whereIpAddress($value)
 * @method static Builder|Log_activity whereLogName($value)
 * @method static Builder|Log_activity whereLogType($value)
 * @method static Builder|Log_activity whereProperties($value)
 * @method static Builder|Log_activity whereSubjectId($value)
 * @method static Builder|Log_activity whereSubjectType($value)
 * @method static Builder|Log_activity whereUpdatedAt($value)
 * @method static Builder|Log_activity whereUserAgent($value)
 * @mixin \Eloquent
 */
class Log_activity extends Model
{
    use HasFactory, MassPrunable;

    protected $table = 'logs_activity';

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
