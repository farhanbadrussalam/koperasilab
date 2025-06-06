const listDocumenLHU = [];
let signaturePad = false;
let periodeJs = false;
let jenisLayanan = false;
let checkedTldValues = [];
let listTldKontrol = [];
let uploadDocLhu = false;
let modalDoc = false;
let inventoryTld = false;
let tmpArrTld = [];

$(function () {
    inventoryTld = new Inventory_tld({preview: true});
    inventoryTld.on('inventory.selected', (e) => {
        const detail = e.detail;

        $(`#tldNoSeri_${detail.selected}`).val(detail.data_tld.no_seri_tld);

        // reset tmpArrTld
        let index = tmpArrTld.findIndex(d => d.id == detail.selected);

        if(index > -1){
            tmpArrTld[index].tld = detail.data_tld.tld_hash;
        }
    })
    const arrPeriode = dataPermohonan.periode_pemakaian;
    jenisLayanan = dataPermohonan.jenis_layanan;

    let txtPeriode = '';
    if(!dataPermohonan.periode_pemakaian){
        txtPeriode = 'Periode ' + dataPermohonan.periode;
    } else {
        txtPeriode = arrPeriode.length + ' Periode';
    }
    $('#periode-pemakaian').val(txtPeriode);

    const conten_2 = document.getElementById("content-ttd-2");
    signaturePad = signature(conten_2, {
        text: 'Front desk'
    });
    loadTld();
    $('#btn-tandaterima').on('click', () => {
        if(tandaterima){
            loadPertanyaan();
        }
        $('#modal-tandaterima').modal('show');
    })

    if(arrPeriode){
        periodeJs = new Periode(arrPeriode, {
            preview: false,
            max: arrPeriode.length
        });

        $('#btn-periode').on('click', () => {
            periodeJs.show();
        });

        periodeJs.on('periode.simpan', () => {
            const dataPeriode = periodeJs.getData();
            const params = new FormData();
            params.append('idPermohonan', dataPermohonan.permohonan_hash);
            params.append('periodePemakaian', JSON.stringify(dataPeriode));

            ajaxPost(`api/v1/permohonan/tambahPengajuan`, params, result => {
                Swal.fire({
                    icon: 'success',
                    text: 'Update periode successfully',
                    timer: 1200,
                    timerProgressBar: true,
                    showConfirmButton: false
                });
            });
        });
    }

    $('#btnSelectAllTld').on('click', function() {
        const isChecked = $('#selectAllTld').is(':checked');
        $('#selectAllTld').prop('checked', !isChecked);
        $('input[name="selectTld"]').prop('checked', !isChecked);
    });

    uploadDocLhu = new UploadComponent('uploadDocLHU', {
        camera: false,
        allowedFileExtensions: ['pdf'],
        urlUpload: {
            url: `api/v1/permohonan/uploadLhuZeroCek`,
            urlDestroy: `api/v1/permohonan/destroyLhuZero`,
            idHash: dataPermohonan.permohonan_hash
        },
        multiple: false
    });

    if(dataPermohonan.file_lhu){
        uploadDocLhu.addData([dataPermohonan.file_lhu]);
    }

    modalDoc = new ModalDocument({
        title: 'Tanda Terima Pengujian',
    });

    $('#btn-show-tandaterima').on('click', () => {
        modalDoc.show(`laporan/tandaterima/${dataPermohonan.permohonan_hash}`);
    });

    $('#btn-delete-tandaterima').on('click', () => {
        ajaxDelete(`api/v1/permohonan/destroyTandaterima/${dataPermohonan.permohonan_hash}`, (result) => {
           Swal.fire({
               icon: 'success',
               text: 'Delete tandaterima successfully',
               timer: 1200,
               timerProgressBar: true,
               showConfirmButton: false
           }).then(() => {
                dataPermohonan.tandaterima = [];
                loadTandaterima();
           })
        });
    })

    loadPelanggan();
    loadTandaterima();
});

