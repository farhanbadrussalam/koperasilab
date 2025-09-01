<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Traits\RestApi;

use App\Models\Documents;

use App\Http\Controllers\MediaController;

use App\Services\PdfRenderService;

use DB;
use Auth;

class DocumentController extends Controller
{
    use RestApi;

    public function __construct(){
        $this->media = resolve(MediaController::class);
    }
    public function index()
    {
        $data = [
            'title' => 'Master Document',
            'module' => 'document'
        ];

        return view('pages.management.document.index', $data);
    }

    public function create(Request $request){
        $type = $request->type;

        $data = [
            'title' => 'Tambah '. $type .' Document',
            'module' => 'document',
            'type' => $type,
            'headers' => Documents::where('jenis', 'header')->where('status', 1)->get(),
            'footers' => Documents::where('jenis', 'footer')->where('status', 1)->get(),
        ];

        return view('pages.management.document.tambah', $data);
    }

    public function edit($id, Request $request){
        $data = [
            'title' => 'Edit Document',
            'module' => 'document',
            'type' => $request->type,
            'data' => Documents::findOrFail(decryptor($id)),
            'headers' => Documents::where('jenis', 'header')->where('status', 1)->get(),
            'footers' => Documents::where('jenis', 'footer')->where('status', 1)->get()
        ];

        return view('pages.management.document.tambah', $data);
    }

    public function show(Request $request){
        $limit = $request->has('limit') ? $request->limit : 10;
        $page = $request->has('page') ? $request->page : 1;
        $search = $request->has('search') ? $request->search : '';
        $jenis = $request->has('jenis') ? $request->jenis : '';

        DB::beginTransaction();
        try {
            $query = Documents::where('jenis', $jenis)
            ->when($search, function($q, $search){
                $q->where('name', 'like', '%'.$search.'%');
            })
            ->orderBy('created_at', 'desc')
            ->offset(($page - 1) * $limit)
            ->where('status', 1)
            ->limit($limit)
            ->paginate($limit);

            $arr = $query->toArray();
            $this->pagination = Arr::except($arr, 'data');
            DB::commit();

            return $this->output($query);
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function store(Request $request){

        DB::beginTransaction();
        $request->validate([
            'title' => 'required',
            'content' => 'required',
        ]);
        try {
            if($request->variables) {
                $pisahVariable = explode(',', $request->variables);
            } else {
                $pisahVariable = null;
            }
            $header = $request->header ? decryptor($request->header) : null;
            $footer = $request->footer ? decryptor($request->footer) : null;

            $data = [
                'name' => $request->title,
                'content' => $request->content,
                'variables' => $pisahVariable,
                'jenis' => $request->jenis,
                'status' => 1,
                'created_by' => Auth::user()->id,
            ];
            $header && $data['id_header'] = $header;
            $footer && $data['id_footer'] = $footer;

            Documents::create($data);

            DB::commit();
            return $this->output(array('msg' => 'Document Behasil ditambahkan'));
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function update(Request $request, $id){
        DB::beginTransaction();
        $request->validate([
            'title' => 'required',
            'content' => 'required',
        ]);
        try {
            if($request->variables) {
                $pisahVariable = explode(',', $request->variables);
            } else {
                $pisahVariable = null;
            }
            $used  = collect($request->input('used_images', []));
            $header = $request->header ? decryptor($request->header) : null;
            $footer = $request->footer ? decryptor($request->footer) : null;

            $data = [
                'name' => $request->title,
                'content' => $request->content,
                'variables' => $pisahVariable,
                'jenis' => $request->jenis,
                'status' => 1,
            ];
            $header && $data['id_header'] = $header;
            $footer && $data['id_footer'] = $footer;

            Documents::where('id_doc', decryptor($id))->update($data);

            DB::commit();
            return $this->output(array('msg' => 'Document Behasil diupdate'));
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }

    public function editHeader($id){
        $data = [
            'title' => 'Edit Header Document',
            'module' => 'document',
            'data' => Documents::findOrFail(decryptor($id))
        ];

        return view('pages.management.document.edit_header', $data);
    }

    public function upload_image(Request $request){
        $file = $request->hasFile('upload') ? $request->file('upload') : false;

        if($file){
            $upload = $this->media->upload($file, 'document');
            $upload->store();
            return response()->json([ 'url' => $this->media->getMediaUrl($upload->getIdMedia()) ]);
        }
    }

    public function destroy($id){
        DB::beginTransaction();
        try {
            // set status 99
            Documents::where('id_doc', decryptor($id))->update(['status' => 99]);
            DB::commit();
            return $this->output(array('msg' => 'Document Behasil dihapus'));
        } catch (\Exception $ex) {
            info($ex);
            DB::rollBack();
            return $this->output(array('msg' => $ex->getMessage()), 'Fail', 500);
        }
    }
}
