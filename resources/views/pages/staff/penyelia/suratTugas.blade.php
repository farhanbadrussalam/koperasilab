@extends('layouts.main')

@section('content')
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item px-3">
            <a href="{{ $type != 'verif' ? route('staff.penyelia') : route('manager.surat_tugas') }}" class="icon-link text-danger"><i
                    class="bi bi-chevron-left fs-3 fw-bolder h-100"></i> Kembali</a>
        </li>
    </ul>
    <div class="card shadow-sm m-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 col-12">
                    <label class="fw-bolder">No Kontrak</label>
                    <div>{{ $penyelia->permohonan->kontrak->no_kontrak ?? '-' }}</div>
                </div>
                <div class="col-md-6 col-12">
                    <label class="fw-bolder">Jenis</label>
                    <div>{{ $penyelia->permohonan->jenis_layanan->name ?? '-' }}</div>
                </div>
                <div class="col-md-6 col-12">
                    <label class="fw-bolder">Pengguna</label>
                    <div>{{ $penyelia->permohonan->jumlah_pengguna ?? '-' }}</div>
                </div>
                <div class="col-md-6 col-12">
                    <label class="fw-bolder">Tipe Kontrak</label>
                    <div>{{ $penyelia->permohonan->tipe_kontrak ?? '-' }}</div>
                </div>
                <div class="col-md-6 col-12">
                    <label class="fw-bolder">Pelanggan</label>
                    <div>{{ $penyelia->permohonan->pelanggan->name ?? '-' }}</div>
                </div>
                <div class="col-md-6 col-12">
                    <label class="fw-bolder">Jenis TLD</label>
                    <div>{{ $penyelia->permohonan->jenisTld->name ?? '-' }}</div>
                </div>
                <div class="col-md-6 col-12">
                    <label class="fw-bolder">Instansi</label>
                    <div>{{ $penyelia->permohonan->pelanggan->perusahaan->nama_perusahaan ?? '-' }}</div>
                </div>
                <div class="col-md-6 col-12">
                    <label class="fw-bolder">Periode</label>
                    <div id="periodePermohonan"></div>
                </div>
                <div class="col-md-12 my-2 d-flex justify-content-end">
                    <a class="btn btn-secondary" href="{{ url('laporan/tandaterima/' . $penyelia->penyelia_hash) }}"
                        target="_blank">Document tanda terima</a>
                </div>
                <hr>
                <div class="col-md-6 col-12 mb-4">
                    <label for="" class="fw-bolder">Tanggal Mulai</label>
                    <input type="text" name="date_start" id="date_start" class="form-control datepicker {{ in_array($type, ['verif', 'show']) ? "bg-secondary-subtle" : '' }}" value="{{ $penyelia->start_date ? (in_array($type, ['verif', 'show']) ? convert_date($penyelia->start_date, 2) : $penyelia->start_date) : '' }}" {{ in_array($type, ['verif', 'show']) ? "readonly" : '' }}>
                </div>
                <div class="col-md-6 col-12 mb-4">
                    <label for="" class="fw-bolder">Tanggal Selesai</label>
                    <input type="text" name="date_end" id="date_end" 
                        class="form-control {{ $penyelia->start_date ? (in_array($type, ['verif', 'show']) ? "bg-secondary-subtle" : '') : "bg-secondary-subtle" }}" 
                        value="{{ $penyelia->end_date ? (in_array($type, ['verif', 'show']) ? convert_date($penyelia->end_date, 2) : $penyelia->end_date) : '' }}" {{ $penyelia->start_date ? (in_array($type, ['verif', 'show']) ? "readonly" : '') : "readonly" }} >
                </div>
                {{-- Load list jobs --}}
                <ul id="sortJobs">
                    @foreach ($jobs as $job)
                    <li class="col-12" data-idjobs="{{ $job->jobs_hash }}">
                        <div class="card shadow-sm border border-1 rounded-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div class="fw-bolder">@if (!in_array($type, ['verif', 'show']))
                                        <span class="moveon cursormove"><i class="bi bi-grip-vertical"></i></span>
                                    @endif {{ $job->name }}</div>
                                    @if (!in_array($type, ['verif', 'show']))
                                    <button class="btn btn-primary btn-sm"
                                        onclick="tambahPetugas('{{ $job->jobs_hash }}', {{ $loop->index }}, '{{ $job->name }}')"><i
                                            class="bi bi-person-plus-fill"></i> Tambah petugas</button>
                                    @endif
                                </div>
                                <div class="mt-3" id="list-petugas-{{ $job->jobs_hash }}">
                                    <p class="w-100 text-center fs-4 m-auto"><i class="bi bi-person-fill-slash"></i> Belum
                                        ada petugas</p>
                                </div>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
                @if (in_array($type, ['verif', 'show']) || $penyelia->ttd)
                <div class="col-md-12 d-flex justify-content-center">
                    <div class="wrapper" id="content-ttd-1"></div>
                </div>
                @endif
                <div class="col-12 text-end">
                    @if($type != 'show')
                    <button class="btn btn-success" onclick=saveSuratTugas(this)>Simpan</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalAddPetugas" tabindex="-1" aria-labelledby="modalAddPetugasLabel">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalAddPetugasLabel">List petugas <span id="modal-name-jobs">Penyelia LAB</span></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" class="form-control w-100" placeholder="Search petugas">
                    <div class="pt-3" id="modal-list-petugas">
                        <div class="border-bottom py-1 d-flex justify-content-between px-2 hover-1 rounded">
                            <div>
                                <span class="fw-medium">Ray Clarke</span>
                                <span class="text-secondary">ray.c@acke.com</span>
                            </div>
                            <div class="text-success cursoron"><i class="bi bi-person-check"></i> Pilih</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        const listJobs = @json($jobs);
        const idPenyelia = "{{ $penyelia->penyelia_hash }}";
        const dataPenyelia = @json($penyelia);
        const typeSurat = "{{ $type }}";
    </script>
    <script src="{{ asset('js/staff/surat_tugas.js') }}"></script>
@endpush