function loadPelanggan() {
    const pelanggan = dataPermohonan.pelanggan;
    const perusahaan = pelanggan.perusahaan;

    $('#nama-instansi').val(perusahaan.nama_perusahaan);
    $('#nama-pic').val(pelanggan.name);
    $('#jabatan-pic').val(pelanggan.jabatan);
    $('#email-pic').val(pelanggan.email);
    $('#telepon-pic').val(pelanggan.telepon);
    $('#npwp-pic').val(perusahaan.npwp_perusahaan);

    // Alamat
    let alamatUtama = false;
    let kodeposUtama = false;
    for (const value of perusahaan.alamat) {
        let valAlamat = value.alamat;
        let valKodepos = value.kode_pos;

        if(value.jenis == 'utama'){
            alamatUtama = value.alamat;
            kodeposUtama = value.kode_pos;
        }else{
            if(value.status){
                valAlamat = value.alamat;
                valKodepos = value.kode_pos;
            }else{
                valAlamat = alamatUtama;
                valKodepos = kodeposUtama;
            }
        }

        $(`#alamat-${value.jenis}`).val(valAlamat);
        $(`#txt-kode-pos-${value.jenis}`).val(valKodepos);
    }
}

function loadPertanyaan(){
    let html = '';
    $('#content-pertanyaan').html('');
    for (const [i, value] of tandaterima.entries()) {
        let htmlAnswer = ``;
        let btnSelectTld = ``;
        let htmlMandatory = value.mandatory ? '<span class="text-danger ml-2">*</span>' : '';

        if(value.type == 1){
            let jenisTld = '';
            let readonly = '';
            if(value.pertanyaan == 'TLD'){
                jenisTld = dataPermohonan.jenis_tld?.name ?? '';
                readonly = ' readonly';
            }
            htmlAnswer = `<textarea name="answer_${i}" id="answer_${i}" cols="30" rows="3" class="form-control" ${readonly}>${jenisTld}</textarea>`;
        }else if(value.type == 2){
            htmlAnswer = `
                <div class="my-3">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="answer_${i}" id="answer_${i}_baik" value="baik" onclick="toggleReason(${i}, false)">
                        <label class="form-check-label" for="answer_${i}_baik">Baik</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="answer_${i}" id="answer_${i}_cacat" value="cacat" onclick="toggleReason(${i}, true)">
                        <label class="form-check-label" for="answer_${i}_cacat">Cacat</label>
                    </div>
                    <div>
                        <input type="text" class="form-control w-100" id="reason_${i}" placeholder="Bila cacat, sebutkan : ....." disabled>
                    </div>
                </div>
            `;
        }else if(value.type == 3){
            htmlAnswer = `<textarea name="answer_${i}" id="answer_${i}" cols="30" rows="3" class="form-control" readonly></textarea>`;
            btnSelectTld = `<button class="btn btn-outline-primary btn-sm" type="button" onclick="selectTLDPermohonan(${i})">Pilih TLD</button>`;
        }

        html += `
            <div class="col-sm-6 mt-2">
                <label for="" class="mb-2">${value.pertanyaan+htmlMandatory} : ${btnSelectTld}</label>
                ${htmlAnswer}
            </div>
        `;
    }

    $('#content-pertanyaan').html(html);
}

function loadTandaterima(){
    const data = dataPermohonan.tandaterima;
    if(data && data.length > 0){
        $('#status_tandaterima').val('true');
        $('#tambah-tandaterima').addClass('d-none');
        $('#show-tandaterima').removeClass('d-none');
    }else{
        $('#status_tandaterima').val('false');
        $('#tambah-tandaterima').removeClass('d-none');
        $('#show-tandaterima').addClass('d-none');
    }
}

function toggleReason(index, enable) {
    $(`#reason_${index}`).prop('disabled', !enable);
}

function loadTld(){
    ajaxGet('api/v1/permohonan/loadTld', {idPermohonan: dataPermohonan.permohonan_hash}, result => {

        // filter untuk memisahkan antara tld pengguna dan tld kontrol
        let kPengguna = result.data.tldKontrak ? result.data.tldKontrak?.filter(tld => tld.pengguna) : [];
        let tldPengguna = [
            ...result.data.tldPermohonan.filter(tld => tld.pengguna),
            ...kPengguna,
        ];

        let kKontrol = result.data.tldKontrak ? result.data.tldKontrak.filter(tld => !tld.pengguna) : [];
        let tldKontrol = [
            ...result.data.tldPermohonan.filter(tld => !tld.pengguna),
            ...kKontrol,
        ];

        loadPengguna();
        loadTldKontrol(tldKontrol);
        return;
    });
}

