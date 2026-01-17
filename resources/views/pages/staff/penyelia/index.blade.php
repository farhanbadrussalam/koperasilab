@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content col-md-12">
        <div class="container">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                  <button class="nav-link active" id="surattugas-tab" onclick="switchLoadTab(1)" data-bs-toggle="tab" data-bs-target="#surattugas-tab-pane" type="button" role="tab" aria-controls="surattugas-tab-pane" aria-selected="true">Penerbitan surat tugas</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="penerbitanlhu-tab" onclick="switchLoadTab(2)" data-bs-toggle="tab" data-bs-target="#penerbitanlhu-tab-pane" type="button" role="tab" aria-controls="penerbitanlhu-tab-pane" aria-selected="true">Penyeliaan LHU</button>
                </li>
            </ul>
            <div class="card shadow-sm mt-2">
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
                                    <div class="placeholder-glow col-12 col-md-4 d-flex flex-column">
                                        <div class="placeholder w-50 mb-1"></div>
                                        <div class="placeholder w-50 mb-1"></div>
                                        <div class="placeholder w-50 mb-1"></div>
                                        <div class="placeholder w-75 mb-1"></div>
                                    </div>
                                    <div class="placeholder-glow col-6 col-md-3 text-end text-md-start">
                                        <div class="placeholder w-50 mb-1"></div>
                                    </div>
                                    <div class="placeholder-glow col-6 col-md-2">
                                        <div class="placeholder w-50 mb-1"></div>
                                    </div>
                                    <div class="placeholder-glow col-6 col-md-3 text-center">
                                        <div class="placeholder w-50 mb-1"></div>
                                    </div>
                                </div>
                            </div>
                            @endfor
                        </div>
                        <div class="body my-3" id="list-container"></div>
                        <div aria-label="Page navigation example" id="list-pagination"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

{{-- Create modal surat persetujuan --}}
<div class="modal fade" id="create_modal_surat_pengujian" tabindex="-1" aria-labelledby="modal_title" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Buat surat pengujian</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-6 border-end">
                    <div class="mb-3">
                        <label for="inputPemilik" class="form-label">Pemilik</label>
                        <div class="rounded border p-2 overflow-auto max-h-max" id="inputPemilik"></div>
                        <input type="hidden" name="txt_id_penyelia" id="txt_id_penyelia" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="inputAlamat" class="form-label">Alamat</label>
                        <div class="rounded border p-2 overflow-auto max-h-max" id="inputAlamat"></div>
                    </div>
                    <div class="mb-3">
                        <label for="inputJenisPengujian" class="form-label">Jenis Pengujian</label>
                        <div class="rounded border p-2 overflow-auto max-h-max" id="inputJenisPengujian"></div>
                    </div>
                    <div class="mb-3">
                        <label for="" class="form-label">Nama Sample/Alat</label>
                        <div class="rounded border p-2 overflow-auto max-h-max" id="list-sample">
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row" id="content-pertanyaan">

                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer d-flex justify-content-end">
          <button type="button" class="btn btn-primary" onclick="btnCreatePengujian(this)">Ajukan pengujian</button>
        </div>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
@include('component.progressModal')
<!-- /.modal -->
@endsection
@push('scripts')
    <script>
        const listJobs = @json($listJobs);
    </script>
    <script src="{{ asset('js/staff/penyelia.js') }}"></script>
@endpush
