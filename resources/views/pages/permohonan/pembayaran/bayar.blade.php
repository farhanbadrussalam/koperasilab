@extends('layouts.main')

@section('content')
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item px-3">
            <a href="{{ $_SERVER['HTTP_REFERER'] }}" class="icon-link text-danger"><i class="bi bi-chevron-left fs-3 fw-bolder h-100"></i> Kembali</a>
        </li>
    </ul>

    <div class="card shadow-sm m-4">
        <div class="card-body">
            <div class="row mx-2">
                <div class="col-md-6 col-12">
                    <label class="fw-bolder">No Invoice</label>
                    <div id="txtNoInvoice">{{ $keuangan->no_invoice }}</div>
                </div>
                <div class="col-md-6 col-12">
                    <label class="fw-bolder">No Kontrak</label>
                    <div id="txtNoKontrakInvoice">{{ $keuangan->permohonan->kontrak?->no_kontrak ?? '' }}</div>
                </div>
                <div class="col-md-6 col-12">
                    <label class="fw-bolder">Jenis</label>
                    <div id="txtJenisInvoice">{{ $keuangan->permohonan->jenis_layanan->name }}</div>
                </div>
                <div class="col-md-6 col-12">
                    <label class="fw-bolder">Pengguna</label>
                    <div id="txtPenggunaInvoice">{{ $keuangan->permohonan->jumlah_pengguna }}</div>
                </div>
                <div class="col-md-6 col-12">
                    <label class="fw-bolder">Tipe Kontrak</label>
                    <div id="txtTipeKontrakInvoice">{{ $keuangan->permohonan->tipe_kontrak }}</div>
                </div>
                <div class="col-md-6 col-12">
                    <label class="fw-bolder">Pelanggan</label>
                    <div id="txtPelangganInvoice">{{ $keuangan->permohonan->pelanggan->name }}</div>
                </div>
                <div class="col-md-6 col-12">
                    <label class="fw-bolder">Jenis TLD</label>
                    <div id="txtJenisTldInvoice">{{ $keuangan->permohonan->jenisTld->name }}</div>
                </div>
                <div class="col-md-6 col-12">
                    <label class="fw-bolder">Instansi</label>
                    <div id="txtInstansiInvoice">{{ $keuangan->permohonan->pelanggan->perusahaan->nama_perusahaan }}</div>
                </div>
            </div>
            <hr class="my-2">
            <div class="border rounded p-3 mt-3">
                <table class="table w-100 text-center">
                    <thead>
                        <tr>
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
            <div class="border rounded p-3 mt-3">
                <p>
                    Note : Kwitansi asli dan TLD akan kami kirimkan setelah menerima bukti pembayaran. <br>
                    (Mohon Bukti Potong PPh 23 dikirimkan kepada kami apabila memotongnya)<br>
                    Pembayaran dilakukan melalui <b>Virtual Account</b> Bank Mandiri <b>atas nama Koperasi JKRL</b> dengan nomor <b>89029220241750</b> atau transfer melalui rekening : <br>
                    <div class="container fw-semibold">
                        <div class="row">
                            <div class="col-md-2">Nama Bank</div>
                            <div class="col-auto">: Mandiri cabang Pondok Indah Jakarta Selatan</div>
                        </div>
                        <div class="row">
                            <div class="col-md-2">Nomor rekening</div>
                            <div class="col-auto">: 101-000011370-2</div>
                        </div>
                        <div class="row">
                            <div class="col-md-2">Atas nama</div>
                            <div class="col-auto">: Koperasi Jasa Keselamatan Radiasi dan Lingkungan</div>
                        </div>
                    </div>
                </p>
                <p>
                    Atas perhatian dan kerjasamanya, diucapkan terima kasih
                </p>
            </div>
            <div class="border rounded p-3 mt-3">
                <h4>Dokumen pendukung</h4>
                <div class="row">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <a target="_blank" href="{{ url('laporan/invoice/'.$keuangan->keuangan_hash) }}" class="text-decoration-none">
                                <i class="bi bi-file-earmark-fill"></i> Invoice
                            </a>
                        </li>
                        <li class="list-group-item">
                            <a href="{{ url('laporan/perjanjian/'.$keuangan->permohonan->kontrak_hash) }}" target="_blank" class="text-decoration-none">
                                <i class="bi bi-file-earmark-fill"></i> Kontrak MoU
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="row my-4">
                <div class="col-md-6">
                    <label for="" class="form-label">Upload bukti bayar<span class="text-danger ms-1">*</span></label>
                    <div id="uploadBuktiBayar"></div>
                </div>
                <div class="col-md-6">
                    <label for="" class="form-label">Upload bukti bayar PPH<span class="text-danger ms-1">*</span></label>
                    <div id="uploadBuktiBayarPph"></div>
                </div>
            </div>
            {{-- <div class="row my-4">
                <div class="col-md-12 d-flex justify-content-center">
                    <div class="wrapper" id="content-ttd-manager"></div>
                </div>
            </div> --}}
            <div class="modal-footer d-flex justify-content-center mt-3">
                <button type="button" class="btn btn-primary me-2" onclick="btnSimpan()">Kirim bukti bayar</button>
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