function loadTldKontrol(tldKontrol){
    ajaxGet(`api/v1/tld/searchTldNotUsed`, {jenis: 'kontrol'}, result => {
        let html = '';
        let htmlDisabled = false;
        if(dataPermohonan.tipe_kontrak == 'kontrak lama'){
            htmlDisabled = true;
        }
        let index = 0;
        for(const iKontrol of tldKontrol){
            let tldHash = '';
            let no_seri_tld = '';
            let idHash = iKontrol.permohonan_tld_hash ? iKontrol.permohonan_tld_hash : iKontrol.kontrak_tld_hash;

            for (let idx = 0; idx < iKontrol.count; idx++) {
                if(iKontrol.tld) {
                    tldHash = iKontrol.tld[idx].tld_hash;
                    no_seri_tld = iKontrol.tld[idx].no_seri_tld;
                } else if(result.data[index]){
                    tldHash = result.data[index].tld_hash;
                    no_seri_tld = result.data[index].no_seri_tld;
                } else {
                    tldHash = '';
                    no_seri_tld = '';
                }

                tmpArrTld.push({
                    id: `${idHash}|${idx+1}`,
                    tld: tldHash
                });

                let kodeLencana = iKontrol.count > 1 ? `C${idx+1}` : `C`;

                html += `
                    <div class="col-sm-6 mt-2">
                        <label for="" class="mb-2">Kontrol ${iKontrol.divisi?.name ?? ''} ${kodeLencana}</label>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control rounded-start" value="${no_seri_tld}" id="tldNoSeri_${idHash}|${idx+1}" placeholder="Pilih No Seri" readonly>
                            ${!htmlDisabled ? `<button class="btn btn-outline-secondary" type="button" data-id="${idHash}|${idx+1}" onclick="openInventory(this, 'kontrol')"><i class="bi bi-arrow-repeat"></i> Ganti</button>` : ``}
                        </div>
                    </div>
                `;
                index++;
            }
            index++;
        }
        $('#tld-kontrol-content').html(html);
    });
}
function loadPengguna(tldPengguna){
    let params = {
        idPermohonan: dataPermohonan.permohonan_hash
    }
    $('#pengguna-placeholder').removeClass('d-none');
    $('#pengguna-table').addClass('d-none');

    ajaxGet(`api/v1/permohonan/listPengguna`, params, result => {
        let html = '';
        let htmlDisabled = false;
        if(dataPermohonan.tipe_kontrak == 'kontrak lama'){
            htmlDisabled = true;
        }

        for (const [i, value] of result.data.entries()) {
            let txtRadiasi = '';
            // RADIASI
            value.radiasi?.map(nama_radiasi => txtRadiasi += `<span class="badge rounded-pill text-bg-secondary me-1 mb-1">${nama_radiasi}</span>`);

            // TLD PENGGUNA
            let idHash = value.permohonan_tld_hash ? value.permohonan_tld_hash : value.kontrak_tld_hash;
            let tldHash = value.tld ? value.tld[0].tld_hash : value.tld_pengguna.tld_hash;
            let no_seri_tld = value.tld ? value.tld[0].no_seri_tld : value.tld_pengguna.no_seri_tld;

            tmpArrTld.push({
                id: idHash,
                tld: tldHash
            });

            html += `
                <tr>
                    <td>${i + 1}</td>
                    <td>
                        <div>${value.pengguna.name}</div>
                        <small class="text-body-secondary fw-light">${value.pengguna.divisi?.name || ''}</small>
                    </td>
                    <td>${txtRadiasi}</td>
                    <td>
                        <div class="input-group">
                            <input type="text" class="form-control rounded-start" value="${no_seri_tld}" id="tldNoSeri_${idHash}" placeholder="Pilih No Seri" readonly>
                            ${!htmlDisabled ? `<button class="btn btn-outline-secondary" type="button" data-id="${idHash}" onclick="openInventory(this, 'pengguna')"><i class="bi bi-arrow-repeat"></i> Ganti</button>` : ''}
                        </div>
                    </td>
                    <td>
                        <a class="btn btn-sm btn-outline-secondary show-popup-image" href="${base_url}/storage/${value.pengguna.media_ktp.file_path}/${value.pengguna.media_ktp.file_hash}">
                            <i class="bi bi-file-person-fill"></i>
                        </a>
                    </td>
                </tr>
            `;
        }

        if(result.data.length == 0){
            html = `
            <tr>
                <td colspan="5" class="text-center">
                <div class="d-flex flex-column align-items-center py-3">
                    <img src="${base_url}/images/no_data2_color.svg" style="width:220px" alt="">
                    <span class="fw-bold mt-3 text-muted">No Data Available</span>
                </div>
                </td>
            </tr>
            `;
        }

        $('#pengguna-list-container').html(html);

        $('#pengguna-placeholder').addClass('d-none');
        $('#pengguna-table').removeClass('d-none');
        showPopupReload();
    })

}

