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
        return view('pages.layananJasa.index');
    }

    public function getData() {
        $layanan = Layanan_jasa::where('created_by', Auth::user()->id)
                    ->where('status', 1);

        return DataTables::of($layanan)
                ->addIndexColumn()
                ->editColumn('jenis_layanan', function($data) {
                    return "
                        <div class=''>$data->jenis_layanan</div>
                        <small class='text-body-secondary'>".$data->detail."</small>
                    ";
                })
                ->editColumn('tarif', function($data){
                    return formatCurrency($data->tarif);
                })
                ->addColumn('action', function($data){
                    return '
                        <a class="btn btn-warning btn-sm" href="'.route("layananJasa.edit", $data->id).'">Edit</a>
                        <button class="btn btn-danger btn-sm" onclick="btnDelete('.$data->id.')">Delete</a>
                    ';
                })
                ->rawColumns(['action', 'jenis_layanan'])
                ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data['satuankerja'] = Satuan_kerja::all();
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
            'jenisLayanan' => ['required'],
            'detail' => ['required'],
            'tarif' => ['required']
        ]);

        $dataLayanan = array(
            'satuankerja_id' => $request->satuankerja,
            'user_id' => $request->pj,
            'jenis_layanan' => $request->jenisLayanan,
            'detail' => $request->detail,
            'tarif' => $request->tarif,
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
        $data['layananjasa'] = Layanan_jasa::findOrFail($id);
        $data['token'] = generateToken();

        return view('pages.layananjasa.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = $request->validate([
            'pj' => ['required'],
            'jenisLayanan' => ['required'],
            'detail' => ['required'],
            'tarif' => ['required']
        ]);

        $layanan = Layanan_jasa::findOrFail($id);

        $layanan->user_id = $request->pj;
        $layanan->jenis_layanan = $request->jenisLayanan;
        $layanan->detail = $request->detail;
        $layanan->tarif = $request->tarif;

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
