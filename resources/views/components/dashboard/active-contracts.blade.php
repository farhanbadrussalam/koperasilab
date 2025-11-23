@props(['contracts'])

<div class="card shadow-sm border-0 rounded-4 mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center rounded-top-4">
        <h6 class="m-0 font-weight-bold">
            <i class="bi bi-briefcase me-2 text-primary"></i>Kontrak & Layanan Berjalan
        </h6>
        <span class="badge bg-primary-subtle text-primary rounded-pill">
            {{ count($contracts) }} Aktif
        </span>
    </div>

    <div class="card-body p-3"> @forelse ($contracts as $contract)
            <div class="card border-0 shadow-sm mb-3 border-start border-primary bg-light">
                <div class="card-body p-3">
                    <div class="row align-items-center">

                        <div class="col-lg-5 col-md-12 mb-3 mb-lg-0">
                            <div class="d-flex align-items-center mb-1">
                                <span class="badge bg-primary text-white me-2">
                                    {{ $contract['nomor_kontrak'] }}
                                </span>
                                <small class="text-muted">
                                    <i class="bi bi-calendar-range me-1"></i>
                                    {{ convert_date($contract['tgl_mulai'], 2) }} -
                                    {{ convert_date($contract['tgl_selesai'], 2) }}
                                </small>
                            </div>
                            <h5 class="fw-bold text-dark mb-1">{{ $contract['jenis_layanan'] }}</h5>
                            <p class="text-muted small mb-0 text-truncate">
                                {{ $contract['nama_perusahaan'] }}
                            </p>
                        </div>

                        <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small fw-bold text-dark">Progress Layanan</span>
                                <span class="small text-primary fw-bold">
                                    Periode {{ $contract['periode_berjalan'] }} / {{ $contract['total_periode'] }}
                                </span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-primary" role="progressbar"
                                     style="width: {{ ($contract['periode_berjalan'] / $contract['total_periode']) * 100 }}%"
                                     aria-valuenow="{{ $contract['periode_berjalan'] }}" aria-valuemin="0" aria-valuemax="{{ $contract['total_periode'] }}">
                                </div>
                            </div>
                            <div class="mt-1">
                                <small class="text-muted" style="font-size: 0.7rem;">
                                    Estimasi Selesai: <br>{{ convert_date($contract['tgl_selesai'], 2) }}
                                </small>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6 text-lg-end text-start">

                            <div class="mb-2 d-flex flex-column align-items-lg-end align-items-start gap-1">
                                @if($contract['status_bayar'] == 'unpaid')
                                    <span class="badge bg-warning-subtle text-warning border border-warning">
                                        <i class="bi bi-exclamation-circle me-1"></i> Menunggu Pembayaran
                                    </span>
                                @else
                                    <span class="badge bg-success-subtle text-success border border-success">
                                        <i class="bi bi-check-circle me-1"></i> Keuangan Lunas
                                    </span>
                                @endif

                                <span class="badge bg-info-subtle text-info border border-info">
                                    <i class="bi bi-activity me-1"></i> {{ $contract['status_lab'] }}
                                </span>
                            </div>

                            <div>
                                @if($contract['status_bayar'] == 'unpaid')
                                    <a href="#" class="btn btn-sm btn-warning shadow-sm me-1 text-white fw-bold">
                                        <i class="bi bi-wallet2"></i> Bayar
                                    </a>
                                @endif
                                <a href="#" class="btn btn-sm btn-outline-primary shadow-sm">
                                    Detail <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-2 bg-white rounded">
                <div class="mb-3">
                    <i class="bi bi-briefcase text-muted opacity-25" style="font-size: 3rem;"></i>
                </div>
                <h6 class="text-muted fw-bold">Tidak ada kontrak aktif</h6>
                <p class="text-muted small mb-0">Permohonan Anda yang disetujui akan muncul di sini.</p>
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
