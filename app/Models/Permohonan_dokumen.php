<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_dokumen
 * @property int|null $id_permohonan
 * @property int|null $id_kontrak
 * @property int|null $id_doc_template
 * @property int|null $periode
 * @property string|null $nomer
 * @property string|null $nama
 * @property int|null $status
 * @property string|null $jenis
 * @property string|null $ttd
 * @property int|null $ttd_by
 * @property string|null $catatan
 * @property array|null $variables
 * @property array|null $content_value
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Documents|null $doc_template
 * @property-read mixed $dokumen_hash
 * @property-read mixed $permohonan_hash
 * @property-read \App\Models\User|null $usersig
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_dokumen newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_dokumen newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_dokumen query()
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_dokumen whereCatatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_dokumen whereContentValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_dokumen whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_dokumen whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_dokumen whereIdDocTemplate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_dokumen whereIdDokumen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_dokumen whereIdKontrak($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_dokumen whereIdPermohonan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_dokumen whereJenis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_dokumen whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_dokumen whereNomer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_dokumen wherePeriode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_dokumen whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_dokumen whereTtd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_dokumen whereTtdBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_dokumen whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permohonan_dokumen whereVariables($value)
 * @property-read mixed $ttd_image
 * @mixin \Eloquent
 */
class Permohonan_dokumen extends Model
{
    use HasFactory;

    protected $table = 'permohonan_dokumen';
    protected $primaryKey = 'id_dokumen';

    protected $fillable = [
        'id_permohonan',
        'id_kontrak',
        'id_doc_template',
        'periode',
        'nomer',
        'nama',
        'status',
        'jenis',
        'ttd',
        'ttd_by',
        'catatan',
        'variables',
        'content_value',
        'created_by',
        'created_at',
    ];

    protected $hidden = [
        'updated_at',
        'id_dokumen'
    ];

    protected $appends = [
        'dokumen_hash',
        'permohonan_hash',
        'ttd_image',
    ];

    protected $casts = [
        'status' => 'integer',
        'id_dokumen' => 'integer',
        'id_permohonan' => 'integer',
        'id_kontrak' => 'integer',
        'created_by' => 'integer',
        'id_doc_template' => 'integer',
        'content_value' => 'array',
        'variables' => 'array'
    ];

    public function getDokumenHashAttribute()
    {
        return $this->id_dokumen ? encryptor($this->id_dokumen) : null;
    }

    public function getPermohonanHashAttribute()
    {
        return $this->id_permohonan ? encryptor($this->id_permohonan) : null;
    }

    public function getTtdImageAttribute()
    {
        // Cek apakah user punya record TTD
        if ($this->ttd) {
            $ttd = Master_ttd::withTrashed()->where('id', $this->ttd)->first();
            // Convert Binary kembali ke Base64 String
            if($ttd) {
                $base64 = $ttd->image_blob;
                return "data:image/png;base64,{$base64}";
            }
        }

        return null; // Atau return path gambar default
    }

    public function doc_template(){
        return $this->belongsTo(Documents::class, 'id_doc_template', 'id_doc')->withTrashed();
    }

    public function usersig(){
        return $this->belongsTo(user::class, 'ttd_by', 'id');
    }
}
