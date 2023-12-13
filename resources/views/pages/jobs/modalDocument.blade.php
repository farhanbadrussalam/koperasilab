<div class="modal fade" id="show-kip-lhu">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Document KIP / LHU</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-primary active" id="lhu-tab" data-bs-toggle="tab"
                        data-bs-target="#lhu-tab-pane" type="button" role="tab"
                        aria-controls="lhu-tab-pane" aria-selected="true">LHU</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-primary" id="kip-tab" data-bs-toggle="tab"
                            data-bs-target="#kip-tab-pane" type="button" role="tab"
                            aria-controls="kip-tab-pane" aria-selected="true">KIP</button>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active pt-3" id="lhu-tab-pane" role="tabpanel"
                        aria-labelledby="lhu-tab" tabindex="0">
                        <div id="doc-lhu-lampiran"></div>
                        <div class="d-flex justify-content-between m-2 mt-4">
                            <input type="hidden" id="idLhu">
                            <div class="row w-50">
                                <div class="wrapper">
                                    <img src="#" width="200" height="114" class="rounded border p-0" alt="ttd penyelia lab" id="ttd_penyelialab">
                                    <p class="text-center mt-2">Penyelia LAB</p>
                                </div>
                            </div>
                            <div class="row w-50">
                                <div class="wrapper">
                                    <img src="#" width="200" height="114" class="rounded border p-0" alt="ttd manager" id="ttd_manager">
                                    <p class="text-center mt-2">Manager</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade pt-3" id="kip-tab-pane" role="tabpanel"
                        aria-labelledby="kip-tab" tabindex="0">
                        <div class="invoice p-2 rounded">
                            <div class="row">
                                <div class="col-6">
                                    <div class="fw-bolder">No kontrak</div>
                                    <div><span id="txtNoKontrakModal"></span></div>
                                </div>
                                <div class="col-6">
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
                            <table class="table table-borderless w-100" id="rincian-table-kip">
                            </table>
                        </div>
                        <div class="d-flex justify-content-center m-2">
                            <input type="hidden" id="idKip">
                            <div class="row">
                                <div class="wrapper">
                                    <img src="#" width="200" height="114" class="rounded border p-0" alt="ttd manager invoice" id="ttd_manager_invoice">
                                    <p class="text-center mt-2">Manager</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-success" onclick="sendDocumen()" id="sendDocumen">Kirim document <i class="bi bi-send"></i></button>
            </div>
        </div>
    </div>
</div>
@push('scripts')
    <script>
        function detailkiplhu(id) {
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
                const dataKip = data.tbl_kip;
                const dataLhu = data.tbl_lhu;

                // KIP
                $('#txtNoKontrakModal').html(data.no_kontrak);
                $('#txtNamaLayananModal').html(data.layananjasa.nama_layanan);
                $('#txtNamaPelangganModal').html(data.user.name);
                $('#txtAlamatModal').html(data.user.email);

                let contentRincian = '';
                // items
                contentRincian += `
                    <tr>
                        <td>${data.jenis_layanan} x ${data.jumlah}</td>
                        <td>${formatRupiah(dataKip.harga)}</td>
                    </tr>
                `;
                // Pajak
                contentRincian += `
                    <tr>
                        <td>PPN 11%</td>
                        <td>${formatRupiah(dataKip.pajak)}</td>
                    </tr>
                `;
                // Total
                total = dataKip.harga + dataKip.pajak;
                contentRincian += `
                    <tr>
                        <th class="w-100">Jumlah</th>
                        <th>${formatRupiah(total)}</th>
                    </tr>
                `;

                $('#ttd_manager_invoice').attr('src', dataKip.ttd_1);
                $('#rincian-table-kip').html(contentRincian);

                // LHU
                let html_lhu = `
                    <div class="mt-2 d-flex align-items-center justify-content-between px-3 mx-1 shadow-sm cursoron document border bg-white">
                        <div class="d-flex align-items-center w-100">
                            <div>
                                <img class="my-3" src="{{ asset('icons') }}/${iconDocument(dataLhu.media.file_type)}" alt=""
                                    style="width: 24px; height: 24px;">
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <div class="d-flex flex-column">
                                    <a class="caption text-main" href="{{ asset('storage') }}/${dataLhu.media.file_path}/${dataLhu.media.file_hash}" target="_blank">${dataLhu.media.file_ori}</a>
                                    <span class="text-submain caption text-secondary">${dateFormat(dataLhu.media.created_at, 1)}</span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <small class="text-submain caption" style="margin-top: -3px;">${formatBytes(dataLhu.media.file_size)}</small>
                            </div>
                        </div>
                    </div>
                `;
                $('#ttd_penyelialab').attr('src', dataLhu.ttd_1);
                $('#ttd_manager').attr('src', dataLhu.ttd_2);
                $('#doc-lhu-lampiran').html(html_lhu);
                $('#idLhu').val(dataLhu.lhu_hash);
                $('#idKip').val(dataKip.kip_hash);

                $('#show-kip-lhu').modal('show');
            })
        }
        function sendDocumen(){
            Swal.fire({
                title: `Are you sure ?`,
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
                    formData.append('idLhu', $('#idLhu').val());
                    formData.append('idKip', $('#idKip').val());

                    $.ajax({
                        url: "{{ url('api/lhu/sendToPelanggan') }}",
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
                        reloadTable(2);
                        $('#show-kip-lhu').modal('hide');
                    })
                }
            })
        }
    </script>
@endpush
