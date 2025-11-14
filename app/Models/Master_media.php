<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $file_hash
 * @property string $file_ori
 * @property int $file_size
 * @property string $file_type
 * @property string|null $file_path
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $media_hash
 * @property-read \App\Models\Keuangan|null $keuangan
 * @method static \Illuminate\Database\Eloquent\Builder|Master_media newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_media newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_media query()
 * @method static \Illuminate\Database\Eloquent\Builder|Master_media whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_media whereFileHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_media whereFileOri($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_media whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_media whereFileSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_media whereFileType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_media whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_media whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Master_media whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Master_media extends Model
{
    use HasFactory;

    protected $table = 'master_media';

    protected $fillable = [
        'file_hash',
        'file_ori',
        'file_size',
        'file_type',
        'file_path',
        'status'
    ];

    protected $hidden = [
        'id'
    ];

    protected $appends = [
        'media_hash'
    ];

    protected $casts = [
        'status' => 'integer',
        'file_size' => 'integer',
        'id' => 'integer'
    ];

    public function getMediaHashAttribute()
    {
        return $this->id ? encryptor($this->id) : null;
    }

    public function keuangan()
    {
        return $this->belongsTo(Keuangan::class);
    }
}
