@props(['shipments'])

<div class="card shadow-sm border-0 mb-4 rounded-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center rounded-top-4">
        <h6 class="m-0 font-weight-bold text-dark">
            <i class="bi bi-truck me-2 text-info"></i>Pengiriman Aktif
        </h6>
        <span class="badge bg-info-subtle text-info rounded-pill">
            {{ count($shipments) }} OTW
        </span>
    </div>

    <div class="card-body p-3 custom-scrollbar" style="max-height: 45vh; overflow-y: auto;">

        @forelse ($shipments as $item)
            <div class="card border-0 shadow-sm mb-3 bg-light border-start border-info">
                <div class="card-body p-3">

                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="badge bg-white text-dark border shadow-sm mb-1">
                                {{ $item['nomor_kontrak'] }}
                            </span>
                            <div class="fw-bold text-dark" style="font-size: 1rem;">
                                {{ $item['nomor_resi'] }}
                            </div>
                        </div>
                        <div class="text-end">
                            <i class="bi bi-box-seam text-muted" style="font-size: 1.2rem;"></i>
                        </div>
                    </div>

                    <div class="mb-2">
                        <small class="text-muted d-block" style="font-size: 0.75rem; line-height: 1.2;">
                            Isi Paket:
                        </small>
                        <span class="text-dark small fw-bold">
                            {{ $item['detail_paket'] }}
                        </span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-2">
                        <small class="text-muted">
                            <i class="bi bi-calendar3 me-1"></i>
                            Periode {{ $item['periode'] }}
                        </small>

                        @if($item['status'] == 'shipping')
                            <span class="badge bg-info text-white">
                                <i class="bi bi-truck me-1"></i>Jalan
                            </span>
                        @else
                            <span class="badge bg-warning text-dark">
                                <i class="bi bi-box me-1"></i>Persiapan
                            </span>
                        @endif
                    </div>

                </div>
            </div>
        @empty
            <div class="text-center py-3 text-muted">
                <i class="bi bi-box2 opacity-25" style="font-size: 3rem;"></i>
                <p class="small mt-2">Tidak ada pengiriman aktif.</p>
            </div>
        @endforelse

    </div>

    <div class="card-footer bg-white text-center py-2 rounded-bottom-4">
        <a href="#" class="text-decoration-none small fw-bold text-info">
            Lihat Semua Riwayat
        </a>
    </div>
</div>
