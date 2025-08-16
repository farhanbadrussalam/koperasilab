@extends('layouts.main')

@section('content')
<div class="card shadow-sm m-4">
    <div class="card-body">
        <div class="d-flex">
            <div class="flex-grow-1">
                <button class="btn btn-outline-secondary btn-sm" onclick="reload()"><i class="bi bi-arrow-clockwise"></i> Refresh data</button>
                <button class="btn btn-outline-secondary btn-sm" onclick="clearFilter()">
                    <i class="bi bi-funnel"></i> Clear Filter <span class="badge text-bg-secondary d-none" id="countFilter">4</span>
                </button>
            </div>
        </div>
        <div id="list-filter"></div>
        <div class="my-3">
            <div class="body-placeholder my-3" id="list-placeholder">
                @for ($i = 0; $i < 3; $i++)
                <div class="card mb-2">
                    <div class="card-body row align-items-center">
                        <div class="placeholder-glow col-12 col-md-3 d-flex flex-column">
                            <div class="placeholder w-50 mb-1"></div>
                            <div class="placeholder w-50 mb-1"></div>
                            <div class="placeholder w-50 mb-1"></div>
                            <div class="placeholder w-75 mb-1"></div>
                        </div>
                        <div class="placeholder-glow col-6 col-md-3">
                            <div class="placeholder w-50 mb-1"></div>
                        </div>
                        <div class="placeholder-glow col-6 col-md-4 text-center">
                            <div class="placeholder w-50 mb-1"></div>
                            <div class="placeholder w-75 mb-1"></div>
                            <div class="placeholder w-50 mb-1"></div>
                            <div class="placeholder w-75 mb-1"></div>
                        </div>
                        <div class="placeholder-glow col-6 col-md-2 text-center">
                            <div class="placeholder w-50 mb-1"></div>
                        </div>
                    </div>
                </div>
                @endfor
            </div>
            <div class="body my-3" id="list-container">

            </div>
            <div aria-label="Page navigation example" id="list-pagination">

            </div>
        </div>
    </div>
</div>
{{-- Create modal surat persetujuan --}}
<div class="modal fade" id="verify_modal_surat_pengujian" tabindex="-1" aria-labelledby="modal_title" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Verifikasi surat pengujian</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label for="inputPemilik" class="form-label">Pemilik</label>
                <input type="text" name="name" id="inputPemilik" class="form-control" autocomplete="false" required readonly>
                <input type="hidden" name="txt_id_penyelia" id="txt_id_penyelia" class="form-control">
            </div>
            <div class="mb-3">
                <label for="inputAlamat" class="form-label">Alamat</label>
                <textarea name="name" id="inputAlamat" cols="30" rows="3" class="form-control" autocomplete="false" required readonly></textarea>
            </div>
            <div class="mb-3">
                <label for="" class="form-label">List pengujian</label>
                <ol class="list-group list-group-numbered overflow-auto max-h-max" id="list-pengujian">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div class="ms-2 me-auto">
                            <div>M0152-18-019</div>
                            <div class="fw-bold">P-0001</div>
                        </div>
                        <div>Jenis Pengujian</div>
                    </li>
                </ol>
            </div>
            <div class="d-flex justify-content-center">
                <div class="wrapper" id="content-ttd"></div>
            </div>
        </div>
        <div class="modal-footer d-flex justify-content-between">
          <button type="button" class="btn btn-danger" id="btnDecline">Tolak</button>
          <a type="button" class="btn btn-secondary" href="#" target="_blank" id="btnLihatSurat">Lihat surat</a>
          <button type="button" class="btn btn-primary" id="btnApprove">Setuju</button>
        </div>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/manager/suratTugas.js') }}"></script>
@endpush
