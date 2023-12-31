<div class="modal fade" id="moda-invoice">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Invoice</h4>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="invoice p-2 rounded">
                    <div class="row">
                        <div class="col-12 mb-2">
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
                {{-- Status --}}
                <input type="hidden" name="idPermohonanPembayaran" id="idPermohonanPembayaran">
            </div>
            <div class="modal-footer" id="actionModalBukti">
                <button class="btn btn-outline-danger" role="button" onclick="tolakBukti()">Tolak</button>
                <button class="btn btn-outline-primary" role="button" onclick="setujuBukti()">Setuju</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="noteModalPembayaran" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title text-center w-100">Tolak</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    {{-- note --}}
                    <div class="mb-2">
                        <label for="inputNotePembayaran" class="form-label">Note <span
                                class="fw-bold fs-14 text-danger">*</span></label>
                        <textarea name="note" id="inputNotePembayaran" cols="30" rows="3" class="form-control"
                            placeholder="Masukan note"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger" role="button" onclick="sendNote(2)">Batal</button>
                <button class="btn btn-primary" role="button" onclick="sendNote(1)">Kirim</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="buatJadwalModal" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title text-center w-100">Buat jadwal</h4>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="idPermohonanJadwal" id="idPermohonanJadwal">
                <div class="row">
                    <div class="col-4 fw-bolder">Tanggal mulai</div>
                    <div class="col-8">: <span id="txtTglStart"></span></div>
                </div>
                <div class="row">
                    <div class="col-4 fw-bolder">Tanggal selesai</div>
                    <div class="col-8">: <span id="txtTglEnd"></span></div>
                </div>
                <div class="row">
                    <div class="col-4 fw-bolder">Estimasi waktu</div>
                    <div class="col-8">: <span id="txtEstimasi"></span></div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" role="button" onclick="sendJadwal()">Kirim</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modal-kwitansi">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Kwitansi</h4>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <h2>Signature</h2>
                <div id="doc-lampiran"></div>
                <div class="d-flex justify-content-between m-2 mt-4">
                    <input type="hidden" id="idKip">
                    <div class="row w-100">
                        <div class="wrapper">
                            <div class="wrapper" id="content-ttd">
                                <button class="btn btn-danger btn-sm position-absolute ms-1 mt-1" id="signature-clear"><i class="bi bi-trash"></i></button>
                                <canvas id="signature-keuangan" class="signature-pad border border-success-subtle rounded border-1" width=300 height=210></canvas>
                                <p class="text-center">Keuangan</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" id="actionModalLhu">
                <button class="btn btn-outline-primary" role="button" id="sendKwitansi" >Create</button>
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
            formData.append('idPermohonan', idPermohonan);
            formData.append('pajak', pajak);
            formData.append('harga', harga);

            $.ajax({
                url: "{{ url('sendKIP') }}",
                method: "POST",
                processData: false,
                contentType: false,
                headers: {
                    'Authorization': `Bearer {{ $token }}`
                },
                data: formData
            }).done(result => {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: result.message
                });
                dt_keuangan?.ajax.reload();
                $('#moda-invoice').modal('hide');
            })
        }

        function tolakBukti(){
            $('#modal-bukti').modal('hide');
            $('#noteModalPembayaran').modal('show');
        }
        function setujuBukti(){
            Swal.fire({
                title: 'Are you sure?',
                icon: false,
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
                customClass: {
                    confirmButton: 'btn btn-outline-success mx-1',
                    cancelButton: 'btn btn-outline-danger mx-1'
                },
                buttonsStyling: false,
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('idPermohonan', $('#idPermohonanPembayaran').val());

                    $.ajax({
                        url: "{{ url('setujuBuktiPembayaran') }}",
                        method: "POST",
                        processData: false,
                        contentType: false,
                        headers: {
                            'Authorization': `Bearer {{ $token }}`
                        },
                        data: formData
                    }).done(result => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: result.message
                        });
                        dt_keuangan?.ajax.reload();
                        $('#modal-bukti').modal('hide');
                    })
                }
            })
        }

        function sendNote(a){
            if(a == 2){
                $('#modal-bukti').modal('show');
                $('#noteModalPembayaran').modal('hide');
            }else{
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('idPermohonan', $('#idPermohonanPembayaran').val());
                formData.append('note', $('#inputNotePembayaran').val());

                $.ajax({
                    url: "{{ url('tolakBuktiPembayaran') }}",
                    method: "POST",
                    processData: false,
                    contentType: false,
                    headers: {
                        'Authorization': `Bearer {{ $token }}`
                    },
                    data: formData
                }).done(result => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: result.message
                    });
                    dt_keuangan?.ajax.reload();
                    $('#modal-bukti').modal('hide');
                    $('#noteModalPembayaran').modal('hide');
                })
            }
        }

        function sendJadwal(){
            const idPermohonan = $('#idPermohonanJadwal').val();

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('idPermohonan', idPermohonan);
            $.ajax({
                url: "{{ url('api/permohonan/createJadwalPermohonan') }}",
                method: "POST",
                processData: false,
                contentType: false,
                headers: {
                    'Authorization': `Bearer {{ $token }}`
                },
                data: formData
            }).done(result => {
                const data = result.data;
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: data.message
                });
                dt_processing?.ajax.reload();
                $('#buatJadwalModal').modal('hide');
            });
        }

        function btnDetailPayment(id){
            $.ajax({
                url: "{{ url('api/permohonan/show') }}/" + id,
                method: 'GET',
                dataType: 'json',
                processing: true,
                serverSide: true,
                headers: {
                    'Authorization': `Bearer {{ $token }}`,
                    'Content-Type': 'application/json'
                }
            }).done(result => {
                const data = result.data;
                $('#txtNoKontrakModal').html(data.no_kontrak);
                $('#txtNamaLayananModal').html(data.layananjasa.nama_layanan);
                $('#txtNamaPelangganModal').html(data.user.name);
                $('#txtAlamatModal').html(data.user.email);

                // Rincian
                let contentRincian = '';
                let tarif = data.tarif;
                let total = 0;

                // items
                let totalItems = tarif * data.jumlah;
                contentRincian += `
                    <tr>
                        <td>${data.jenis_layanan} x ${data.jumlah}</td>
                        <td>${formatRupiah(totalItems)}</td>
                    </tr>
                `;

                // Pajak
                let totalPajak = totalItems * (11/100);
                contentRincian += `
                    <tr>
                        <td>PPN 11%</td>
                        <td>${formatRupiah(totalPajak)}</td>
                    </tr>
                `;
                // Total
                total = totalItems + totalPajak;
                contentRincian += `
                    <tr>
                        <th class="w-100">Jumlah</th>
                        <th>${formatRupiah(total)}</th>
                    </tr>
                `;

                $('#inputNoKontrak').val(data.no_kontrak);
                $('#inputPajak').val(totalPajak);
                $('#inputHarga').val(totalItems);

                $('#rincian-table').html(contentRincian);

                $('#actionModalInvoice').hide();

                $('#contentBuktiPembayaran').show();
                $('#imgBuktiPembayaran').attr('src', `{{ asset('storage') }}/${data.tbl_kip.bukti.file_path}/${data.tbl_kip.bukti.file_hash}`);

                $('#moda-invoice').modal('show');
            })
        }

        function createKwitansi(id){
            $('#idKip').val(id);
            $('#modal-kwitansi').modal('show');
        }

   </script>
@endpush
