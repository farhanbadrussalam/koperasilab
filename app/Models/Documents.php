<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id_doc
 * @property string|null $name
 * @property string|null $jenis
 * @property array|null $pertanyaan
 * @property int|null $status 1 = active, 99 = remove
 * @property int|null $version
 * @property int|null $id_doc_version
 * @property string $content
 * @property int|null $id_header
 * @property int|null $id_footer
 * @property string|null $alias
 * @property array|null $variables
 * @property string|null $view
 * @property string|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Documents|null $footer
 * @property-read mixed $data_pertanyaan
 * @property-read mixed $doc_hash
 * @property-read mixed $doc_version_hash
 * @property-read mixed $footer_hash
 * @property-read mixed $header_hash
 * @property-read Documents|null $header
 * @method static \Illuminate\Database\Eloquent\Builder|Documents newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Documents newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Documents query()
 * @method static \Illuminate\Database\Eloquent\Builder|Documents whereAlias($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Documents whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Documents whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Documents whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Documents whereIdDoc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Documents whereIdDocVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Documents whereIdFooter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Documents whereIdHeader($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Documents whereJenis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Documents whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Documents wherePertanyaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Documents whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Documents whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Documents whereVariables($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Documents whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Documents whereView($value)
 * @mixin \Eloquent
 */
class Documents extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'documents';
    protected $primaryKey = 'id_doc';

    protected $fillable = [
        'name',
        'jenis',
        'pertanyaan',
        'status',
        'version',
        'id_doc_verion',
        'content',
        'view',
        'id_header',
        'id_footer',
        'id_perusahaan',
        'no_formulir',
        'orientation',
        'variables',
        'created_by',
        'created_at',
    ];

    protected $hidden = [
        'id_doc',
        'id_doc_verion',
        'id_header',
        'id_footer',
        'pertanyaan',
    ];

    protected $appends = [
        'doc_hash',
        'doc_version_hash',
        'header_hash',
        'footer_hash',
        'data_pertanyaan',
    ];

    protected $casts = [
        'version' => 'integer',
        'id_doc_verion' => 'integer',
        'status' => 'integer',
        'pertanyaan' => 'json',
        'id_doc' => 'integer',
        'id_header' => 'integer',
        'id_footer' => 'integer',
        'variables' => 'json',
    ];

    public function getDocHashAttribute()
    {
        return $this->id_doc ? encryptor($this->id_doc) : null;
    }

    public function getDocVersionHashAttribute()
    {
        return $this->id_doc_verion ? encryptor($this->id_doc_verion) : null;
    }

    public function getHeaderHashAttribute()
    {
        return $this->id_header ? encryptor($this->id_header) : null;
    }

    public function getFooterHashAttribute()
    {
        return $this->id_footer ? encryptor($this->id_footer) : null;
    }

    public function getDataPertanyaanAttribute()
    {
        $decodedIds = $this->pertanyaan;
        $decodedIds = is_array($decodedIds) ? $decodedIds : [];

        return Master_pertanyaan::whereIn('id_pertanyaan', $decodedIds)->get();
    }

    public function footer()
    {
        return $this->belongsTo(Documents::class, 'id_footer', 'id_doc')->withTrashed();
    }

    public function header()
    {
        return $this->belongsTo(Documents::class, 'id_header', 'id_doc')->withTrashed();
    }
}
