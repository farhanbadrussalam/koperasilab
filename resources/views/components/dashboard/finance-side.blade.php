@props(['topDebtors', 'activities'])

<div class="card shadow-sm border-0 rounded-4 mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center rounded-top-4">
        <h6 class="m-0 font-weight-bold text-dark">
            <i class="bi bi-exclamation-octagon-fill me-2 text-danger"></i>Prioritas Penagihan
        </h6>
    </div>
    <div class="card-body p-0">
        <ul class="list-group list-group-flush">
            @forelse($topDebtors as $debtor)
                <li class="list-group-item py-3">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div>
                            <h6 class="text-dark fw-bold mb-0 text-sm">{{ $debtor->nama_perusahaan }}</h6>
                            <small class="text-muted" style="font-size: 0.75rem;">
                                {{ $debtor->total_invoice }} Invoice Unpaid
                            </small>
                        </div>
                        <div class="text-end">
                            <span class="d-block fw-bold text-danger text-sm">
                                Rp {{ number_format($debtor->total_hutang / 1000000, 1, ',', '.') }} Jt
                            </span>
                        </div>
                    </div>

                    <div class="progress my-2" style="height: 4px;">
                        <div class="progress-bar bg-danger" role="progressbar"
                             style="width: {{ $debtor->persentase }}%"></div>
                    </div>

                    <div class="d-flex justify-content-end mt-2">
                        <a href="https://wa.me/{{ $debtor->no_hp }}?text=Halo..." target="_blank"
                           class="btn btn-xs btn-outline-success rounded-pill px-3"
                           style="font-size: 0.7rem;">
                            <i class="bi bi-whatsapp me-1"></i> Ingatkan
                        </a>
                    </div>
                </li>
            @empty
                <li class="list-group-item text-center py-4 text-muted small">
                    Tidak ada piutang tertunggak.
                </li>
            @endforelse
        </ul>
    </div>
    {{-- <div class="card-footer bg-white text-center py-2 rounded-bottom-4">
        <a href="#" class="text-decoration-none small fw-bold text-danger">
            Lihat Semua Piutang <i class="bi bi-arrow-right"></i>
        </a>
    </div> --}}
</div>
