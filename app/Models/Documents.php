<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documents extends Model
{
    use HasFactory;

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
        return encryptor($this->id_doc);
    }

    public function getDocVersionHashAttribute()
    {
        return encryptor($this->id_doc_verion);
    }

    public function getHeaderHashAttribute()
    {
        return encryptor($this->id_header);
    }

    public function getFooterHashAttribute()
    {
        return encryptor($this->id_footer);
    }

    public function getDataPertanyaanAttribute()
    {
        $decodedIds = $this->pertanyaan;
        $decodedIds = is_array($decodedIds) ? $decodedIds : [];

        return Master_pertanyaan::whereIn('id_pertanyaan', $decodedIds)->get();
    }

    public function footer()
    {
        return $this->belongsTo(Documents::class, 'id_footer', 'id_doc');
    }

    public function header()
    {
        return $this->belongsTo(Documents::class, 'id_header', 'id_doc');
    }

}
