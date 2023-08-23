<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Layanan_jasa;
use App\Models\Satuan_kerja;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Psr\Http\Message\ResponseInterface;

use Illuminate\Support\Facades\Session;
use Auth;
use DataTables;

class LayananJasaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['token'] = generateToken();
        return view('pages.layananJasa.index', $data);
    }

    public function getData() {
        $layanan = Layanan_jasa::where('status', 1);

        if(!Auth::user()->hasRole('Super Admin')){
            $layanan->where('created_by', Auth::user()->id);
        }

        return DataTables::of($layanan)
                ->addIndexColumn()
                ->editColumn('nama_layanan', function($data) {
                    return "
                        <div class=''>$data->nama_layanan</div>
                        <div role='button'>
                            <span class='badge text-bg-info' onclick='showJenis(this)' data-jenis='$data->jenis_layanan'>Jenis Layanan</span>
                        </div>
                    ";
                })
                ->addColumn('action', function($data){
                    $idDel = "'".encryptor($data->id)."'";
                    $user = Auth::user();
                    $btnAction = '<div class="text-center">';
                    $user->hasPermissionTo('Layananjasa.edit') && $btnAction .= '<a class="btn btn-warning btn-sm m-1" href="'.route("layananJasa.edit", encryptor($data->id)).'"><i class="bi bi-pencil-square"></i></a>';
                    $user->hasPermissionTo('Layananjasa.delete') && $btnAction .= '<button class="btn btn-danger btn-sm m-1" onclick="btnDelete('.$idDel.')"><i class="bi bi-trash3-fill"></i></a>';
                    $btnAction .= '</div>';

                    return $btnAction;
                })
                ->rawColumns(['action', 'nama_layanan'])
                ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $_token = generateToken();
        $data['satuankerja'] = Satuan_kerja::where('id', Auth::user()->satuankerja_id)->get();
        $data['token'] = $_token;
        return view('pages.layananJasa.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = $request->validate([
            'satuankerja' => ['required'],
            'pj' => ['required'],
            'name_layanan' => ['required']
        ]);


        $arrJenis = array();
        foreach ($request->jenisLayanan as $key => $jenis) {
            $arrJenis[$key] = array(
                'jenis' => $jenis,
                'tarif' => $request->tarif[$key]
            );
        }
        $dataLayanan = array(
            'satuankerja_id' => $request->satuankerja,
            'user_id' => $request->pj,
            'nama_layanan' => $request->name_layanan,
            'jenis_layanan' => json_encode($arrJenis),
            'status' => 1,
            'created_by' => Auth::user()->id
        );

        Layanan_jasa::create($dataLayanan);

        return redirect()->route('layananJasa.index')->with('success', 'Berhasil di tambah');
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
        $data['satuankerja'] = Satuan_kerja::all();
        $data['layananjasa'] = Layanan_jasa::findOrFail(decryptor($id));
        $data['jenisLayanan']= json_decode($data['layananjasa']->jenis_layanan);
        $data['token'] = generateToken();
        return view('pages.layananjasa.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $idHash = decryptor($id);
        $validator = $request->validate([
            'pj' => ['required'],
            'nama_layanan' => ['required']
        ]);

        $arrJenis = array();
        foreach ($request->jenisLayanan as $key => $jenis) {
            $arrJenis[$key] = array(
                'jenis' => $jenis,
                'tarif' => $request->tarif[$key]
            );
        }

        $layanan = Layanan_jasa::findOrFail($idHash);

        $layanan->user_id = $request->pj;
        $layanan->nama_layanan = $request->nama_layanan;
        $layanan->jenis_layanan = json_encode($arrJenis);

        $layanan->update();

        return redirect()->route('layananJasa.index')->with('success', 'Berhasil di update');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    private function _resourceAPI($method, $url, $params = [])
    {
        $_token = generateToken();

        // $httpClient = new Client([
        //     'timeout' => 20
        // ]);
        $dataRes = '';
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $_token, // Ganti dengan token yang valid
        ])->get(url('/api/getPegawai'), $params);

        // $response = $httpClient->request($method, url('/api/getPegawai'), [
        //     'headers' => [
        //         'Authorization' => 'Bearer ' . $_token,
        //     ],
        //     'form_params' => $params
        // ]);

        dd($response->successful());
        $dataRes = json_decode($response->getBody()->getContents(), true);
        $dataRes['status_code'] = $response->getStatusCode();
        // try {
        // } catch (\Throwable $th) {
        //     //throw $th;
        // }


        return $dataRes;
    }
}
