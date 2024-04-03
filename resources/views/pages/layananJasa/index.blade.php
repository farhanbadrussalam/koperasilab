@extends('layouts.main')

@section('content')
<div class="card p-0 m-0 shadow border-0">
    <div class="card-body">
        <div class="row d-flex align-items-center mb-4 px-3">
            <h4 class="col-12 col-md-10">&nbsp;</h4>
            @can('Layananjasa.create')
            <a class="btn btn-primary col-12 col-md-2" href="javascript:void(0)" id="create_layanan">
                <i class="bi bi-plus"></i>
                Created
            </a>
            @endcan
        </div>
        <div class="row mt-2">
            <div class="mb-4 d-flex justify-content-between ">
                <div class="d-flex">

                </div>
                <div class="">
                    <div class="input-group">
                        <input type="text" name="search" id="search" class="form-control">
                        <button class="btn btn-info" id="btn-search"><i class="bi bi-search"></i></button>
                    </div>
                </div>
            </div>
            <div class="overflow-y-auto">
                <div>
                    <div id="skeleton-container" class="placeholder-glow">
                        @for ($a=0; $a < 5; $a++)
                        <div class="placeholder rounded w-100 mb-2 bg-secondary" style="height: 50px;"></div>
                        @endfor
                    </div>
                    <div id="content-container">
                    </div>
                    <div id="pagination-container"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalJenisLayanan" tabindex="-1" aria-labelledby="modalJenisLayanan" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-body">
            <div class="row fw-bolder">
                <h4 class="col-6">Jenis Layanan</h4>
                <h4 class="col-6">Tarif</h4>
            </div>
          <div id="isi-jenislayanan" class="px-2">

          </div>
          <div class="mt-2 text-center w-100">
              <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
</div>

{{-- Modal Create --}}
<div class="modal fade" id="modal_form" tabindex="-1" aria-labelledby="modal_title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal_title">Create Layanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- Satuan kerja --}}
                <div class="mb-3">
                    <label for="selectSatuanKerja" class="form-label">Satuan kerja</label>
                    <select name="satuankerja" id="selectSatuanKerja" class="form-control">
                        <option value="">-- Select --</option>
                        @foreach ($satuankerja as $key => $satuan)
                            <option value="{{ $satuan->satuan_hash }}">{{ $satuan->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="selectPJ" class="form-label">Penanggung Jawab</label>
                    <div id="invalid-pj" class="rounded">
                        <select name="selectPj" id="selectPj" class="form-control">
                            <option value="">-- Select --</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="inputNamaLayanan" class="form-label">Nama Layanan</label>
                    <input type="text" id="inputNamaLayanan" name="inputNamaLayanan" class="form-control">
                    <div class="invalid-feedback d-none" id="invalid-namalayanan"></div>
                </div>

                <div class="mb-3">
                    <label for="inputBiayaLayanan" class="form-label">Biaya Layanan</label>
                    <div id="contentBiayaLayanan">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-grey" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="save-layanan"><i class="bi bi-floppy2-fill"></i> Save</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal buat permohonan --}}
<div class="modal fade" id="modal_permohonan" tabindex="-1" aria-labelledby="modal_title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal_title">Form Permohonan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body row">
                <form action="#" method="post" id="formPermohonan" class="row">
                    @csrf
                    <div class="mb-3">
                        <label for="permohonan_namalayanan" class="form-label">Nama Layanan</label>
                        <input type="text" class="form-control" id="permohonan_namalayanan" name="namalayanan" readonly>
                        <input type="hidden" name="layanan_hash" id="permohonan_layananHash" readonly>
                    </div>

                    <div class="mb-3 col-md-6">
                        <label for="permohonanJenis" class="form-label">Jenis <span class="fw-bold fs-14 text-danger">*</span></label>
                        <select name="desc_biaya" id="permohonanJenis" class="form-control" required>
                            <option value="">-- Select --</option>
                        </select>
                    </div>

                    <div class="mb-3 col-md-6">
                        <label for="permohonan_biaya" class="form-label">Biaya</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control rupiah" id="permohonan_biaya" name="biaya" readonly>
                        </div>
                    </div>

                    <div class="mb-3 col-md-6">
                        <label for="permohonan_noBapeten" class="form-label">Nomor BAPETEN</label>
                        <input type="number" class="form-control" id="permohonan_noBapeten" name="no_bapeten" autocomplete="false" required>
                    </div>

                    <div class="mb-3 col-md-6">
                        <label for="permohonan_jenisLimbah" class="form-label">Jenis limbah <span class="fw-bold fs-14 text-danger">*</span></label>
                        <input type="text" class="form-control" id="permohonan_jenisLimbah" name="jenis_limbah" autocomplete="false" required>
                    </div>

                    <div class="mb-3 col-md-6">
                        <label for="permohonan_radioaktif" class="form-label">Sumber Radioaktif <span class="fw-bold fs-14 text-danger">*</span></label>
                        <input type="text" class="form-control" id="permohonan_radioaktif" name="sumber_radioaktif" autocomplete="false" required>
                    </div>

                    <div class="mb-3 col-md-6">
                        <label for="permohonan_jumlah" class="form-label">Jumlah <span class="fw-bold fs-14 text-danger">*</span></label>
                        <input type="number" class="form-control" id="permohonan_jumlah" name="jumlah" autocomplete="false" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">File pendukung</label>
                        <div id="contentFilePermohonan"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-grey" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="save-permohonan"><i class="bi bi-floppy2-fill"></i> Create</button>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
@vite(['resources/js/pages/layananjasa.js'])
@endpush
