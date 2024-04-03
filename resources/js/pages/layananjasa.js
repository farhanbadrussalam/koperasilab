document.addEventListener('DOMContentLoaded', function () {
    // Initialisasi
    const inputBiayaLayanan = document.getElementById('contentBiayaLayanan');
    const modalLayanan = $('#modal_form');
    const modalTitle = $('#modal_title')
    const selectSatuanKerja = $('#selectSatuanKerja')
    const selectPj = $('#selectPj')
    const inputNamaLayanan = $('#inputNamaLayanan')
    const saveLayanan = $('#save-layanan')
    const btnSearch = $('#btn-search')

    const modalPemohonan = $('#modal_permohonan')
    const permohonanNamaLayanan = $('#permohonan_namalayanan')
    const permohonanJenis = $('#permohonanJenis')
    const permohonanBiaya = $('#permohonan_biaya')
    const permohonanNoBapeten = $('#permohonan_noBapeten')
    const permohonanJumlah = $('#permohonan_jumlah')
    const permohonanJenisLimbah = $('#permohonan_jenisLimbah')
    const permohonanRadioaktif = $('#permohonan_radioaktif')
    const savePermohonan = $('#save-permohonan')
    const formPermohonan = $('#formPermohonan')
    const permohonanLayananHash = $('#permohonan_layananHash')
    const contentFilePermohonan = document.getElementById('contentFilePermohonan')
    let arrBiayaLayanan = []

    let idLayanan = false
    let countBiaya = 1;
    let countFile = 1
    loadLayananjasa();

    selectPj.select2({
        theme: "bootstrap-5",
        placeholder: "Select penanggung jawab",
        templateResult: formatSelect2Staff,
        dropdownParent: modalLayanan,
    })

    // METHOD
    function loadLayananjasa(page = 1) {
        const search = $('#search').val();

        $('#skeleton-container').show();
        $('#content-container').hide();
        $('#pagination-container').hide();

        const params = {
            page: page,
            search: search,
            limit: 5
        }

        ajaxGet('api/layananjasa/list', params, result => {
            $('#content-container').html(createTable(result.data));

            $('#pagination-container').html(createPaginationHTML(result.pagination));

            $('#skeleton-container').hide();
            $('#content-container').show();
        })
    }

    function createTable(data = []) {
        let content = document.createElement('div');
        if (data.length == 0) {
            content.innerHTML = `<div class="d-flex flex-column align-items-center py-4"><img src="${base_url}/images/no_data2_color.svg" style="width:220px" alt=""><span class="fw-bold mt-3 text-muted">No Data Found</span></div>`;
        } else {
            for (const [i, item] of data.entries()) {
                const biayaLayanan = JSON.parse(item.biaya_layanan)
                // Membuat elemen div utama
                let mainDiv = document.createElement('div');
                mainDiv.className = 'row m-0 p-0 border rounded p-3 mb-1 align-items-center';

                // Membuat elemen div kolom pertama
                let col1Div = document.createElement('div');
                col1Div.className = `col-6 ${role != 'Pelanggan' ? 'col-md-6' : 'col-md-10'}`;
                col1Div.innerHTML = `${item.nama_layanan}`;
                mainDiv.appendChild(col1Div);

                // Membuat elemen div kolom kedua
                let col2Div = document.createElement('div');
                col2Div.classList.add('col-3', 'col-md-2');
                col2Div.innerHTML = `<span title="Penanggung jawab">${item.user.name}</span>`;

                role != 'Pelanggan' && mainDiv.appendChild(col2Div);

                // Membuat elemen div kolom ketiga
                let col3Div = document.createElement('div');
                col3Div.classList.add('col-3', 'col-md-2', 'text-center', 'mb-2', 'mb-md-0');
                col3Div.innerHTML = formatRupiah(biayaLayanan[0].tarif);
                // if (biayaLayanan.length == 1) {
                //     col3Div.innerHTML = formatRupiah(biayaLayanan[0].tarif);
                // } else {
                //     let button = document.createElement('button');
                //     button.classList.add('btn', 'btn-secondary', 'rounded-pill', 'btn-sm', 'text-small');
                //     button.textContent = 'Rincian biaya';
                //     col3Div.appendChild(button);
                // }
                role != 'Pelanggan' && mainDiv.appendChild(col3Div);

                // Membuat elemen div kolom keempat
                let col4Div = document.createElement('div');
                col4Div.classList.add('col-12', 'col-md-2', 'text-end');
                let editButton = document.createElement('button');
                editButton.classList.add('btn', 'btn-warning', 'btn-sm', 'me-1');
                editButton.setAttribute('data-bs-toggle', 'tooltip');
                editButton.setAttribute('data-bs-title', 'Edit');
                editButton.onclick = () => { btnUpdate(item.layanan_hash) }
                editButton.innerHTML = '<i class="bi bi-pencil-square"></i>';
                editButton.title = 'Update'
                role != 'Pelanggan' && col4Div.appendChild(editButton);

                let deleteButton = document.createElement('button');
                deleteButton.classList.add('btn', 'btn-danger', 'btn-sm', 'me-1');
                deleteButton.setAttribute('data-bs-toggle', 'tooltip');
                deleteButton.setAttribute('data-bs-title', 'Delete');
                deleteButton.onclick = () => { btnDelete(item.layanan_hash) };
                deleteButton.innerHTML = '<i class="bi bi-trash3-fill"></i>';
                deleteButton.title = "Remove"
                role != 'Pelanggan' && col4Div.appendChild(deleteButton);

                let createPermohonan = document.createElement('button')
                createPermohonan.className = 'btn btn-primary btn-sm'
                createPermohonan.innerHTML = 'Buat Permohonan'
                createPermohonan.onclick = () => { permohonan(item.layanan_hash) }
                role == 'Pelanggan' && col4Div.appendChild(createPermohonan)

                mainDiv.appendChild(col4Div);

                content.appendChild(mainDiv);

                // Sekarang `mainDiv` adalah elemen DOM yang sesuai dengan markup HTML yang diberikan

            }
            $('#pagination-container').show();
        }

        return content;
    }

    function tambahBiaya() {
        const content = document.createElement('div');
        content.className = 'd-flex mb-1';

        const desc = document.createElement('div');
        desc.className = 'input-group pe-1';

        const btnTambah = document.createElement('button');
        btnTambah.className = 'btn btn-primary';
        btnTambah.innerHTML = '<i class="bi bi-plus-lg"></i>';
        btnTambah.onclick = tambahBiaya;

        const btnRemove = document.createElement('button');
        btnRemove.className = 'btn btn-danger bi bi-dash-lg';
        btnRemove.onclick = removeBiaya;

        const inputTextDesc = document.createElement('input');
        inputTextDesc.className = 'form-control';
        inputTextDesc.name = `inputBiayaLayanan`;
        inputTextDesc.placeholder = 'Description';

        countBiaya == 1 ? desc.appendChild(btnTambah) : desc.appendChild(btnRemove);
        desc.appendChild(inputTextDesc);

        const tarif = document.createElement('div');
        tarif.className = 'input-group';
        tarif.innerHTML = `
            <span class="input-group-text" id="rupiah-text">Rp</span>
            <input type="text" class="form-control rupiah" name="tarif" id="tarif[]" placeholder="Biaya">
        `;

        content.appendChild(desc);
        content.appendChild(tarif);

        inputBiayaLayanan.appendChild(content);

        countBiaya++;
        maskReload();
    }

    function clearForm() {
        selectSatuanKerja.removeClass('border-danger').val('');
        selectPj.val(null).trigger('change');
        $('#invalid-pj').removeClass('border border-danger')
        inputNamaLayanan.removeClass('border-danger').val('');
        $('#contentBiayaLayanan').empty();
        countBiaya = 1;
        tambahBiaya();
    }

    function clearFormPermohonan(){
        permohonanJenis.removeClass('border-danger').val('')
        permohonanJenisLimbah.removeClass('border-danger').val('')
        permohonanJumlah.removeClass('border-danger').val('')
        permohonanLayananHash.val('')
        permohonanNoBapeten.val('')
        permohonanBiaya.val('')
        permohonanRadioaktif.removeClass('border-danger').val('')

        contentFilePermohonan.innerHTML = ''
        countFile = 1
    }

    function removeBiaya(obj) {
        $(obj.target).parent().parent().remove();
        countBiaya--;
    }

    function cekValidationPermohonan(){
        const cekEmpty = permohonanJenis.val() != '' && permohonanJenisLimbah.val() != '' && permohonanRadioaktif.val() != '' && permohonanJumlah.val() != ''
        if(!cekEmpty){
            permohonanJenis.val() == '' && permohonanJenis.addClass('border-danger')
            permohonanJenisLimbah.val() == '' && permohonanJenisLimbah.addClass('border-danger')
            permohonanRadioaktif.val() == '' && permohonanRadioaktif.addClass('border-danger')
            permohonanJumlah.val() == '' && permohonanJumlah.addClass('border-danger')

            return false
        }
        return cekEmpty
    }

    function cekValidation() {
        const satuankerja = selectSatuanKerja.val();
        const penanggungjawab = selectPj.val();
        const namalayanan = inputNamaLayanan.val();
        const desc_layanan = document.getElementsByName('inputBiayaLayanan');
        const tarif_layanan = document.getElementsByName('tarif');

        const list_desc = []
        const list_tarif = []
        for (const list of desc_layanan) {
            if (list.value != '') {
                list_desc.push(list.value)
            }
        }
        for (const [i, list] of tarif_layanan.entries()) {
            if (list.value != '') {
                list_tarif.push(unmask(list.value))
            }
        }

        const cekEmpty = satuankerja != '' && penanggungjawab != '' && namalayanan != '' && list_desc.length != 0 && list_tarif.length != 0;
        if (!cekEmpty) {
            satuankerja == '' && selectSatuanKerja.addClass('border-danger')
            penanggungjawab == '' && $('#invalid-pj').addClass('border border-danger')
            namalayanan == '' && inputNamaLayanan.addClass('border-danger')
            if (list_desc.length == 0 || list_tarif == 0) {
                Swal.fire({
                    icon: 'error',
                    text: 'Tolong isi rincian biaya!',
                    timer: 2000,
                    timerProgressBar: true,
                })
            }

            return false
        }

        const formData = new FormData()
        formData.append('satuankerja', satuankerja)
        formData.append('pj', penanggungjawab)
        formData.append('nama_layanan', namalayanan)
        formData.append('desc_biaya', JSON.stringify(list_desc))
        formData.append('tarif', JSON.stringify(list_tarif))

        return formData
    }

    function btnUpdate(id) {
        ajaxGet(`api/layananjasa/getLayanan/${id}`, false, result => {
            const data = result.data
            const biayaLayanan = JSON.parse(data.biaya_layanan)
            idLayanan = id
            modalTitle.html('Update Layanan')
            saveLayanan.empty().html('<i class="bi bi-floppy2-fill"></i> Update')
            modalLayanan.modal('show')

            selectSatuanKerja.val(data.satuan_kerja.satuan_hash)
            inputNamaLayanan.val(data.nama_layanan)
            getPegawai(data.satuan_kerja.satuan_hash, data.user.user_hash)

            for (const [i, biaya] of biayaLayanan.entries()) {
                if(i != 0) {
                    tambahBiaya()
                }

                document.getElementsByName('inputBiayaLayanan')[i].value = biaya.desc
                document.getElementsByName('tarif')[i].value = biaya.tarif
            }
        })

    }

    function btnDelete(id) {
        const url = `api/layananjasa/deleteLayanan/${id}`;
        ajaxDelete(url, (result) => {
            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    text: result.message,
                    timer: 1200,
                    timerProgressBar: true,
                    showConfirmButton: false
                }).then(() => {
                    loadLayananjasa()
                });
            }
        }, (error) => {
            Swal.fire({
                icon: 'error',
                text: error.message,
                timer: 1000,
                timerProgressBar: true,
            })
        })
    }

    function getPegawai(idSatuan, pj = false){
        ajaxGet(`api/petugas/getPetugas`, { idSatuan: idSatuan }, result => {
            if (result.data) {
                let html = '<option value="">-- Select --</option>';
                for (const data of result.data) {
                    html += `<option value="${data.petugas.user_hash}" title="${stringSplit(data.otorisasi[0].name, 'Otorisasi-')}" ${pj == data.petugas.user_hash ? 'selected' : ''}>${data.petugas.name}</option>`;
                }

                selectPj.html(html);
            }
        })
    }

    function permohonan(layanan_hash){
        ajaxGet(`api/layananjasa/getLayanan/${layanan_hash}`, false, result => {

            const data = result.data
            permohonanNamaLayanan.val(data.nama_layanan)
            arrBiayaLayanan = JSON.parse(data.biaya_layanan)
            let contentBiaya = '<option value="">-- Select --</option>'

            for (const biaya of arrBiayaLayanan) {
                contentBiaya += `<option value="${biaya.desc}">${biaya.desc}</option>`;
            }
            permohonanJenis.html(contentBiaya)
            permohonanLayananHash.val(layanan_hash)

            tambahFile()
            maskReload()
        })
        modalPemohonan.modal('show')
    }

    function tambahFile(){
        const mainDiv = document.createElement('div')
        mainDiv.className = 'input-group mb-2'

        const input1 = document.createElement('input')
        input1.type = 'file'
        input1.className = 'form-control'
        input1.ariaLabel = 'Upload file'
        input1.accept = '.pdf, .docx, .doc, .xls'
        input1.name = 'documents[]'
        mainDiv.appendChild(input1)

        const btnTambah = document.createElement('button')
        btnTambah.className = 'btn btn-primary bi bi-plus-lg'
        btnTambah.type = 'button'
        btnTambah.onclick = tambahFile

        const btnRemove = document.createElement('button')
        btnRemove.className = 'btn btn-danger bi bi-dash-lg'
        btnRemove.type = 'button'
        btnRemove.onclick = removeFile

        countFile == 1 ? mainDiv.appendChild(btnTambah) : mainDiv.appendChild(btnRemove)

        contentFilePermohonan.appendChild(mainDiv)
        countFile++;
    }

    function removeFile(obj) {
        $(obj.target).parent().remove();
        countFile--;
    }

    // EVENT
    formPermohonan.on('submit', obj => {
        obj.preventDefault()

        if(cekValidationPermohonan()){
            const formdata = new FormData(obj.target)
            savePermohonan.attr('disabled', true).html(`
                <span class="spinner-border spinner-border-sm" role="status"></span> Create
            `)

            ajaxPost('api/permohonan/addPermohonan', formdata, result => {
                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        text: result.message,
                        timer: 1000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(() => {
                        modalPemohonan.modal('hide')
                        savePermohonan.attr('disabled', false).html(`
                            <i class="bi bi-floppy2-fill"></i> Create
                        `)
                        clearFormPermohonan()
                    });

                } else {
                    Swal.fire({
                        icon: 'error',
                        text: 'Terjadi masalah saat menambah data',
                        timer: 2000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(() => {
                        savePermohonan.attr('disabled', false).html(`
                            <i class="bi bi-floppy2-fill"></i> Create
                        `)
                    })
                }
            }, err => {
                Swal.fire({
                    icon: 'error',
                    text: 'Server error',
                    timer: 2000,
                    timerProgressBar: true,
                    showConfirmButton: false
                }).then(() => {
                    savePermohonan.attr('disabled', false).html(`
                        <i class="bi bi-floppy2-fill"></i> Create
                    `)
                })
                console.error(err)
            })
        }
    })
    savePermohonan.on('click', obj => {
        formPermohonan.submit();
    })

    permohonanJenis.on('change', obj => {
        const biaya = arrBiayaLayanan.find(d => d.desc == obj.target.value)
        permohonanBiaya.val(biaya.tarif);
    })
    $('#search').keypress((key) => {
        if(key.which == 13){
            key.preventDefault()
            loadLayananjasa()
            key.target.blur()
        }
    })
    btnSearch.on('click', () => {
        loadLayananjasa(1)
    })

    $('#create_layanan').on('click', () => {
        modalTitle.html('Create Layanan')
        saveLayanan.empty().html('<i class="bi bi-floppy2-fill"></i> Save')
        modalLayanan.modal('show')
        idLayanan = false
    })

    permohonanJenis.on('focus', obj => {
        $(obj.target).removeClass('border-danger')
    })

    permohonanJenisLimbah.on('focus', obj => {
        $(obj.target).removeClass('border-danger')
    })

    permohonanJumlah.on('focus', obj => {
        $(obj.target).removeClass('border-danger')
    })

    permohonanRadioaktif.on('focus', obj => {
        $(obj.target).removeClass('border-danger')
    })

    selectSatuanKerja.on('focus', (obj) => {
        $(obj.target).removeClass("border-danger")
    })

    selectPj.on('select2:open', (obj) => {
        $('#invalid-pj').removeClass("border border-danger")
    })

    inputNamaLayanan.on('focus', obj => {
        $(obj.target).removeClass('border-danger')
    })

    $('#pagination-container').on('click', 'a', function (e) {
        e.preventDefault();
        const pageno = e.target.dataset.page;

        loadLayananjasa(pageno);
    });

    saveLayanan.on('click', function (obj) {
        obj.preventDefault();
        const data = cekValidation()
        if (data) {

            if(idLayanan){
                $(obj.target).attr('disabled', true).html(`
                    <span class="spinner-border spinner-border-sm" role="status"></span> Update
                `)

                data.append('layanan_hash', idLayanan)

                ajaxPost('api/layananjasa/updateLayanan', data, result => {
                    if (result.success) {
                        Swal.fire({
                            icon: 'success',
                            text: result.message,
                            timer: 1000,
                            timerProgressBar: true,
                            showConfirmButton: false
                        }).then(() => {
                            modalLayanan.modal('hide')
                            loadLayananjasa()
                            $(obj.target).attr('disabled', false).empty().html(`
                                <i class="bi bi-floppy2-fill"></i> Update
                            `)
                        });

                    } else {
                        Swal.fire({
                            icon: 'danger',
                            text: 'Terjadi masalah saat update',
                            timer: 2000,
                            timerProgressBar: true,
                        })
                    }
                })
            }else{
                $(obj.target).attr('disabled', true).html(`
                    <span class="spinner-border spinner-border-sm" role="status"></span> Save
                `)
                ajaxPost('api/layananjasa/addLayanan', data, (result) => {
                    if (result.success) {
                        Swal.fire({
                            icon: 'success',
                            text: result.message,
                            timer: 1000,
                            timerProgressBar: true,
                            showConfirmButton: false
                        }).then(() => {
                            modalLayanan.modal('hide')
                            loadLayananjasa()
                            $(obj.target).attr('disabled', false).empty().html(`
                                <i class="bi bi-floppy2-fill"></i> Save
                            `)
                        });

                    } else {
                        Swal.fire({
                            icon: 'danger',
                            text: 'Terjadi masalah saat menyimpan',
                            timer: 2000,
                            timerProgressBar: true,
                        })
                    }
                })
            }

        }

    })

    selectSatuanKerja.on('change', (e) => {
        const target = e.target;
        if (target.value) {
            getPegawai(target.value)
        } else {
            selectPj.html('<option>-- Select --</option>')
        }
    })

    modalLayanan.on('show.bs.modal', event => {
        clearForm();
    })
})
