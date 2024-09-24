<?php 
namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class FileUpload
{
    protected $file;
    protected $path;
    protected $idMedia;
    protected $filename;

    public function __construct($file, $path, $filename, $idMedia)
    {
        $this->file = $file;
        $this->path = $path;
        $this->filename = $filename;
        $this->idMedia = $idMedia;
    }

    // Method untuk menyimpan file
    public function store()
    {
        // Menyimpan file ke path yang ditentukan
        return $this->file->storeAs('public/'.$this->path, $this->filename);
    }

    // Method lain seperti misalnya untuk validasi
    public function validate()
    {
        // Kamu bisa tambahkan logika validasi di sini
    }

    public function getIdMedia()
    {
        return $this->idMedia;
    }
}

?>