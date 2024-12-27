$(function () {
    let htmlAlamat = '<option value="">Pilih alamat</option>';
    for (const [i,value] of dataKontrak.pelanggan.perusahaan.alamat.entries()) {
        htmlAlamat += `<option value='${i}'>Alamat ${value.jenis}</option>`;
    }

    $('#selectAlamat').html(htmlAlamat);

    $('#selectAlamat').on('change', obj => {
        if(dataKontrak){
            const perusahaan = dataKontrak.pelanggan.perusahaan;

            if(perusahaan.alamat[obj.target.value]){
                $('#txt_alamat').val(perusahaan.alamat[obj.target.value].alamat + ", "+ perusahaan.alamat[obj.target.value].kode_pos);
            }else{
                $('#txt_alamat').val('');                
            }
        }
    });

    loadTld();
    loadPengguna();

    $('#flexCheckTldAll').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('input[name="flexCheckTld"]').prop('checked', isChecked);
    });
    $('#flexCheckPenggunaAll').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('input[name="flexCheckPengguna"]').prop('checked', isChecked);
    });
})

function loadTld() {
    let tld = dataKontrak.list_tld;

    let html = '';
    for (const [i, value] of tld.entries()) {
        html += `
            <li class="list-group-item px-0">
                <input class="form-check-input form-check-lg" type="checkbox" value="${value}" id="flexCheckTld${i}" name="flexCheckTld" checked>
                <label class="form-check-label" for="flexCheckTld${i}">${value}</label>
            </li>
        `;
    }

    $('#listTld').html(html);
}

function loadPengguna(){
    let pengguna = dataKontrak.pengguna;

    let html = '';
    for (const [i, value] of pengguna.entries()) {
        let txtRadiasi = '';
        value.radiasi?.map(d => txtRadiasi += `<span class="badge rounded-pill text-bg-secondary me-1 mb-1">${d.nama_radiasi}</span>`);

        html += `
            <li class="list-group-item">
                <div class="row align-items-center">
                    <div class="col-md-7 lh-sm d-flex align-items-center p-0">
                        <div class="me-2">
                            <input class="form-check-input form-check-lg" type="checkbox" value="" id="flexCheckPengguna${i}" name="flexCheckPengguna" checked>
                        </div>
                        <span class="col-form-label me-2">${i + 1}</span>
                        <div class="mx-2 d-flex flex-column gap-1">
                            <div>${value.nama}</div>
                            <small class="text-body-secondary fw-light">${value.posisi}</small>
                            <div class="d-flex flex-wrap">
                                ${txtRadiasi}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 ms-auto">
                        ${[1,2].includes(value.status) ? '<span class="badge text-bg-success">Active</span>' : '<span class="badge text-bg-danger">Inactive</span>'}
                    </div>
                    <div class="col-md-2 text-end">
                        <button class="btn btn-sm btn-outline-secondary"  data-path="${value.media.file_path}" data-file="${value.media.file_hash}" onclick="showPreviewKtp(this)" title="Show ktp"><i class="bi bi-file-person-fill"></i></button>
                    </div>
                </div>
            </li>
        `;
    }

    $('#listPengguna').html(html);
}

function buatPermohonan(obj){
    let jenisLayanan = dataJenisLayanan.jenis_layanan_hash;
    let jenisLayananParent = dataJenisLayanan.parent_hash;
    let idKontrak = dataKontrak.kontrak_hash;
    let periode = dataPeriodeNow;
    let alamatIndex = $('#selectAlamat').val();

    let checkedTldValues = [];
    $('input[name="flexCheckTld"]:checked').each(function() {
        checkedTldValues.push($(this).val());
    });

    // let checkedPenggunaValues = [];
    // $('input[name="flexCheckPengguna"]:checked').each(function() {
    //     checkedPenggunaValues.push($(this).val());
    // });

    if(!alamatIndex){
        Swal.fire({
            icon: 'warning',
            text: 'Alamat belum dipilih. Silakan pilih alamat terlebih dahulu.'
        });
        return;
    }

    Swal.fire({
        title: 'Buat Permohonan?',
        text: "Apakah Anda yakin ingin membuat permohonan ini?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',  // Green
        cancelButtonColor: '#d33',     // Red
        confirmButtonText: 'Ya, Buat!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {

            let alamatData = dataKontrak.pelanggan.perusahaan.alamat[alamatIndex]; // Get address data

            const params = new FormData();
            params.append('jenisLayanan2', jenisLayanan);
            params.append('jenisLayanan1', jenisLayananParent);
            params.append('idKontrak', idKontrak);
            params.append('periode', periode?.periode);
            params.append('alamat', alamatData.alamat_hash); // Send the address hash
            params.append('dataTld', JSON.stringify(checkedTldValues));
            params.append('createBy', userActive.user_hash);
            params.append('tipeKontrak', 'kontrak lama');

            // params.append('dataPengguna', JSON.stringify(checkedPenggunaValues));
            params.append('status', 1);

            spinner('show', $(obj)); // Show the spinner on the clicked button

            ajaxPost('api/v1/permohonan/tambahPengajuan', params, result => {
                spinner('hide', $(obj)); // Hide the spinner *after* the AJAX call completes

                // Show success message and handle any further actions (like redirecting):
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Permohonan berhasil dibuat.',
                    timer: 1500, // Adjust as needed
                    showConfirmButton: false
                }).then(() => {
                    // e.g., redirect to a different page
                    window.location.href = base_url+"/permohonan/pengajuan";
                });


            }, error => {
                spinner('hide', $(obj));  // Important: hide the spinner on error too!
            });
        } // End of if(result.isConfirmed)
    });
    
}