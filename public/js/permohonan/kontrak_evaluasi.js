const tmpArrTld = [];
let inventoryTld = false;
let JL = '';
let htmlDisabled = false;

$(function () {
    inventoryTld = new Inventory_tld({
        preview: true,
        no_kontrak: dataKontrak.no_kontrak
    });
    inventoryTld.on('inventory.selected', (e) => {
        const detail = e.detail;
        $(`#tldNoSeri_${detail.selected}`).val(detail.data_tld.no_seri_tld);

        // reset tmpArrTld
        let index = tmpArrTld.findIndex(d => d.id == detail.selected);

        if(index > -1){
            tmpArrTld[index].tld = detail.data_tld.tld_hash;
        }
    });
    let htmlAlamat = '<option value="">Pilih alamat</option>';
    for (const [i,value] of dataKontrak.pelanggan.perusahaan.alamat.entries()) {
        if(value.status){
            htmlAlamat += `<option value='${i}'>Alamat ${value.jenis}</option>`;
        }
    }

    $('#selectAlamat').html(htmlAlamat);

    $('#selectAlamat').on('change', obj => {
        if(dataKontrak){
            const perusahaan = dataKontrak.pelanggan.perusahaan;

            if(perusahaan.alamat[obj.target.value] && perusahaan.alamat[obj.target.value].alamat){
                $('#txt_alamat').val(perusahaan.alamat[obj.target.value].alamat + ", "+ perusahaan.alamat[obj.target.value].kode_pos);
            }else{
                $('#txt_alamat').val('');
            }
        }
    });

    JL = jenislayanan(dataKontrak.jenis_layanan_parent, dataKontrak.jenis_layanan);

    if(tmpArrEvaluasi.includes(JL)){
        htmlDisabled = true;
    }

    loadTld();

    $('#checkAllTldPengguna').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('input[name="checkTldPengguna"]').prop('checked', isChecked);
    });
})

function loadTld() {
    let tldPengguna = dataKontrak.rincian_list_tld.filter(tld => tld.pengguna);
    let tldKontrol = dataKontrak.rincian_list_tld.filter(tld => !tld.pengguna);

    loadTldKontrol(tldKontrol);
    loadPengguna(tldPengguna);
}

function loadTldKontrol(tldKontrol) {
    let htmlTldKontrol = '';
    for (const [i, list] of tldKontrol.entries()) {

        for (let idx = 0; idx < list.count; idx++) {
            tmpArrTld.push({
                id: `${list.kontrak_tld_hash}_kontrol_${idx+1}`,
                tld: list.tld ? list.tld[idx].tld_hash : null
            });

            htmlTldKontrol += `
                <div class="w-50 pe-1 mb-1 input-group">
                    <div class="input-group-text">
                        <input class="form-check-input mt-0" name="checkTldKontrol" id="checkTldKontrol${i}" type="checkbox" value="${list.kontrak_tld_hash}" aria-label="Checkbox for following text input">
                    </div>
                    <input type="text" class="form-control" value="${list.tld ? list.tld[idx].no_seri_tld : ''}" id="tldNoSeri_${list.kontrak_tld_hash}_kontrol_${idx+1}" placeholder="Pilih No Seri" readonly>
                    ${ htmlDisabled ? `<button class="btn btn-outline-secondary" type="button" data-id="${list.kontrak_tld_hash}_kontrol_${idx+1}" onclick="openInventory(this, 'kontrol')"><i class="bi bi-arrow-repeat"></i> Ganti</button>` : '' }
                </div>
            `;
        }

    }

    $('#tld-kontrol-content').html(htmlTldKontrol);
}

function loadPengguna(tldPengguna){
    let htmlPengguna = '';
    for (const [i, value] of tldPengguna.entries()) {
        let txtRadiasi = '';
        value.pengguna.radiasi?.map(d => txtRadiasi += `<span class="badge rounded-pill text-bg-secondary me-1 mb-1">${d.nama_radiasi}</span>`);

        tmpArrTld.push({
            id: value.kontrak_tld_hash,
            tld: value.tld ? value.tld[0].tld_hash : null
        });

        htmlPengguna += `
            <tr>
                <td>
                    <input class="form-check-input mt-0" name="checkTldPengguna" type="checkbox" value="${value.kontrak_tld_hash}" aria-label="" id="checkTldPengguna${i}">
                </td>
                <td>${i + 1}</td>
                <td>
                    <div>${value.pengguna.name}</div>
                    <small class="text-body-secondary fw-light">${value.pengguna.divisi?.name || ''}</small>
                </td>
                <td>${txtRadiasi}</td>
                <td>
                    <div class="input-group">
                        <input type="text" class="form-control rounded-start" value="${value.tld ? value.tld[0].no_seri_tld : ''}" id="tldNoSeri_${value.kontrak_tld_hash}" placeholder="Pilih No Seri" readonly>
                        ${htmlDisabled ? `<button class="btn btn-outline-secondary" type="button" data-id="${value.kontrak_tld_hash}" onclick="openInventory(this, 'pengguna')"><i class="bi bi-arrow-repeat"></i> Ganti</button>` : ''}
                    </div>
                </td>
                <td>
                    <a class="btn btn-sm btn-outline-secondary show-popup-image" href="${base_url}/storage/${value.pengguna.media_ktp.file_path}/${value.pengguna.media_ktp.file_hash}" title="Show ktp">
                        <i class="bi bi-file-person-fill"></i>
                    </a>
                </td>
            </tr>
        `;
    }

    $('#pengguna-list-container').html(htmlPengguna);
    showPopupReload();
}

function buatPermohonan(obj){
    let jenisLayanan = dataJenisLayanan.jenis_layanan_hash;
    let jenisLayananParent = dataJenisLayanan.parent_hash;
    let idKontrak = dataKontrak.kontrak_hash;
    let periode = dataPeriodeNow;
    let alamatIndex = $('#selectAlamat').val();

    let checkTld = [];
    $('input[name="checkTldPengguna"]:checked, input[name="checkTldKontrol"]:checked').each(function() {
        checkTld.push($(this).val());
    });

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
            params.append('listTld', JSON.stringify(checkTld));
            params.append('createBy', userActive.user_hash);
            params.append('is_zerocek', 0);
            params.append('haveTld', 1);
            params.append('tipeKontrak', 'kontrak lama');
            params.append('dataTld', JSON.stringify(tmpArrTld));
            dataPermohonan ? params.append('idPermohonan', dataPermohonan.permohonan_hash) : false;

            // params.append('dataPengguna', JSON.stringify(checkedPenggunaValues));
            params.append('status', 1);

            spinner('show', $(obj)); // Show the spinner on the clicked button

            ajaxPost('api/v1/permohonan/tambahPengajuan', params, result => {
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

function openInventory(obj, jenis){
    let id = $(obj).data('id');
    inventoryTld.show(id, tmpArrTld, jenis);
}
