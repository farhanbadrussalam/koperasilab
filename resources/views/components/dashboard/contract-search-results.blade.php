@if ($contracts->isEmpty())
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

    @foreach ($contracts as $contract)
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

        <div class="card mb-3 smooth-height hover-effect border border-light-subtle rounded-4 shadow-sm">
            <div class="card-body p-4">
                {{-- Row Header --}}
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 border-bottom pb-2">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge bg-secondary-subtle fw-normal rounded-pill text-secondary-emphasis">
                            {{ $contract->jenis_layanan_parent?->name }} - {{ $contract->jenis_layanan?->name }}
                        </span>

                        @if (isset($contract->document_kontrak) && count($contract->document_kontrak) > 0)
                            @php
                                $doc = $contract->document_kontrak->first();
                                $statusTtd = $doc->ttd ? 'Sudah Ditandatangani' : 'Belum Ditandatangani';
                                $ttdClass = $doc->ttd
                                    ? 'bg-success-subtle text-success-emphasis'
                                    : 'bg-warning-subtle text-warning-emphasis';
                            @endphp
                            <span class="badge {{ $ttdClass }} fw-normal rounded-pill">{{ $statusTtd }}</span>
                        @endif

                        @if ($contract->is_zerocek == 1)
                            <span class="badge bg-info-subtle fw-normal rounded-pill text-info-emphasis"><i
                                    class="bi bi-check-circle me-1"></i>Zero Check</span>
                        @else
                            <span class="badge bg-danger-subtle fw-normal rounded-pill text-danger-emphasis"><i
                                    class="bi bi-x-circle me-1"></i>Bukan Zero Check</span>
                        @endif

                        @if ($contract->is_have_tld == 1)
                            <span class="badge bg-primary-subtle fw-normal rounded-pill text-primary-emphasis"><i
                                    class="bi bi-check-circle me-1"></i>Mempunyai TLD</span>
                        @else
                            <span class="badge bg-danger-subtle fw-normal rounded-pill text-danger-emphasis"><i
                                    class="bi bi-x-circle me-1"></i>Tidak Mempunyai TLD</span>
                        @endif
                    </div>
                    <div>
                        <span class="badge {{ $statusKontrakClass }} rounded-pill px-3 py-2">
                            Kontrak {{ $statusKontrakText }}
                        </span>
                    </div>
                </div>

                {{-- Row Content --}}
                <div class="row align-items-center">
                    <div class="col-md-7 col-12">
                        <div class="fs-5 my-2">
                            <div class="fw-bold text-dark">
                                #{{ $contract->no_kontrak }}
                            </div>
                            <div class="text-body-tertiary fs-7">
                                {{ $contract->jenisTld?->name }} - Layanan {{ $contract->layanan_jasa?->nama_layanan }}
                            </div>
                            <div class="text-body-tertiary fs-7">
                                <div><i class="bi bi-building-fill me-1"></i>
                                    {{ $contract->pelanggan?->perusahaan?->nama_perusahaan ?? 'Perusahaan tidak diketahui' }}
                                </div>
                            </div>
                            <div class="d-flex gap-3 text-body-tertiary fs-7">
                                <div><i class="bi bi-calendar-fill me-1"></i>
                                    {{ $contract->created_at ? convert_date($contract->created_at, 4) : '-' }}</div>
                                <div><i class="bi bi-cash-stack me-1"></i>
                                    {{ 'Rp ' . number_format($contract->total_harga, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="col-md-5 col-12 align-self-center my-2 my-md-0">
                        @php
                            $jml_periode = $contract->periode_all['jml_periode'] ?? 0;
                            $periode_selesai = $contract->periode
                                ->filter(function ($d) {
                                    return $d->periode != 0 && $d->selesai == 1 && $d->status == 1;
                                })
                                ->count();
                            $progress = $jml_periode > 0 ? round(($periode_selesai / $jml_periode) * 100) : 0;
                            $txtProgress = $progress == 100 ? 'Selesai' : "{$periode_selesai}/{$jml_periode}";
                            $successClass = $progress >= 100 ? 'bg-success' : '';
                        @endphp
                        <div class="d-flex align-items-center gap-1 flex-column w-100">
                            <span class="fw-bold small text-muted">Progress Periode:</span>
                            <div class="progress w-100" role="progressbar" aria-valuenow="{{ $progress }}"
                                aria-valuemin="0" aria-valuemax="100" style="height: 8px;">
                                <div class="progress-bar {{ $successClass }}" style="width: {{ $progress }}%">
                                </div>
                            </div>
                            <div class="text-center small text-muted mt-1">
                                {{ $txtProgress }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Detail Periode Berjalan & Pengembalian --}}
                <div class="mt-3 bg-light rounded-3 p-3">
                    <div class="fw-bold text-dark mb-2 small">
                        <i class="bi bi-calendar-event text-primary me-1"></i>
                        Status Periode Berjalan:
                    </div>
                    <div class="px-2" id="listPeriodeNowSearch-{{ $contract->kontrak_hash }}">
                        <div class="text-center py-3">
                            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                            <span class="ms-2 small text-muted">Memuat status periode...</span>
                        </div>
                    </div>

                    <div id="htmlPengembalianSearch-{{ $contract->kontrak_hash }}"></div>
                </div>

                {{-- Tombol Tindakan Lain --}}
                <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2 border-top pt-2">
                    <div>
                        @if ($contract->periode_all && $contract->periode_all['jml_periode'] > 1)
                            <button class="btn btn-sm btn-outline-secondary rounded-pill px-3"
                                onclick="showPeriode('{{ $contract->kontrak_hash }}')">
                                <i class="bi bi-clock-history me-1"></i> Lihat Periode Lain
                                ({{ $contract->periode->count() }})
                            </button>
                        @endif
                    </div>
                    <div>
                        @can('Kontrak')
                            <a href="{{ route('permohonan.kontrak') }}"
                                class="btn btn-sm btn-outline-info rounded-pill px-3">
                                <i class="bi bi-box-arrow-up-right me-1"></i> Ke Halaman Kontrak
                            </a>
                        @endcan
                    </div>
                </div>

            </div>
        </div>

        <script>
            $(document).ready(function() {
                const contractData = @json($contract);
                const activePeriode = @json($activePeriode);

                // 1. Hitung detailPengiriman
                let detailPengiriman = [];
                let arrFind = ['tld', 'lhu', 'invoice'];

                if (contractData && contractData.pengiriman) {
                    for (const pengiriman of contractData.pengiriman) {
                        let detail = pengiriman.detail.filter(detail => arrFind.includes(detail.jenis));
                        if (detail.length > 0) {
                            detail.map(d => detailPengiriman.push({
                                jenis: d.jenis,
                                periode: d.periode ? d.periode : (pengiriman.periode ? pengiriman.periode :
                                    0),
                                status: pengiriman.status,
                                no_resi: pengiriman.no_resi ?? false,
                                tipe_kontrak: pengiriman.permohonan ? pengiriman.permohonan.tipe_kontrak :
                                    false,
                                permohonan_hash: pengiriman.permohonan ? pengiriman.permohonan
                                    .permohonan_hash : false
                            }));
                        }
                    }
                }

                // 2. Render Periode Berjalan menggunakan modalPeriode
                if (activePeriode && typeof modalPeriode !== 'undefined') {
                    const htmlPeriode = modalPeriode.htmlPeriode(activePeriode, contractData, detailPengiriman, arrFind,
                        false);
                    $(`#listPeriodeNowSearch-${contractData.kontrak_hash}`).html(htmlPeriode);
                } else {
                    $(`#listPeriodeNowSearch-${contractData.kontrak_hash}`).html(`
                        <div class="alert alert-secondary py-2 text-center rounded-3 small mb-0">
                            <i class="bi bi-info-circle me-1"></i> Belum ada informasi periode berjalan untuk kontrak ini.
                        </div>
                    `);
                }

                // 3. Render Pengembalian TLD
                let htmlPengembalian = '';
                let tldTidakDigunakan = contractData.rincian_list_tld || [];

                let activePeriodeIsLast = false;
                if (activePeriode && contractData.periode_count) {
                    let JL = jenislayanan(contractData.jenis_layanan_parent, contractData.jenis_layanan);
                    let jml_periode_val = contractData.periode_count;
                    if (typeof tmpArrSewa !== 'undefined' && !tmpArrSewa.includes(JL)) {
                        if (contractData.is_zerocek && !contractData.is_have_tld) {
                            jml_periode_val = jml_periode_val - 1;
                        }
                    }
                    activePeriodeIsLast = (jml_periode_val == activePeriode.periode);
                }

                if (tldTidakDigunakan.length > 0 && activePeriodeIsLast) {
                    let countTld = tldTidakDigunakan[0].count_tld;
                    let orderPeriode = [...contractData.periode];
                    orderPeriode.sort((a, b) => b.periode - a.periode);
                    let ambil = false;
                    for (const item of orderPeriode) {
                        if (item.count_tld === countTld) {
                            ambil = item;
                            break;
                        }
                    }

                    if (ambil && ambil.status != 2) {
                        // Cek pelabelan TLD selesai
                        let tldSelesai = false;
                        if (ambil.penyelia) {
                            let search = ambil.penyelia.penyelia_map.find(d => d.jobs && d.jobs.name ==
                                'Pelabelan TLD');
                            if (search?.status == 2) {
                                tldSelesai = true;
                            }
                        }

                        let htmlBtnTld = '';
                        if (tldSelesai) {
                            htmlBtnTld =
                                `<a class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-xs" href="${base_url}/staff/pengiriman/pengembalian/${contractData.kontrak_hash}"><i class="bi bi-send-fill me-1"></i> Kirim TLD</a>`;
                        }

                        // set tanggal
                        let startDate = new Date(ambil.end_date);
                        startDate.setDate(1);
                        startDate.setMonth(startDate.getMonth() + 4);

                        let endDate = new Date(startDate);
                        endDate.setMonth(endDate.getMonth() + 3);
                        endDate.setDate(0);

                        if (contractData.periode_next && contractData.periode_next.length > 0) {
                            startDate = new Date(contractData.periode_next[0].start_date);
                            endDate = new Date(contractData.periode_next[0].end_date);
                        }

                        htmlPengembalian = `
                            <div class="border-top mt-3 pt-3 d-flex justify-content-between align-items-center flex-wrap gap-2 bg-light p-3 rounded-3 mt-2">
                                <div>
                                    <span class="fw-semibold text-dark small"><i class="bi bi-arrow-return-left text-danger me-1"></i>Pengembalian TLD</span>
                                    <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">
                                        Periode: <b>${dateFormat(startDate, 4)} - ${dateFormat(endDate, 4)}</b>
                                    </small>
                                    <div class="mt-2">
                                        <span class="badge bg-secondary-subtle text-secondary-emphasis fw-normal rounded-pill">
                                            <i class="bi bi-file-binary me-1"></i>TLD - Belum Dikirim
                                        </span>
                                    </div>
                                </div>
                                <div class="align-self-center">
                                    ${htmlBtnTld}
                                </div>
                            </div>
                        `;
                        $(`#htmlPengembalianSearch-${contractData.kontrak_hash}`).html(htmlPengembalian);
                    }
                }
            });
        </script>
    @endforeach
@endif
