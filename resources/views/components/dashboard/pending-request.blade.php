@props(['requests'])

<div class="card shadow-sm border-0 rounded-4 mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center rounded-top-4">
        <h6 class="m-0 font-weight-bold">
            <i class="bi bi-file-earmark-plus me-2 text-primary"></i>Pengajuan Baru
        </h6>
        <a href="#" class="btn btn-sm btn-primary shadow-sm rounded-pill px-3">
            <i class="bi bi-plus-lg me-1"></i> Buat Baru
        </a>
    </div>

    <div class="card-body p-3">

        @forelse ($requests as $item)
            @php
                $isRevisi = $item['status'] == 'revisi';
                $borderClass = $isRevisi ? 'border-danger' : 'border-warning';
                $bgBadge = $isRevisi ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning';
                $iconStatus = $isRevisi ? 'bi-exclamation-triangle-fill' : 'bi-hourglass-split';
                $textStatus = $isRevisi ? 'Perlu Revisi Data' : 'Menunggu Verifikasi Admin';
            @endphp

            <div class="card border-0 shadow-sm mb-3 border-start bg-light {{ $borderClass }}">
                <div class="card-body p-3">
                    <div class="row align-items-center">

                        <div class="col-md-7 mb-2 mb-md-0">
                            <div class="d-flex align-items-center mb-1">
                                <span class="badge bg-secondary bg-opacity-10 text-secondary me-2">
                                    {{ $item['no_tiket'] }}
                                </span>
                                <small class="text-muted">
                                    <i class="bi bi-calendar3 me-1"></i>
                                    {{ convert_date($item['tanggal_pengajuan'], 2) }}
                                </small>
                            </div>
                            <h6 class="fw-bold text-dark mb-1" style="font-size: 1.1rem;">
                                {{ $item['jenis_layanan'] }}
                            </h6>
                            @if($isRevisi)
                                <small class="text-danger fst-italic">
                                    <i class="bi bi-info-circle me-1"></i>Admin: "Mohon upload ulang KTP..."
                                </small>
                            @endif
                        </div>

                        <div class="col-md-5 text-md-end text-start">
                            <div class="d-flex flex-column align-items-md-end align-items-start justify-content-center h-100">

                                <span class="badge {{ $bgBadge }} mb-2 px-3 py-2 rounded-pill">
                                    <i class="bi {{ $iconStatus }} me-1"></i> {{ $textStatus }}
                                </span>

                                <div>
                                    @if($isRevisi)
                                        <a href="#" class="btn btn-sm btn-danger shadow-sm px-3">
                                            <i class="bi bi-pencil-square me-1"></i> Perbaiki
                                        </a>
                                    @else
                                        <a href="#" class="btn btn-sm btn-outline-secondary shadow-sm px-3">
                                            <i class="bi bi-eye me-1"></i> Detail
                                        </a>
                                    @endif
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
            </div>

        @empty
            <div class="text-center py-2 bg-white rounded">
                <div class="mb-3">
                    <i class="bi bi-folder-plus text-muted opacity-25" style="font-size: 3rem;"></i>
                </div>
                <h6 class="text-muted fw-bold">Belum ada pengajuan</h6>
                <p class="text-muted small mb-3">Ajukan layanan baru sekarang untuk memulai.</p>
                <a href="#" class="btn btn-outline-primary btn-sm rounded-pill px-4">
                    Buat Pengajuan
                </a>
            </div>
        @endforelse

    </div>

    <div class="card-footer bg-white py-3 d-flex justify-content-end rounded-bottom-4">
        <nav aria-label="Page navigation example">
            <ul class="pagination pagination-sm mb-0 d-flex align-items-center justify-content-end mt-1">
                <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                </li>
                <li class="page-item"><a class="page-link" href="#">1</a></li>
                <li class="page-item active" aria-current="page">
                    <a class="page-link" href="#">2</a>
                </li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item">
                    <a class="page-link" href="#">Next</a>
                </li>
            </ul>
        </nav>
    </div>
</div>
