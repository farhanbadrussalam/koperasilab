@extends('layouts.main')

@section('content')
    @php
        $document_kontrak = $keuangan->permohonan->kontrak->document_kontrak->first();
    @endphp
    <div class="content-wrapper">
        <div class="container-fluid">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="mb-1 fw-bold text-dark">Detail Tagihan & Pembayaran</h4>
                    <p class="text-muted small mb-0">Invoice #{{ $keuangan->no_invoice }}</p>
                </div>
                <a href="{{ $_SERVER['HTTP_REFERER'] }}" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="bi bi-arrow-left me-2"></i>Kembali
                </a>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <h6 class="text-uppercase text-muted small fw-bold mb-3 tracking-wide">Detail Invoice & Kontrak
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded-3 border h-100">
                                        <small class="text-muted d-block mb-1">No. Kontrak</small>
                                        <span
                                            class="fw-bold text-primary font-monospace">{{ $keuangan->permohonan->kontrak?->no_kontrak ?? '' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded-3 border h-100">
                                        <small class="text-muted d-block mb-1">Pelanggan / Instansi</small>
                                        <span
                                            class="fw-bold text-dark d-block">{{ $keuangan->permohonan->pelanggan->name }}</span>
                                        <small
                                            class="text-muted">{{ $keuangan->permohonan->pelanggan->perusahaan->nama_perusahaan }}</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded-3 border h-100">
                                        <small class="text-muted d-block mb-1">Jenis Layanan</small>
                                        <span
                                            class="badge bg-info-subtle text-info border border-info-subtle">{{ $keuangan->permohonan->jenis_layanan_parent->name }}-{{ $keuangan->permohonan->jenis_layanan->name }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0 rounded-top-4">
                            <h6 class="fw-bold text-dark mb-0">Rincian Perhitungan Biaya</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="table-responsive">
                                <table class="table align-middle w-100">
                                    <thead class="bg-light border-bottom">
                                        <tr class="text-muted small text-uppercase">
                                            <th class="text-start" width="40%">Rincian</th>
                                            <th>Harga</th>
                                            <th>Qty</th>
                                            <th>Periode (Bulan)</th>
                                            <th>Jumlah</th>
                                        </tr>
                                    </thead>
                                    <tbody id="deskripsiInvoice">

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold text-dark mb-4">KONFIRMASI PEMBAYARAN</h6>

                            <div class="mb-4" id="divBuktiUtama">
                                <label class="small fw-bold text-muted mb-2 text-uppercase">Bukti Bayar Utama *</label>
                                <div class="" id="uploadBuktiBayar">

                                </div>
                            </div>

                            <div class="mb-4" id="divBuktiPph" style="display: none;">
                                <label class="small fw-bold text-muted mb-2 text-uppercase">Bukti Bayar PPH</label>
                                <div class="" id="uploadBuktiBayarPph">

                                </div>
                            </div>

                            <div class="d-grid">
                                <button class="btn btn-primary py-3 fw-bold rounded-3 shadow" onclick="btnSimpan(this)">
                                    <i class="bi bi-send-fill me-2"></i>Kirim Konfirmasi
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="sticky-sidebar">
                        <div class="card border-0 shadow-sm rounded-4 mb-3">
                            <div class="card-body p-4">
                                <h6 class="text-uppercase text-muted small fw-bold mb-3 tracking-wide">Dokumen Pendukung
                                </h6>
                                <div class="d-flex gap-3 flex-column">
                                    <button data-url="{{ 'laporan/invoice/' . $keuangan->keuangan_hash }}"
                                        data-title="Invoice Penagihan" onclick="openModalDoc(this)"
                                        class="btn btn-light border rounded-3 p-3 py-2 text-start flex-fill">
                                        <i class="bi bi-file-earmark-pdf text-danger fs-4 mb-2 d-block"></i>
                                        <span class="fw-bold d-block small">Invoice Penagihan</span>
                                        <small class="text-muted">PDF</small>
                                    </button>
                                    @if ($document_kontrak)
                                        <button
                                            data-url="{{ 'laporan/' . $document_kontrak->jenis . '/' . $keuangan->permohonan->kontrak_hash }}"
                                            data-title="Kontrak MoU" onclick="openModalDoc(this)"
                                            class="btn btn-light border rounded-3 p-3 py-2 text-start flex-fill">
                                            <i class="bi bi-file-earmark-pdf text-danger fs-4 mb-2 d-block"></i>
                                            <span class="fw-bold d-block small">Kontrak MoU</span>
                                            <small class="text-muted">PDF</small>
                                        </button>
                                    @endif
                                    @if ($keuangan->media->isNotEmpty())
                                        @foreach ($keuangan->media as $media)
                                            <button
                                                data-url="{{ 'storage/' . $media->file_path . '/' . $media->file_hash }}"
                                                data-title="Faktur Pajak" onclick="openModalDoc(this)"
                                                class="btn btn-light border rounded-3 p-3 py-2 text-start flex-fill">
                                                <i class="bi bi-file-earmark-pdf text-danger fs-4 mb-2 d-block"></i>
                                                <span class="fw-bold d-block small">Faktur Pajak</span>
                                                <small class="text-muted">PDF</small>
                                            </button>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="card border-0 shadow-sm rounded-4 mb-3">
                            <div class="card-body p-4">
                                <h6 class="fw-bold mb-3 text-uppercase small opacity-75">Instruksi Pembayaran</h6>
                                <div class="bg-white bg-opacity-10 border border-white border-opacity-25">
                                    {!! $keuangan->metode_pembayaran->content !!}
                                    <p>
                                        Atas perhatian dan kerjasamanya, diucapkan terima kasih
                                    </p>
                                </div>
                                <p class="small mb-0 opacity-75" style="line-height: 1.4;">
                                    * Note : Kwitansi asli dan TLD akan kami kirimkan setelah menerima bukti pembayaran.
                                    <br>
                                    (Mohon Bukti Potong PPh 23 dikirimkan kepada kami apabila memotongnya).
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        const dataKeuangan = @json($keuangan)
    </script>
    <script src="{{ asset('js/permohonan/bayar_invoice.js') }}"></script>
@endpush
