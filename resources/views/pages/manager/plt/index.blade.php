@extends('layouts.main')

@section('content')
    <div class="m-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4><i class="bi bi-person-badge me-2"></i> Manajemen Pelaksana Tugas (PLT)</h4>
            <button class="btn btn-primary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#pltModal">
                <i class="bi bi-plus-circle"></i> Tambah PLT
            </button>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Pegawai (PLT)</th>
                                <th>Role</th>
                                <th>Masa Berlaku</th>
                                <th>Surat Tugas</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignments as $idx => $plt)
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td>{{ $plt->pltUser->name ?? '-' }}</td>
                                    <td>{{ $plt->role_name }}</td>
                                    <td>
                                        {{ $plt->start_date ? $plt->start_date->format('d/m/Y') : '-' }}
                                        <i class="bi bi-arrow-right mx-1"></i>
                                        {{ $plt->end_date ? $plt->end_date->format('d/m/Y') : '-' }}
                                    </td>
                                    <td>
                                        @if ($plt->surat_tugas_path)
                                            <a href="{{ asset('storage/' . $plt->surat_tugas_path) }}" target="_blank"
                                                class="btn btn-sm btn-outline-info">
                                                <i class="bi bi-file-earmark-pdf"></i> Lihat File
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if ($plt->status == 1 && $plt->end_date >= now())
                                            <span class="badge bg-success">Aktif</span>
                                        @elseif($plt->status == 1 && $plt->end_date < now())
                                            <span class="badge bg-secondary">Kadaluarsa</span>
                                        @else
                                            <span class="badge bg-danger">Dicabut</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($plt->status == 1)
                                            <form action="{{ route('plt.assign.revoke', $plt->id) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Apakah Anda yakin ingin mencabut penugasan PLT ini?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-danger" title="Cabut PLT">
                                                    <i class="bi bi-x-circle"></i> Cabut
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Belum ada riwayat penugasan PLT.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Assign PLT -->
    <div class="modal fade" id="pltModal" tabindex="-1" aria-labelledby="pltModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('plt.assign.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="pltModalLabel">Assign Pelaksana Tugas (PLT)</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="role_name" value="Manager Keuangan">
                        <div class="mb-3">
                            <label for="plt_user_id" class="form-label">Pilih Pegawai (PLT)</label>
                            <select class="form-select select2-ajax" id="plt_user_id" name="plt_user_id" style="width: 100%"
                                required>
                                <!-- Options will be populated by AJAX -->
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">Tanggal Berakhir</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="surat_tugas" class="form-label">Upload Surat Tugas (PDF/Image)</label>
                            <input class="form-control" type="file" id="surat_tugas" name="surat_tugas"
                                accept=".pdf,image/*" required>
                            <div class="form-text">Maksimal ukuran file 2MB.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan & Aktifkan PLT</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2-ajax').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#pltModal'),
                placeholder: 'Cari nama atau email pegawai...',
                allowClear: true,
                ajax: {
                    url: "{{ route('plt.assign.search_user') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term // search term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                },
                minimumInputLength: 1
            });
        });
    </script>
@endpush
