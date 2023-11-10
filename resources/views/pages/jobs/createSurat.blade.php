<div class="modal fade" id="create-surat">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Buat tugas</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-2 fw-bolder">Tugas</div>
                    <div class="col-8">: <span id="txtTugas">Uji kebocoran sumber radioaktif</span></div>
                </div>
                <div class="row">
                    <div class="col-2 fw-bolder">Customer</div>
                    <div class="col-8">: <span id="txtCustomer">Kadeem Bradley</span></div>
                </div>
                <div class="row">
                    <div class="col-2 fw-bolder">Jumlah</div>
                    <div class="col-8">: <span id="txtJumlah">20</span></div>
                </div>
                <div class="row">
                    <div class="col-2 fw-bolder">Tanggal</div>
                    <div class="col-8">: <span id="txtTanggal">31 January 2023 - 1 February 2023</span></div>
                </div>
                <h4 class="mt-2 border-bottom pb-2 text-end">List petugas</h4>
                <div class="row mt-3">
                    <div class="col-md-5">
                        <select name="satuan_lab" id="namaPetugas" class="form-select" required>
                            <option></option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <input type="text" name="" id="tugas" class="form-control" placeholder="Tugas">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-outline-primary mb-3" onclick="tambahPetugas()">Tambah</button>
                    </div>
                </div>
                {{-- <div class="my-2 w-100">
                    <div class="text-center h3">Belum ada petugas</div>
                </div> --}}
                <div class="d-flex flex-wrap">
                    <div class="col-md-12 mb-1">
                        <div class="d-flex align-items-center px-3 shadow-sm cursoron document border">
                            <div>
                                <img class="my-3 img-fluid img-circle" src="#" alt="" onerror="this.src=`{{ asset('assets/img/default-avatar.jpg') }}`" style="width: 3em;">
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <div class="d-flex flex-column">
                                    <div class="text-main">Nama petugas</div>
                                    <span class="text-submain text-secondary">Tugas yang diberikan</span>
                                </div>
                            </div>
                            <div class="p-1">
                                <button class="btn btn-sm btn-outline-danger" title="Hapus"><i
                                        class="bi bi-trash"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 mb-1">
                        <div class="d-flex align-items-center px-3 shadow-sm cursoron document border">
                            <div>
                                <img class="my-3 img-fluid img-circle" src="#" alt="" onerror="this.src=`{{ asset('assets/img/default-avatar.jpg') }}`" style="width: 3em;">
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <div class="d-flex flex-column">
                                    <div class="text-main">Nama petugas</div>
                                    <span class="text-submain text-secondary">Tugas yang diberikan</span>
                                </div>
                            </div>
                            <div class="p-1">
                                <button class="btn btn-sm btn-outline-danger" title="Hapus"><i
                                        class="bi bi-trash"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-success">Kirim tugas <i class="bi bi-send"></i></button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $('#namaPetugas').select2({
        theme: "bootstrap-5",
        placeholder: "Select petugas",
        dropdownParent: $('#create-surat'),
        // minimumInputLength: 3,
        placeholder: "Pilih petugas",
        ajax: {
            url: "{{ url('api/petugas/search') }}",
            dataType: "json",
            processing: true,
            serverSide: true,
            delay: 250,
            headers: {
                'Authorization': `Bearer {{ $token }}`,
                'Content-Type': 'application/json'
            },
            data: function (params) {
                let query = {
                    search: params.term
                }

                return query;
            },
            processResults: function(response, params){
                let items = [];
                for (const data of response.data) {
                    items.push({
                        'id' : data.petugas_hash,
                        'text' : data.petugas.name
                    });
                }
                return {
                    results: items
                }
            }
        }
    });
    function tambahPetugas(){
        let petugas = $('#namaPetugas').val();
        let tugas = $('#tugas').val();

        $.ajax({
            method: 'GET',
            url: "{{ url('/api/petugas/getPetugas') }}",
            dataType: 'json',
            processData: true,
            headers: {
                'Authorization': `Bearer {{ $token }}`,
                'Content-Type': 'application/json'
            },
            data: {
                idPetugas: petugas
            }
        }).done(function(result) {
            if (result.data) {
                console.log(result.data);
                console.log(tugas);
            }
        }).fail(function(message) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: message.responseJSON.message
            });
            console.error(message.responseJSON.message);
        });

    }
</script>
@endpush