function verif_kelengkapan(status, obj){
    if(status == 'lengkap'){
        if(dataPermohonan.tandaterima.length == 0){
            return Swal.fire({
                icon: "warning",
                text: "Harap tambah tandaterima terlebih dahulu.",
            });
        }

        if(signaturePad.isEmpty()){
            return Swal.fire({
                icon: "warning",
                text: "Harap berikan tanda tangan terlebih dahulu.",
            });
        }

        Swal.fire({
            icon: 'warning',
            title: 'Apakah data sudah lengkap?',
            showCancelButton: true,
            confirmButtonText: 'Iya',
            cancelButtonText: 'Tidak',
            customClass: {
                confirmButton: 'btn btn-outline-success mx-1',
                cancelButton: 'btn btn-outline-danger mx-1'
            },
            buttonsStyling: false,
            reverseButtons: true
        }).then(result => {
            if(result.isConfirmed){
                let ttd = signaturePad.toDataURL();
                let formData = new FormData();
                formData.append('ttd', ttd);
                formData.append('status', status);
                formData.append('idPermohonan', dataPermohonan.permohonan_hash);
                formData.append('listTld', JSON.stringify(tmpArrTld));

                spinner('show', obj);
                ajaxPost(`api/v1/permohonan/verifikasi/cek`, formData, result => {
                    Swal.fire({
                        icon: 'success',
                        text: 'Permohonan terverifikasi',
                        timer: 1200,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = base_url+"/staff/permohonan";
                    });
                }, error => {
                    spinner('hide', obj);
                });
            }
        })
    }else if(status == 'tidak_lengkap'){
        $('#modal-verif-invalid').modal('show');
    }
}

function createInvoice(idPermohonan){
    const formData = new FormData();
    formData.append('idPermohonan', idPermohonan);
    formData.append('status', 1);
    ajaxPost(`api/v1/keuangan/action`, formData, result => {})
}

function createPenyelia(idPermohonan){
    const formData = new FormData();
    formData.append('idPermohonan', idPermohonan);
    formData.append('status', 1);
    ajaxPost(`api/v1/penyelia/action`, formData, result => {

    })
}

function simpanTandaTerimaPermohonan(obj){
    // Get all form elements within #content-pertanyaan
    const answerTandaterima = [];
    if(tandaterima){
        for (const [i, value] of tandaterima.entries()) {
            let elementAnswer = false;
            if(value.type == 1 || value.type == 3){
                elementAnswer = $(`#answer_${i}`).val();
                answerTandaterima.push({
                    id: value.pertanyaan_hash,
                    answer: elementAnswer,
                    note: ''
                });
            } else if(value.type == 2) {
                elementAnswer = $(`[name="answer_${i}"]:checked`).val();
                let note = '';
                if(elementAnswer == 'cacat'){
                    note = $(`#reason_${i}`).val();
                }
                answerTandaterima.push({
                    id: value.pertanyaan_hash,
                    answer: elementAnswer,
                    note: note
                });
            }

            if(value.mandatory && elementAnswer == ''){
                return Swal.fire({
                    icon: "warning",
                    text: `Harap lengkapi pertanyaan yang wajib diisi.`
                });
            }
        }

        let formData = new FormData();
        formData.append('tandaterima', JSON.stringify(answerTandaterima));
        formData.append('idPermohonan', dataPermohonan.permohonan_hash);
        spinner('show', $(obj));
        ajaxPost(`api/v1/permohonan/verifikasi/tambahTandaterima`, formData, result => {
            spinner('hide', $(obj));
            if(result.meta.code == 200){
                Swal.fire({
                    icon: "success",
                    text: result.data.msg,
                }).then(() => {
                    dataPermohonan.tandaterima = result.data.information;
                    $('#status_tandaterima').val('true');
                    $('#modal-tandaterima').modal('hide');

                    loadTandaterima();
                });
            }else{
                Swal.fire({
                    icon: "error",
                    text: result.data.msg,
                });
            }
            spinner('hide', $(obj));
        }, error => {
            spinner('hide', $(obj));
        });
    }
}

