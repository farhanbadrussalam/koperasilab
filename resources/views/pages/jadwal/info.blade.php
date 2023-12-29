<div class="modal fade" id="infoModal">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header border-0">
          <h4 class="modal-title">List petugas</h4>
          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" id="content-petugas">

        </div>
        <div class="modal-footer">
            <input type="hidden" id="idPermohonanPetugas">
            <button class="btn btn-outline-primary" onclick="addPetugas()">Tambah petugas</button>
        </div>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
@push('scripts')
    <script>
        function showPetugas(id) {
            $.ajax({
                url: "{{ url('api/getJadwalPetugas') }}",
                method: "GET",
                dataType: 'json',
                processData: true,
                headers: {
                    'Authorization': `Bearer {{ $token }}`,
                    'Content-Type': 'application/json'
                },
                data: {
                    id: id
                }
            }).done(result => {
                $('#idPermohonanPetugas').val(id);
                let content = '';
                for (const data of result.data.petugas) {
                    let contentOtorisasi = '';
                    for (const otorisasi of data.otorisasi) {
                        contentOtorisasi += `<button class="btn btn-outline-dark btn-sm m-1" role="button">${stringSplit(otorisasi.name, 'Otorisasi-')}</button>`;
                    }
                    let listItem = '';
                    if(data.status == 1 || data.status == 9){
                        listItem += `
                            <li class="my-1 cursoron">
                                <a class="dropdown-item dropdown-item-lab subbody" onclick="changePetugas('${data.jadwalpetugas_hash}')">
                                    <i class="bi bi-arrow-repeat"></i>&nbsp;Change
                                </a>
                            </li>
                        `;
                    }

                    let btnAction = `
                        <div class="dropdown">
                            <div class="more-option d-flex align-items-center justify-content-center mx-0 mx-md-4" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </div>
                            <ul class="dropdown-menu shadow-sm px-2">
                                ${listItem}
                                <li class="my-1 cursoron">
                                    <a class="dropdown-item dropdown-item-lab subbody text-danger" onclick="deletePetugas('${data.jadwalpetugas_hash}')">
                                        <i class="bi bi-trash"></i>&nbsp;Delete
                                    </a>
                                </li>
                            </ul>
                        </div>
                    `;

                    let pj = data.petugas.user_hash == result.data.pj ? `<small class="text-danger">Penanggung jawab</small>` : '';
                    content += `
                        <div class="card m-0 mb-2">
                            <div class="card-body d-flex p-2">
                                <div class="flex-grow-1 d-flex my-auto">
                                    <div>
                                        <img src="${data.avatar}" alt="Avatar" onerror="this.src='{{ asset('assets/img/default-avatar.jpg') }}'" style="width: 3em;" class="img-circle border shadow-sm">
                                    </div>
                                    <div class="px-3 my-auto">
                                        <div class="lh-1">${data.petugas.name}</div>
                                        ${pj}
                                        <div class="lh-1">${data.petugas.email}</div>
                                    </div>
                                </div>
                                <div class="p-2 m-auto">
                                    <div class="d-flex flex-wrap justify-content-end">
                                        ${contentOtorisasi}
                                    </div>
                                </div>
                                <div class="p-2 m-auto">${statusFormat('jadwal', data.status)}</div>
                                <div class="p-2 m-auto">
                                    ${btnAction}
                                </div>
                            </div>
                        </div>
                    `;
                }

                if(result.data.petugas.length == 0){
                    content = '<div class="text-center">No petugas</div>';
                }

                $('#content-petugas').html(content);
                $('#infoModal').modal('show');
            })
        }

        function storePetugas(){
            let idPermohonan = $('#idPermohonanPetugas').val();
            let select = $('#selectPetugas').val();
            let formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('idPetugas', select);
            formData.append('idJadwal', d_jadwal.jadwal_hash);
            formData.append('idPermohonan', idPermohonan);

            $.ajax({
                method: "POST",
                url: "{{ url('api/petugas/storeJadwalPetugas') }}",
                processData: false,
                contentType: false,
                dataType: 'json',
                headers: {
                    'Authorization': `Bearer {{ $token }}`
                },
                data: formData
            }).done(result => {
                toastr.success(result.message);
                window.location.reload();
                // dt_permohonan?.ajax.reload();
                // $('#addPetugas').modal('hide');
                // $('#infoModal').modal('hide');
            });
        }

        function changePetugas(idHash){
            $.ajax({
                method: 'GET',
                url: "{{ url('api/petugas/getJadwalPetugas/'.$jadwal->jadwal_hash) }}",
                dataType: "JSON",
                processData: true,
                headers: {
                    'Authorization': `Bearer {{ $token }}`,
                    'Content-Type': 'application/json'
                }
            }).done(function(result){
                let arrResult = [];
                for (const data of pegawai) {
                    let find = result.data.find(f => f.petugas.user_hash == data.petugas.user_hash);

                    if(!find){
                        arrResult.push({
                            id: data.petugas.user_hash,
                            text: data.petugas.name,
                            title: stringSplit(data.otorisasi[0].name, 'Otorisasi-')
                        });
                    }
                }
                $('#idJadwalPetugas').val(idHash);
                $('#selectChangePetugas').select2({
                    theme: "bootstrap-5",
                    placeholder: "Select petugas",
                    templateResult: formatSelect2Staff,
                    dropdownParent: $('#changePetugas'),
                    data: arrResult
                });

                $('#changePetugas').modal('show');
            })
        }

        function deletePetugas(id){
            deleteGlobal(() => {
                $.ajax({
                    url: "{{ url('/api/petugas/destroyJadwalPetugas') }}/"+id,
                    method: 'DELETE',
                    dataType: 'json',
                    processData: true,
                    headers: {
                        'Authorization': `Bearer {{ $token }}`,
                        'Content-Type': 'application/json'
                    }
                }).done((result) => {
                    if(result.message){
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: result.message
                        });
                        dt_permohonan?.ajax.reload();
                        $('#infoModal').modal('hide');
                    }
                }).fail(function(message) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: message.responseJSON.message
                    });
                });
            });
        }
    </script>
@endpush
