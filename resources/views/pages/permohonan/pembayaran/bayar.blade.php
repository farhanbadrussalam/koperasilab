@extends('layouts.main')

@section('content')
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item px-3">
            <a href="{{ route('permohonan.pembayaran') }}" class="icon-link text-danger"><i class="bi bi-chevron-left fs-3 fw-bolder h-100"></i> Kembali</a>
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
                    <div id="txtNoKontrakInvoice">{{ $keuangan->permohonan->no_kontrak }}</div>
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
                    <div id="txtInstansiInvoice">-</div>
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
                    Pembayaran dapat dilakukan secara Tunai atau melalui <br>
                    Virtual Account Bank Mandiri dengan No : 89029120231337
                </p>
                <p>
                    Apabila telah melakukan pembayaran, kami mohon bukti teransfer dikirim melalui e-mail ke tld@kop-jkrl.co.id dan diberi keterangan untuk pembayaran dimaksud berdasarkan invoice/kwitansi tersebut diatas. <br>
                    Atas perhatian dan kerjasamanya, diucapkan terima kasih
                </p>
            </div>
            <div class="modal-footer d-flex justify-content-center mt-3">
                <button type="button" class="btn btn-primary" onclick="">Upload bukti bayar</button>
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