function return_permohonan(obj){
    let note = $('#txt_note').val();
    spinner('show', obj);

    let formData = new FormData();
    formData.append('_token', csrf);
    formData.append('status', 'tidak_lengkap');
    formData.append('note', note);
    formData.append('idPermohonan', dataPermohonan.permohonan_hash);
    ajaxPost(`api/v1/permohonan/verifikasi/cek`, formData, result => {
        Swal.fire({
            icon: 'success',
            text: 'Permohonan dikembalikan',
            timer: 1200,
            timerProgressBar: true,
            showConfirmButton: false
        }).then(() => {
            window.location.href = base_url+"/staff/permohonan";
        });
    })
}

function selectTLDPermohonan(index){
    let jsonTld = [];
    if(dataPermohonan.list_tld){
        jsonTld = dataPermohonan.list_tld;
    }else{
        let jumTld = dataPermohonan.jumlah_pengguna + dataPermohonan.jumlah_kontrol;
        for (let i = 0; i < jumTld; i++) {
            jsonTld.push('TLD '+ (i+1));
        }
    }

    if(checkedTldValues.length != 0){
        jsonTld = checkedTldValues;
    }

    let htmlList = '';
    for (const [i, tld] of jsonTld.entries()) {
        htmlList += `
            <li class="list-group-item">
                <input class="form-check-input me-1" type="checkbox" value="${tld}" data-index="${i}" name="selectTld" checked>
                <label class="form-check-label">
                    <input type="text" class="form-control form-control-sm" name="listTld[]" id="tld_${i}" placeholder="${tld}" value="${tld}" autocomplete="off">
                </label>
            </li>
        `;
    }
    $('#btnPilihTld').data('index', index);
    $('#listTldSelect').html(htmlList);
    $('#modal-select-tld').modal('show');
}

function simpanTldPermohonan(obj){
    checkedTldValues = [];
    $('input[name="selectTld"]:checked').each(function() {
        let indexTld = $(this).data('index');
        let value = $(`#tld_${indexTld}`).val();
        checkedTldValues.push(value);
    });

    let index = $(obj).data('index');

    // tambahkan ke textarea answer
    $(`#answer_${index}`).html(checkedTldValues.map(tld => tld).join(', '));

    $('#listTldSelect').html('');
    $('#modal-select-tld').modal('hide');
}

function areThereEmptyFields(formElements) {
    let isEmpty = false; // Assume no empty fields initially

    // Iterate through each form element
    formElements.each(function() {
      const element = $(this); // Get the jQuery object for the element

      // Check for empty values based on element type
      if (element.is('input[type="text"], input[type="email"], input[type="number"], textarea') && element.val().trim() === "") {
        isEmpty = true; // Found an empty field
        return false; // Exit the .each() loop early
      } else if (element.is('input[type="radio"], input[type="checkbox"]') && !element.is(':checked')) {
        // Check if at least one radio button in a group is selected
        const name = element.attr('name');
        if ($(`input[name="${name}"]:checked`).length === 0) {
          isEmpty = true;
          return false;
        }
      } else if (element.is('select') && element.val() === null) {
        isEmpty = true;
        return false;
      }
    });

    return isEmpty;
}

function templateTld(state){
    if(!state.id){
        return state.text;
    }

    let content = $(`
        <div class="d-flex justify-content-between">
            <div>${state.text}</div>
            <div>${state.status == 1 ? '<span class="badge rounded-pill text-bg-success">Digunakan</span>' : ''}</div>
        </div>
    `);
    return content;
}

function openInventory(obj, jenis){
    let id = $(obj).data('id');
    inventoryTld.show(id, tmpArrTld, jenis);
}
