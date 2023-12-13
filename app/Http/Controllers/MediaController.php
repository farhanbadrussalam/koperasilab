<?php

namespace App\Http\Controllers;

use App\Models\tbl_media;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class MediaController extends Controller
{
    public function upload($file, $jenis){
        // $file = false;
        // if($request->file('dokumen')){
        //     $file = $request->file('dokumen');
        // }

        $path = $this->createPath($jenis);
        $idMedia = false;
        if($file){
            $realname =  pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $filename = $this->filename($realname, $extension);

            $file->storeAs('public/'.$path, $filename);

            $media = tbl_media::create([
                'file_hash' => $filename,
                'file_ori' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'file_type' => $file->getClientMimeType(),
                'file_path' => $path,
                'status' => 1
            ]);

            $idMedia = $media->id;
        }

        return $idMedia;
    }

    public function update($file, $id_media){
        $media = tbl_media::findOrFail($id_media);

        // $file = false;
        // if($request->file('dokumen')){
        //     $file = $request->file('dokumen');
        // }

        if($file){
            $path = 'public/'.$media->file_path.'/'.$media->file_hash;

            // menghapus file yang sudah ada
            if(Storage::exists($path)){
                Storage::delete($path);
            }

            $realname =  pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $filename = $this->filename($realname, $extension);

            $file->storeAs('public/'.$media->file_path.'/'.$filename);

            $media->file_hash = $filename;
            $media->file_ori = $file->getClientOriginalName();
            $media->file_size = $file->getSize();
            $media->file_type = $file->getClientMimeType();

            $media->update();
        }
    }

    private function filename($name, $extension){
        // Mengambil waktu saat ini dalam bentuk Carbon
        $now = Carbon::now();

        // Mengambil waktu dalam bentuk milidetik
        $milliseconds = $now->timestamp * 1000;

        return md5($name.$milliseconds).'.'.$extension;
    }

    private function createPath($jenis){
        switch ($jenis) {
            case 'jadwal':
                $path = 'dokumen/jadwal';
                break;
            case 'avatar':
                $path = 'images/avatar';
                break;
            case 'permohonan':
                $path = 'dokumen/permohonan';
                break;
            case 'frontdesk':
                $path = 'dokumen/frontdesk';
                break;
            case 'pelaksana':
                $path = 'dokumen/pelaksana';
                break;
            case 'surat_tugas':
                $path = 'dokumen/surat_tugas';
                break;
            case 'lhu':
                $path = 'dokumen/lhu';
                break;
            case 'kip':
                $path = 'images/kip';
                break;
            default:
                $path = 'dokumen';
                break;
        }

        return $path;
    }
}
