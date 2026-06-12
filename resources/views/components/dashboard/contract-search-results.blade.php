@if($contracts->isEmpty())
    <div class="card border-0 shadow-sm rounded-4 text-center py-5">
        <div class="card-body">
            <div class="badge bg-danger-subtle rounded-circle p-3 mb-3">
                <i class="bi bi-search text-danger" style="font-size: 2rem;"></i>
            </div>
            <h5 class="fw-bold text-dark">Data Kontrak Tidak Ditemukan</h5>
            <p class="text-muted">Tidak ada kontrak yang cocok dengan nama perusahaan atau nomor kontrak tersebut.</p>
        </div>
    </div>
@else
    <div class="mb-3 text-muted small fw-semibold">
        Ditemukan {{ $contracts->count() }} data kontrak:
    </div>

    @foreach($contracts as $contract)
        @php
            $activePeriode = $contract->periode_active ?? $contract->periode->last();
            $statusKontrakClass = 'bg-secondary';
            $statusKontrakText = 'Non-Aktif';
            if ($contract->status === 1) {
                $statusKontrakClass = 'bg-success';
                $statusKontrakText = 'Berjalan';
            } elseif ($contract->status === 2) {
                $statusKontrakClass = 'bg-primary';
                $statusKontrakText = 'Selesai';
            }
        @endphp

        <div class="card border-0 shadow-sm rounded-4 mb-3 border-start border-4 border-info">
            <div class="card-body p-4">
                {{-- Header Kontrak --}}
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">
                            <i class="bi bi-building me-2 text-info"></i>
                            {{ $contract->pelanggan?->perusahaan?->nama_perusahaan ?? 'Perusahaan tidak diketahui' }}
                        </h5>
                        <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
                            <span class="badge bg-light text-dark border fw-bold">
                                <i class="bi bi-file-earmark-text-fill text-muted me-1"></i>
                                {{ $contract->no_kontrak }}
                            </span>
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-tag-fill text-muted me-1"></i>
                                {{ $contract->layanan_jasa?->nama_layanan ?? '' }}
                                @if($contract->jenis_layanan?->name)
                                    - {{ $contract->jenis_layanan->name }}
                                @elseif($contract->jenisTld?->name)
                                    - {{ $contract->jenisTld->name }}
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge {{ $statusKontrakClass }} rounded-pill px-3 py-2">
                            Kontrak {{ $statusKontrakText }}
                        </span>
                        @can('Kontrak')
                            <a href="{{ route('permohonan.kontrak') }}" class="btn btn-sm btn-outline-info rounded-pill px-3">
                                <i class="bi bi-box-arrow-up-right me-1"></i> Ke Halaman Kontrak
                            </a>
                        @endcan
                    </div>
                </div>

                <hr class="text-muted opacity-25">

                {{-- Status Periode Aktif --}}
                @if($activePeriode)
                    @php
                        $permohonan = $activePeriode->permohonan;
                        
                        // 1. Invoice Status
                        $invoice = $permohonan?->invoice;
                        $invText = 'Belum Mulai';
                        $invBadge = 'bg-secondary-subtle text-secondary';
                        if ($permohonan) {
                            if ($invoice) {
                                switch ($invoice->status) {
                                    case 5:
                                        $invText = 'Lunas';
                                        $invBadge = 'bg-success-subtle text-success';
                                        break;
                                    case 4:
                                        $invText = 'Perlu Verifikasi';
                                        $invBadge = 'bg-info-subtle text-info';
                                        break;
                                    case 3:
                                        $invText = 'Menunggu Bayar';
                                        $invBadge = 'bg-warning-subtle text-warning';
                                        break;
                                    case 7:
                                        $invText = 'Faktur Belum Terbit';
                                        $invBadge = 'bg-secondary-subtle text-secondary';
                                        break;
                                    case 90:
                                        $invText = 'Ditolak';
                                        $invBadge = 'bg-danger-subtle text-danger';
                                        break;
                                    default:
                                        $invText = 'Proses';
                                        $invBadge = 'bg-primary-subtle text-primary';
                                        break;
                                }
                            } else {
                                $invText = 'Belum Terbit';
                                $invBadge = 'bg-secondary-subtle text-secondary';
                            }
                        }

                        // 2. Lab Status
                        $lhu = $permohonan?->lhu;
                        $labText = 'Belum Mulai';
                        $labBadge = 'bg-secondary-subtle text-secondary';
                        if ($permohonan && $lhu) {
                            if ($lhu->status == 2) {
                                $labText = 'TTD Manager';
                                $labBadge = 'bg-warning bg-opacity-25 text-warning-emphasis';
                            } elseif ($lhu->status == 3) {
                                $labText = 'Selesai';
                                $labBadge = 'bg-success-subtle text-success';
                            } else {
                                $stepNow = $lhu->penyelia_map->where('status', 1)->first();
                                if ($stepNow && $stepNow->jobs) {
                                    $labText = $stepNow->jobs->name;
                                    $labBadge = 'bg-info-subtle text-info';
                                } else {
                                    $labText = 'Proses Lab';
                                    $labBadge = 'bg-info-subtle text-info';
                                }
                            }
                        }

                        // 3. Shipping Status
                        $pengiriman = $permohonan?->pengiriman;
                        $shipText = 'Belum Dikirim';
                        $shipBadge = 'bg-secondary-subtle text-secondary';
                        if ($permohonan && $pengiriman) {
                            switch ($pengiriman->status) {
                                case 1:
                                    $shipText = 'Sedang Jalan';
                                    $shipBadge = 'bg-info-subtle text-info';
                                    break;
                                case 2:
                                    $shipText = 'Sampai Tujuan';
                                    $shipBadge = 'bg-success-subtle text-success';
                                    break;
                                case 3:
                                    $shipText = 'On Proses';
                                    $shipBadge = 'bg-primary-subtle text-primary';
                                    break;
                            }
                        }
                    @endphp

                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-dark small">
                                <i class="bi bi-calendar-event text-primary me-1"></i>
                                Status Periode Berjalan: <span class="text-primary">Periode {{ $activePeriode->periode }}</span>
                            </span>
                            <span class="text-muted small">
                                {{ convert_date($activePeriode->start_date, 2) }} s/d {{ convert_date($activePeriode->end_date, 2) }}
                            </span>
                        </div>

                        {{-- Panel visual status --}}
                        <div class="row g-2">
                            {{-- Step 1 --}}
                            <div class="col-md-4">
                                <div class="p-3 rounded-3 border bg-light h-100 d-flex flex-column justify-content-between">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white" style="width:24px;height:24px;font-size:0.75rem;">1</span>
                                        <small class="fw-bold text-secondary">Pembayaran / Invoice</small>
                                    </div>
                                    <div>
                                        <span class="badge {{ $invBadge }} rounded-3 px-2 py-1 small fw-semibold">
                                            {{ $invText }}
                                        </span>
                                        @if($invoice && $invoice->no_invoice)
                                            <div class="text-muted small mt-1" style="font-size:0.7rem;">
                                                No: {{ $invoice->no_invoice }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Step 2 --}}
                            <div class="col-md-4">
                                <div class="p-3 rounded-3 border bg-light h-100 d-flex flex-column justify-content-between">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white" style="width:24px;height:24px;font-size:0.75rem;">2</span>
                                        <small class="fw-bold text-secondary">Proses Lab & LHU</small>
                                    </div>
                                    <div>
                                        <span class="badge {{ $labBadge }} rounded-3 px-2 py-1 small fw-semibold">
                                            {{ $labText }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Step 3 --}}
                            <div class="col-md-4">
                                <div class="p-3 rounded-3 border bg-light h-100 d-flex flex-column justify-content-between">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white" style="width:24px;height:24px;font-size:0.75rem;">3</span>
                                        <small class="fw-bold text-secondary">Pengiriman Logistik</small>
                                    </div>
                                    <div>
                                        <span class="badge {{ $shipBadge }} rounded-3 px-2 py-1 small fw-semibold">
                                            {{ $shipText }}
                                        </span>
                                        @if($pengiriman && $pengiriman->no_resi)
                                            <div class="text-muted small mt-1" style="font-size:0.7rem;">
                                                Resi: <b>{{ $pengiriman->no_resi }}</b>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-secondary py-2 mt-2 text-center rounded-3 small">
                        <i class="bi bi-info-circle me-1"></i> Belum ada informasi periode berjalan untuk kontrak ini.
                    </div>
                @endif

                {{-- Kolaps Semua Periode --}}
                <div class="mt-3">
                    <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" type="button" 
                            data-bs-toggle="collapse" data-bs-target="#collapsePeriods-{{ $contract->id_kontrak }}"
                            aria-expanded="false" aria-controls="collapsePeriods-{{ $contract->id_kontrak }}">
                        <i class="bi bi-list-nested me-1"></i> Tampilkan Semua Periode ({{ $contract->periode->count() }})
                    </button>

                    <div class="collapse mt-3" id="collapsePeriods-{{ $contract->id_kontrak }}">
                        <div class="card card-body bg-light border-0 shadow-inner rounded-3 p-3">
                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                                <i class="bi bi-clock-history me-1 text-secondary"></i>
                                Riwayat Periode Kontrak
                            </h6>
                            <div class="d-flex flex-column gap-3">
                                @foreach($contract->periode as $p)
                                    @php
                                        $pPermohonan = $p->permohonan;
                                        $pInvoice = $pPermohonan?->invoice;
                                        $pLhu = $pPermohonan?->lhu;
                                        $pPengiriman = $pPermohonan?->pengiriman;

                                        // Status Invoice
                                        $pInvText = 'Belum Mulai';
                                        $pInvBadge = 'bg-secondary-subtle text-secondary';
                                        if ($pPermohonan) {
                                            if ($pInvoice) {
                                                switch ($pInvoice->status) {
                                                    case 5: $pInvText = 'Lunas'; $pInvBadge = 'bg-success-subtle text-success'; break;
                                                    case 4: $pInvText = 'Perlu Verifikasi'; $pInvBadge = 'bg-info-subtle text-info'; break;
                                                    case 3: $pInvText = 'Menunggu Bayar'; $pInvBadge = 'bg-warning-subtle text-warning'; break;
                                                    case 7: $pInvText = 'Faktur Belum Terbit'; $pInvBadge = 'bg-secondary-subtle text-secondary'; break;
                                                    case 90: $pInvText = 'Ditolak'; $pInvBadge = 'bg-danger-subtle text-danger'; break;
                                                    default: $pInvText = 'Proses'; $pInvBadge = 'bg-primary-subtle text-primary'; break;
                                                }
                                            } else {
                                                $pInvText = 'Belum Terbit'; $pInvBadge = 'bg-secondary-subtle text-secondary';
                                            }
                                        }

                                        // Status Lab
                                        $pLabText = 'Belum Mulai';
                                        $pLabBadge = 'bg-secondary-subtle text-secondary';
                                        if ($pPermohonan && $pLhu) {
                                            if ($pLhu->status == 2) {
                                                $pLabText = 'TTD Manager'; $pLabBadge = 'bg-warning bg-opacity-25 text-warning-emphasis';
                                            } elseif ($pLhu->status == 3) {
                                                $pLabText = 'Selesai'; $pLabBadge = 'bg-success-subtle text-success';
                                            } else {
                                                $pStepNow = $pLhu->penyelia_map->where('status', 1)->first();
                                                $pLabText = $pStepNow && $pStepNow->jobs ? $pStepNow->jobs->name : 'Proses Lab';
                                                $pLabBadge = 'bg-info-subtle text-info';
                                            }
                                        }

                                        // Status Pengiriman
                                        $pShipText = 'Belum Dikirim';
                                        $pShipBadge = 'bg-secondary-subtle text-secondary';
                                        if ($pPermohonan && $pPengiriman) {
                                            switch ($pPengiriman->status) {
                                                case 1: $pShipText = 'Sedang Jalan'; $pShipBadge = 'bg-info-subtle text-info'; break;
                                                case 2: $pShipText = 'Sampai Tujuan'; $pShipBadge = 'bg-success-subtle text-success'; break;
                                                case 3: $pShipText = 'On Proses'; $pShipBadge = 'bg-primary-subtle text-primary'; break;
                                            }
                                        }
                                    @endphp

                                    <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom pb-2 {{ $loop->last ? 'border-0 pb-0' : '' }} gap-2">
                                        <div>
                                            <span class="fw-bold text-dark small">Periode {{ $p->periode }}</span>
                                            <span class="text-muted small ms-1">({{ convert_date($p->start_date, 2) }} s/d {{ convert_date($p->end_date, 2) }})</span>
                                        </div>
                                        <div class="d-flex flex-wrap align-items-center gap-2">
                                            <span class="badge {{ $pInvBadge }} rounded-pill px-2 py-1" style="font-size:0.7rem;" title="Status Pembayaran">
                                                <i class="bi bi-credit-card me-1"></i>{{ $pInvText }}
                                            </span>
                                            <span class="badge {{ $pLabBadge }} rounded-pill px-2 py-1" style="font-size:0.7rem;" title="Status Proses Lab/LHU">
                                                <i class="bi bi-flask me-1"></i>{{ $pLabText }}
                                            </span>
                                            <span class="badge {{ $pShipBadge }} rounded-pill px-2 py-1" style="font-size:0.7rem;" title="Status Pengiriman">
                                                <i class="bi bi-truck me-1"></i>{{ $pShipText }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    @endforeach
@endif
