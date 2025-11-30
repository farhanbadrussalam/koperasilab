@if($data)
    <div class="card bg-primary-subtle border-0 rounded-3 mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Nomor Resi</small>
                    <h4 class="fw-bold text-primary mb-1">{{ $data->nomor_resi }}</h4>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-white text-dark border">
                            {{ $data->kurir }} </span>
                        <span class="text-muted small">
                            • Dikirm pada {{ convert_date($data->send_at, 2) }}
                        </span>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    @if($data->status == 2)
                        <div class="text-success fw-bold">
                            <i class="bi bi-check-circle-fill fs-3 d-block"></i>
                            PAKET DITERIMA
                        </div>
                    @elseif($data->status == 1)
                        <div class="text-warning fw-bold">
                            <i class="bi bi-arrow-repeat fs-3 d-block"></i>
                            SEDANG DALAM PROSES
                        </div>
                    @else
                        <div class="text-info fw-bold">
                            <i class="bi bi-truck fs-3 d-block"></i>
                            SEDANG DIKIRIM
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-6">
            <small class="text-muted d-block">Penerima</small>
            <span class="fw-bold text-dark">{{ $data->nama_penerima }}</span>
            <small class="d-block text-muted" style="font-size: 0.8rem;">{{ $data->alamat_tujuan }}</small>
        </div>
        <div class="col-6">
            <small class="text-muted d-block">Isi Paket</small>
            <span class="fw-bold text-dark">{{ $data->isi_paket }}</span>
            <small class="d-block text-muted" style="font-size: 0.8rem;">Kontrak: {{ $data->nomor_kontrak }}</small>
        </div>
    </div>

    @if($data->histories && count($data->histories) > 0)
    <h6 class="fw-bold border-bottom pb-2 mb-3">Riwayat Perjalanan</h6>

    <div class="tracking-timeline ps-2" style="max-height: 40vh; overflow-y: auto;">
        @foreach($data->histories as $index => $history)
            <div class="d-flex mb-4 position-relative">
                @if(!$loop->last)
                    <div class="position-absolute" style="left: 6px; top: 10px; bottom: -24px; width: 2px; background-color: #e9ecef;"></div>
                @endif

                <div class="me-3 mt-1">
                    @if($index == 0)
                        <div class="rounded-circle bg-success border border-4 border-success-subtle" style="width: 14px; height: 14px;"></div>
                    @else
                        <div class="rounded-circle bg-secondary" style="width: 14px; height: 14px;"></div>
                    @endif
                </div>

                <div>
                    <div class="fw-bold text-dark {{ $index == 0 ? 'text-success' : '' }}">
                        {{ $history->status_text }}
                    </div>
                    <small class="text-muted d-block">
                        {{ \Carbon\Carbon::parse($history->created_at)->format('d M Y, H:i') }}
                    </small>
                    <small class="text-secondary">
                        {{ $history->lokasi }}
                    </small>
                </div>
            </div>
        @endforeach
    </div>
    @endif
@else
    <div class="text-center py-5">
        <div class="badge bg-danger-subtle rounded-circle p-3 mb-3">
            <i class="bi bi-search text-danger" style="font-size: 2rem;"></i>
        </div>
        <h5 class="fw-bold text-dark">Data Tidak Ditemukan</h5>
        <p class="text-muted">Nomor resi yang Anda masukkan tidak terdaftar di sistem.</p>
    </div>
@endif
