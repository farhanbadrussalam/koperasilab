<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Petugas_layanan;
use App\Models\tbl_lab;
use App\Models\User;
use App\Models\Satuan_kerja;
use Spatie\Permission\Models\Permission;
use Auth;
use DataTables;

use App\Http\Controllers\API\SendMailAPI;

class PetugasLayananController extends Controller
{
    public function __construct() {
        $this->SendMailAPI = resolve(SendMailAPI::class);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['token'] = generateToken();
        $data['lab'] = tbl_lab::all();
        $data['satuanKerja'] = Satuan_kerja::all();
        $data['otorisasi'] = Permission::where('name', 'like', 'Otorisasi-%')->get();
        return view('pages.petugas.index', $data);
    }

    public function getData()
    {
        $petugas = Petugas_layanan::where('status', '1');

        if(!Auth::user()->hasRole('Super Admin')){
            $petugas->where('satuankerja_id', Auth::user()->satuankerja_id);
        }

        return DataTables::of($petugas)
                ->addIndexColumn()
                ->addColumn('content', function($data) {
                    if(isset($data->petugas->profile->avatar)){
                        $avatar = asset("storage/images/avatar/".$data->petugas->profile->avatar);
                    }else{
                        $avatar = asset("assets/img/default-avatar.jpg");
                    }

                    $userPetugas = User::where('id', $data->user_id)->first();
                    $otorisasi = $userPetugas->getDirectPermissions();
                    $btnOtorisasi = "";
                    foreach ($otorisasi as $key => $value) {
                        $btnOtorisasi .= '<button class="btn btn-outline-dark btn-sm m-1" role="button">'.stringSplit($value->name, "Otorisasi-").'</button>';
                    }
                    $status = statusFormat('petugas', $data->status_verif);
                    return '
                        <div class="card m-0">
                            <div class="card-body d-flex p-1">
                                <div class="flex-grow-1 p-2 d-flex m-auto">
                                    <div>
                                        <img src=" '.$avatar.' " class="img-circle border shadow-sm" alt="Avatar"  onerror="this.src=`'.asset("assets/img/default-avatar.jpg").'`" style="width: 5em;" />
                                    </div>
                                    <div class="px-3 my-auto">
                                        <div class="text-break fw-bolder">'.$data->petugas->name.'</div>
                                        <div>'.$data->petugas->email.'</div>
                                        <div>'.$data->lab->name_lab.'</div>
                                    </div>
                                </div>
                                <div class="p-2 m-auto">
                                    <div class="d-flex flex-wrap justify-content-end">
                                        '.$btnOtorisasi.'
                                    </div>
                                </div>
                                <div class="p-2 m-auto">'.$status.'</div>
                                <div class="p-2 m-auto flex-column d-flex">
                                    <button role="button" class="btn btn-outline-warning btn-sm mb-2" data-petugasid="'.$data->petugas_hash.'" onclick="btnEdit(this)"><i class="bi bi-pencil-square"></i></button>
                                    <button role="button" class="btn btn-outline-danger btn-sm" data-id="'.$data->petugas_hash.'" onclick="btnDelete(this)"><i class="bi bi-trash-fill"></i></button>
                                </div>
                            </div>
                        </div>
                    ';
                })
                ->filter(function ($query) {
                    if(request()->has('search')){
                        $query->whereHas('petugas', function($qPetugas) {
                            $qPetugas->where('name', 'like', "%" . request('search') . "%");
                        });
                    }

                    if(request()->has('filterStatus')){
                        if(request('filterStatus')){
                            $status = decryptor(request('filterStatus'));
                            $query->where('status_verif', $status);
                        }
                    }

                    if(request()->has('filterLab')){
                        if(request('filterLab')){
                            $labId = decryptor(request('filterLab'));
                            $query->where('lab_id', $labId);
                        }
                    }
                })
                ->rawColumns(['content'])
                ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data['token'] = generateToken();
        return view('pages.petugas.index', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = $request->validate([
            'satuankerja' => 'required',
            'satuan_lab' => 'required',
            'pegawai' => 'required|unique:petugas_layanan,user_id'
        ]);

        $satuankerja = decryptor($request->satuankerja);
        $satuan_lab = decryptor($request->satuan_lab);
        $idPegawai = $request->pegawai;

        $dataUser = User::where('id', $idPegawai)->first();

        $create = Petugas_layanan::create([
            'lab_id' => $satuan_lab,
            'satuankerja_id' => $satuankerja,
            'user_id' => $idPegawai,
            'status_verif' => 1,
            'status' => 1,
            'created_by' => Auth::user()->id
        ]);

        foreach ($request->otorisasi as $key => $value) {
            $dataUser->givePermissionTo(decryptor($value));
            $request->nameOtorisasi = $value;
        }

        // send mail to petugas and notif
        $request->id = $create->petugas_hash;
        $this->SendMailAPI->verifikasiPetugas($request);

        # Send notifikasi
        $sendNotif = notifikasi(array(
            'to_user' => $idPegawai,
            'type' => 'Petugas Layanan'
        ), "Anda telah ditambahkan menjadi petugas layanan, silahkan cek E-mail untuk melakukan verifikasi");

        return redirect()->route('petugasLayanan.index')->with('success', 'Berhasil di tambah');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $petugasId = decryptor($request->petugasId);
        $petugas = Petugas_layanan::findOrFail($petugasId);
        $labId = decryptor($request->satuan_lab);

        $dataUser = User::where('id', $petugas->user_id)->first();

        $petugas->lab_id = $labId;

        $dataUser->syncPermissions($request->otorisasi);

        $petugas->update();

        return response()->json(['message' => 'Petugas berhasil diupdate'], 200);
    }

    public function verifikasiPetugas($id)
    {
        $id = decryptor($id);
        $update = Petugas_layanan::where('id', $id)->update(array(
            'status_verif' => 2
        ));

        return view('emails.resultPetugasLayanan');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $petugasId = decryptor($id);
        $data = Petugas_Layanan::findOrFail($petugasId);
        $dataUser = User::where('id', $data->user_id)->first();

        $permission = $dataUser->getDirectPermissions();
        foreach ($permission as $key => $value) {
            $dataUser->revokePermissionTo($value->name);
        }
        $data->delete();

        return response()->json(['message' => 'Petugas berhasil dihapus'], 200);
    }
}
