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
        'pertanyaan',
        'status',
        'version',
        'id_doc_verion',
        'view',
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
        'data_pertanyaan',
    ];

    protected $casts = [
        'version' => 'integer',
        'id_doc_verion' => 'integer',
        'status' => 'integer',
        'pertanyaan' => 'json',
        'id_doc' => 'integer',
    ];

    public function getDocHashAttribute()
    {
        return encryptor($this->id_doc);
    }

    public function getDocVersionHashAttribute()
    {
        return encryptor($this->id_doc_verion);
    }

    public function getDataPertanyaanAttribute()
    {
        $decodedIds = $this->pertanyaan;
        $decodedIds = is_array($decodedIds) ? $decodedIds : [];

        return Master_pertanyaan::whereIn('id_pertanyaan', $decodedIds)->get();
    }


}
