<div class="modal fade" id="moda-invoice">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Invoice</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="invoice p-2 rounded">
                    <div class="row">
                        <div class="col-12">
                            <div class="fw-bolder">Nama Layanan</div>
                            <div><span id="txtNamaLayananModal"></span></div>
                        </div>
                        <br>
                        <div class="col-6">
                            <div class="fw-bolder">Customer</div>
                            <div><span id="txtNamaPelangganModal"></span></div>
                        </div>
                        <div class="col-6">
                            <div class="fw-bolder">Email</div>
                            <div><span id="txtAlamatModal">-</span></div>
                        </div>
                    </div>
                    <hr>
                    <h3>Rincian: </h3>
                    <input type="hidden" id="inputIdPermohonan">
                    <input type="hidden" id="inputPajak">
                    <input type="hidden" id="inputHarga">
                    <table class="table table-borderless w-100" id="rincian-table">
                    </table>
                </div>
                <div class="invoice p-2 rounded mt-2 text-center" id="contentBuktiPembayaran" style="display: none;">
                    <h3>Bukti Pembayaran</h3>
                    <img src="#" alt="Bukti pembayaran" class="img-fluid" id="imgBuktiPembayaran">
                </div>
            </div>
            <div class="modal-footer" id="actionModalInvoice">
                <button class="btn btn-outline-primary" role="button" onclick="sendKip()">Kirim</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modal-bukti">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Bukti Pembayaran</h4>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <img src="#" alt="Bukti pembayaran" class="img-fluid" id="imgBukti">
            </div>
            <div class="modal-footer" id="actionModalInvoice">
                <button class="btn btn-outline-primary" role="button" onclick="">Cetak Kuitansi</button>
            </div>
        </div>
    </div>
</div>
@push('scripts')
    <script>
        function sendKip() {
            const idPermohonan = $('#inputIdPermohonan').val();
            const pajak = $('#inputPajak').val();
            const harga = $('#inputHarga').val();

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('id_permohonan', idPermohonan);
            formData.append('pajak', pajak);
            formData.append('harga', harga);

            ajaxPost(`sendKIP`, formData, result => {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: result.message
                });
                dt_keuangan?.ajax.reload();
                $('#moda-invoice').modal('hide');
            })
        }
    </script>
@endpush
