<div class="modal fade" id="create-surat">
    <div class="modal-dialog">
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
                <hr>
                <input type="hidden" name="" id="noKontrak">
                {{-- Upload Surat --}}
                <div class="mb-2">
                    <label for="uploadSuratTugas" class="form-label"><span>Lampirkan surat tugas</span><span
                            class="fw-bold fs-14 text-danger">*</span></label>
                    <div class="card mb-0" style="height: 100px;">
                        <input type="file" name="uploadSuratTugas" id="uploadSuratTugas" class="form-control dropify">
                    </div>
                    <span class="mb-3 text-muted" style="font-size: 12px;">Allowed file types: pdf,doc,docx.
                        Recommend size under 5MB.</span>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-success" onclick="sendSuratTugas()">Kirim tugas <i class="bi bi-send"></i></button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    setDropify('init', '#uploadSuratTugas', {
        allowedFileExtentions: ['pdf', 'doc', 'docx'],
        maxFileSize: '5M'
    });

    function sendSuratTugas() {
        let lampiran = $('#uploadSuratTugas')[0].files[0];
        let no_kontrak = $('#noKontrak').val();

        if(lampiran){
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('file', lampiran);
            formData.append('no_kontrak', no_kontrak);

            $.ajax({
                method: 'POST',
                url: "{{ url('/api/permohonan/sendSuratTugas') }}",
                dataType: 'json',
                processData: false,
                contentType: false,
                headers: {
                    'Authorization': `Bearer {{ $token }}`
                },
                data: formData
            }).done(function(result) {
                if(result.meta?.code == 200){
                    Swal.fire({
                        icon: "success",
                        text: result.data.message
                    });

                    dt_layanan?.ajax.reload();
                    $('#create-surat').modal('hide');
                }
            })
        }else{
            Swal.fire({
                icon: "error",
                text: "Lampiran harap diisi"
            });
        }
    }
    // $('#namaPetugas').select2({
    //     theme: "bootstrap-5",
    //     placeholder: "Select petugas",
    //     dropdownParent: $('#create-surat'),
    //     // minimumInputLength: 3,
    //     placeholder: "Pilih petugas",
    //     ajax: {
    //         url: "{{ url('api/petugas/search') }}",
    //         dataType: "json",
    //         processing: true,
    //         serverSide: true,
    //         delay: 250,
    //         headers: {
    //             'Authorization': `Bearer {{ $token }}`,
    //             'Content-Type': 'application/json'
    //         },
    //         data: function (params) {
    //             let query = {
    //                 search: params.term,
    //                 satuankerja_id : "{{Auth::user()->satuankerja_id}}"
    //             }

    //             return query;
    //         },
    //         processResults: function(response, params){
    //             let items = [];
    //             for (const data of response.data) {
    //                 items.push({
    //                     'id' : data.petugas_hash,
    //                     'text' : data.petugas.name
    //                 });
    //             }
    //             return {
    //                 results: items
    //             }
    //         }
    //     }
    // });
    // function tambahPetugas(){
    //     let petugas = $('#namaPetugas').val();
    //     let tugas = $('#tugas').val();

    //     $.ajax({
    //         method: 'GET',
    //         url: "{{ url('/api/petugas/getPetugas') }}",
    //         dataType: 'json',
    //         processData: true,
    //         headers: {
    //             'Authorization': `Bearer {{ $token }}`,
    //             'Content-Type': 'application/json'
    //         },
    //         data: {
    //             idPetugas: petugas
    //         }
    //     }).done(function(result) {
    //         if (result.data) {
    //             console.log(result.data);
    //             console.log(tugas);
    //         }
    //     }).fail(function(message) {
    //         Swal.fire({
    //             icon: 'error',
    //             title: 'Oops...',
    //             text: message.responseJSON.message
    //         });
    //         console.error(message.responseJSON.message);
    //     });

    // }
</script>
@endpush
