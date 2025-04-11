const periode = [];
const idPermohonan = $('#id_permohonan').val();
let arrKontrolTmp = [];
let typeLayanan = '';

$(function () {
    const formInputan = $('#form-inputan');
    const formTipeKontrak = $('#form-tipe-kontrak');
    const formPeriode = $('#form-periode');
    const formJenisTld = $('#form-jenis-tld');
    const formJumPengguna = $('#form-jum-pengguna');
    const formJumKontrol = $('#form-jum-kontrol');
    const formPic = $('#form-pic');
    const formNoHp = $('#form-nohp');
    const formAlamat = $('#form-alamat');
    const formPeriodeNext = $('#form-periode-next');
    const formPeriode1 = $('#form-periode-1');
    const formPeriode2 = $('#form-periode-2');
    const formTotalHarga = $('#form-total-harga');
    const formZeroCek = $('#form-zero-cek');
    const btnAddPengguna = $('#btn-add-pengguna');
    const btnPilihPengguna = $('#btn-pilih-pengguna');
    const btnTambahPengguna = $('#btn-tambah-pengguna');

    const modalNamaPengguna = $('#nama_pengguna')
    const modalJenisRadiasi = $('#jenis_radiasi');
    const modalDivisiPengguna = $('#divisi_pengguna');

    const optionsUploadKTP = {
        allowedFileExtensions: ['png', 'gif', 'jpeg', 'jpg']
    };

    modalJenisRadiasi.select2({
        theme: "bootstrap-5",
        tags: true,
        placeholder: "Pilih Jenis Radiasi",
        dropdownParent: $('#modal-add-pengguna'),
        createTag: (params) => {
            return {
                id: params.term,
                text: params.term,
                newTag: true
            };
        }
    });
    setDropify('init', '#uploadKtpPengguna', optionsUploadKTP);

    resetForm();
    loadPengguna();

    let htmlAlamat = '<option value="">Pilih alamat</option>';
    for (const [i,value] of dataPermohonan.pelanggan.perusahaan.alamat.entries()) {
        htmlAlamat += `<option value='${i}'>Alamat ${value.jenis}</option>`;
    }

    $('#selectAlamat').html(htmlAlamat);

    $('#selectAlamat').on('change', obj => {
        if(dataPermohonan){
            const perusahaan = dataPermohonan.pelanggan.perusahaan;

            if(perusahaan.alamat[obj.target.value]){
                $('#txt_alamat').val(perusahaan.alamat[obj.target.value].alamat + ", "+ perusahaan.alamat[obj.target.value].kode_pos);
            }else{
                $('#txt_alamat').val('');                
            }
        }
    });

    function resetForm(){
        formTipeKontrak.hide();
        formPic.hide();
        formNoHp.hide();
        formAlamat.hide();
        formPeriodeNext.hide();
        formPeriode.hide();
        formJenisTld.hide();
        formJumPengguna.hide();
        formJumKontrol.hide();
        formTotalHarga.hide();
        formPeriode1.hide();
        formPeriode2.hide();
        formZeroCek.hide();
    
        $('#no_kontrak').val('');
        $('#durasi').val('');
        $('#jenis_tld').val('');
        $('#jum_kontrol').val('');
        $('#pic').val('');
        $('#nohp').val('');
        $('#periode_next').val('');
        $('#periode_1').val('');
        $('#periode_2').val('');
        $('#total_harga').val('');
        $('#zero_cek').val('');
    }

    $('#btn-add-pengguna').on('click', () => {
        $('#modal-add-pengguna').modal('show');
    });
    $('#btn-pilih-pengguna').on('click', () => {
        $('#modal-pilih-pengguna').modal('show');
    })
    $('#btn-add-kontrol').on('click', () => {
        $('#modal-add-kontrol').modal('show');
    });

    $('#jenis_layanan').on('change', obj => {
        let jenisLayanan = obj.target.value;

        if(jenisLayanan == ''){
            formInputan.addClass('d-none').removeClass('d-block');
            $('#jenis_layanan_2').html('<option value="">Pilih</option>');
            return;
        }

        ajaxGet(`api/v1/permohonan/getChildJenisLayanan/${jenisLayanan}`, false, (result) => {
            resetForm();
            if(result.meta.code == 200){
                let parent = result.data;

                let html = '<option value="">Pilih</option>';
                
                parent.child.forEach((list) => {
                    html += `<option value='${list.jenis_layanan_hash}'>${list.name}</option>`;
                });

                switch (parent.name.toLowerCase()) {
                    case 'kontrak':
                        btnAddPengguna.addClass('d-block').removeClass('d-none');
                        btnPilihPengguna.addClass('d-none').removeClass('d-block');
                        formTipeKontrak.show();
                        formPeriode.show();
                        formJenisTld.show();
                        formJumPengguna.show();
                        formJumKontrol.show();
                        formTotalHarga.show();
                        break;
                    case 'evaluasi':
                        // btnAddPengguna.addClass('d-none').removeClass('d-block');
                        // btnPilihPengguna.addClass('d-block').removeClass('d-none');
                        formTipeKontrak.show();
                        formPeriode.show();
                        formJenisTld.show();
                        formJumKontrol.show();
                        formJumPengguna.show();
                        formPic.show();
                        formNoHp.show();
                        formAlamat.show();
                        formPeriodeNext.show();
                        formTotalHarga.show();
                        break;
                    case 'zero cek':
                        btnAddPengguna.addClass('d-none').removeClass('d-block');
                        btnPilihPengguna.addClass('d-block').removeClass('d-none');
                        formTipeKontrak.show();
                        formPeriode.show();
                        formJenisTld.show();
                        formJumKontrol.show();
                        formJumPengguna.show();
                        formPic.show();
                        formNoHp.show();
                        formAlamat.show();
                        formPeriodeNext.show();
                        formTotalHarga.show();
                        break;
                    case 'adendum':
                        btnAddPengguna.addClass('d-block').removeClass('d-none');
                        btnPilihPengguna.addClass('d-none').removeClass('d-block');
                        formTipeKontrak.show();
                        formPeriode.show();
                        formJenisTld.show();
                        formJumPengguna.show();
                        formJumKontrol.show();
                        formTotalHarga.show();
                        break;
                    case 'pembelian':
                        formJenisTld.show();
                        formJumPengguna.show();
                        formJumKontrol.show();
                        formPeriode1.show();
                        formPeriode2.show();
                        formTotalHarga.show();
                        break;
                }
                formInputan.addClass('d-none').removeClass('d-block');
                $('#jenis_layanan_2').html(html);
                html = '';
            }
        })
        return;
    });

    $('#jenis_layanan_2').on('change', obj => {
        const layanan = obj.target.value;
        typeLayanan = obj.target.selectedOptions[0].innerHTML;
        let html = '<option value="">Pilih</option>';
        $('#divKontrolEvaluasi').hide();
        $('#btnTambahKontrol').hide();
        $('#form-kode-lencana-pengguna').hide();
        $('#jum_kontrol').attr('readonly', false).removeClass('bg-secondary-subtle');
        arrKontrolTmp = [];
        if(layanan == ''){
            formInputan.addClass('d-none').removeClass('d-block');
        }else{
            ajaxGet(`api/v1/permohonan/getJenisTld/${layanan}`, false, result => {
                if(result.meta.code == 200){
                    let list = result.data;
                    if(typeLayanan == 'Evaluasi') {
                        $('#divKontrolEvaluasi').show();
                        $('#btnTambahKontrol').show();
                        $('#form-kode-lencana-pengguna').show();
                        $('#jum_kontrol').attr('readonly', true).addClass('bg-secondary-subtle');
                    }
                    formInputan.addClass('d-block').removeClass('d-none');
    
                    list.forEach(value => {
                        html += `<option value="${value.jenis_tld.jenis_tld_hash}">${value.jenis_tld.name}</option>`
                    });

                    $('#jenis_tld').html(html);
                }
            });
        }
        loadKontrol();
        return;
    });

    $('#jenis_tld').on('change', obj => {
        const idJenisLayanan = $('#jenis_layanan_2').val();
        const idJenisTld = obj.target.value;
        
        spinner('show', $('#label_total_harga'), {place: 'after'});
        if(idJenisLayanan && idJenisTld){
            const params = {
                idJenisLayanan : idJenisLayanan,
                idJenisTld : idJenisTld
            }
            ajaxGet(`api/v1/permohonan/getPrice`, params, result => {
                let price = result.data.price;
                window.price = price;
                
                calcPrice();
                spinner('hide', $('#label_total_harga'));
            })
        }else {
            window.price = 0;
            calcPrice();
            spinner('hide', $('#label_total_harga'));
        }
    });

    $('#simpanPengajuan').on('click', obj => {
        let vallayananJasa = $('#layanan_jasa').val();
        let valjenisLayanan1 = $('#jenis_layanan').val();
        let valjenisLayanan2 = $('#jenis_layanan_2').val();
        let valjenisTld = $('#jenis_tld').val();
        let valperiodePemakaian = $('#periode-pemakaian').attr('data-periode');
        let valjumPengguna = $('#jum_pengguna').val();
        let valjumKontrol = $('#jum_kontrol').val();
        let valpic = $('#pic').val();
        let valnoHp = $('#nohp').val();
        let valAlamat = $('#selectAlamat').val();
        let valtotalHarga = $('#total_harga').val();
        let valHargaLayanan = window.price;

        dataPermohonan.pelanggan.perusahaan.alamat[valAlamat] ? valAlamat = dataPermohonan.pelanggan.perusahaan.alamat[valAlamat].alamat_hash : false;

        const sanityCek = [];
        if (!valjenisTld) sanityCek.push('Jenis TLD');
        if (!valperiodePemakaian) sanityCek.push('Periode Pemakaian');
        if (valjumPengguna == 0) sanityCek.push('Jumlah Pengguna');
        if (valjumKontrol == 0) sanityCek.push('Jumlah Kontrol');

        // Jika layanan evaluasi
        if (typeLayanan === 'Evaluasi' && arrKontrolTmp.some(value => !value.kode_lencana)) {
            return Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Kode lencana tidak boleh kosong!'
            });
        }

        if(sanityCek.length > 0){
            return Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: `Data berikut masih kosong: ${sanityCek.join(', ')}`
            });
        }

        Swal.fire({
            title: 'Apa kamu yakin?',
            text: "Apakah Anda ingin melanjutkan tindakan ini?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, proceed!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Proceed with the action
                const formData = new FormData();
                formData.append('idPermohonan', idPermohonan);
                formData.append('idLayanan', vallayananJasa);
                formData.append('jenisLayanan1', valjenisLayanan1);
                formData.append('jenisLayanan2', valjenisLayanan2);
        
                formData.append('pic', valpic);
                formData.append('noHp', valnoHp);
                formData.append('alamat', valAlamat);
                
                formData.append('tipeKontrak', 'kontrak baru');
                formData.append('jenisTld', valjenisTld);
                formData.append('periodePemakaian', valperiodePemakaian);
                formData.append('jumlahPengguna', valjumPengguna);
                formData.append('jumlahKontrol', valjumKontrol);
                formData.append('hargaLayanan', valHargaLayanan);
                formData.append('totalHarga', valtotalHarga);
                formData.append('periode', 0);

                typeLayanan == 'Evaluasi' ? formData.append('tldKontrol', JSON.stringify(arrKontrolTmp)) : false;
        
                spinner('show', obj.target);
                ajaxPost(`api/v1/permohonan/tambahPengajuan`, formData, result => {
                    Swal.fire({
                        icon: 'success',
                        text: 'Pengajuan berhasil dibuat',
                        timer: 1200,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = base_url+"/permohonan/pengajuan";
                    });
                }, error => {
                    spinner('hide', obj.target);
                });
            }
        });
    });

    $('#btn-clear-periode').on('click', obj => {
        $('#periode-pemakaian').val('');
        $('#periode-pemakaian').attr('data-periode', '');
        $('#periode-pemakaian').attr('data-jumperiode', '');
        $('#btn-clear-periode').addClass('d-none').removeClass('d-block');
        periodeJs.addData([]);
        calcPrice();
    });

    btnTambahPengguna.on('click', obj => {
        spinner('show', obj.target);
        const namaPengguna = modalNamaPengguna.val();
        const divisiPengguna = modalDivisiPengguna.val();
        const jenisRadiasi = modalJenisRadiasi.val();
        const imageKtp = $('#uploadKtpPengguna')[0].files[0];
        const kodeLencana = $('#kodeLencanaPengguna').val();

        const formData = new FormData();
        formData.append('ktp', imageKtp);
        formData.append('nama', namaPengguna);
        formData.append('divisi', divisiPengguna);
        formData.append('radiasi', JSON.stringify(jenisRadiasi));
        formData.append('idPermohonan', idPermohonan);
        kodeLencana ? formData.append('kode_lencana', kodeLencana) : false;
        
        ajaxPost(`api/v1/permohonan/tambahPengguna`, formData, result => {
            spinner('hide', obj.target);
            if(result.meta.code == 200){
                Swal.fire({
                    icon: "success",
                    text: result.data.msg,
                });
                loadPengguna();
                $('#modal-add-pengguna').modal('hide');
            }else{
                Swal.fire({
                    icon: "error",
                    text: result.data.msg,
                });
            }
        }, error => {
            spinner('hide', obj.target);
        });
    });

    $('#modal-add-pengguna').on('hidden.bs.modal', event => {
        $('#nama_pengguna').val('');
        $('#divisi_pengguna').val('');
        $('#jenis_radiasi').val(null).trigger('change');
        $('#kodeLencanaPengguna').val('');
        setDropify('reset', '#uploadKtpPengguna', optionsUploadKTP);
    });
})
// js add periode
let getPeriode = $('#periode-pemakaian').attr('data-periode');
const periodeJs = new Periode(getPeriode);

$('#btn-periode').on('click', obj => {
    periodeJs.show();
});

periodeJs.on('periode.simpan', result => {
    const dataPeriode = periodeJs.getData();
    $('#periode-pemakaian').val(dataPeriode.length + ' Periode');
    $('#periode-pemakaian').attr('data-periode', JSON.stringify(dataPeriode));
    $('#periode-pemakaian').attr('data-jumperiode', dataPeriode.length);
    $('#btn-clear-periode').addClass('d-block').removeClass('d-none');
    
    calcPrice();
});

function loadPengguna(){
    let params = {
        idPermohonan: idPermohonan
    }
    // $('#pengguna-placeholder').show();
    // $('#pengguna-list-container').hide();
    ajaxGet(`api/v1/permohonan/listPengguna`, params, result => {
        if(result.meta.code == 200){
            let html = '';
            for (const [i,pengguna] of result.data.entries()) {
                let txtRadiasi = '';
                pengguna.radiasi?.map(nama_radiasi => txtRadiasi += `<span class="badge rounded text-bg-secondary me-1 mb-1">${nama_radiasi}</span>`);
                
                html += `
                    <div class="card mb-1 shadow-sm">
                        <div class="card-body row align-items-center p-1 px-3">
                            <div class="col-md-7 lh-sm align-items-center">
                                <div>${pengguna.nama}</div>
                                <small class="text-body-secondary fw-light">${pengguna.posisi}</small>
                                <div class="d-flex flex-wrap">
                                    ${txtRadiasi}
                                </div>
                            </div>
                            <div class="col-auto ms-auto">
                                <span class="fw-bold">${pengguna.permohonan_tld.tld_tmp ?? ''}</span>
                            </div>
                            <div class="col-auto text-end ms-auto">
                                <a class="btn btn-sm btn-outline-secondary show-popup-image" href="${base_url}/storage/${pengguna.media.file_path}/${pengguna.media.file_hash}" title="Show ktp"><i class="bi bi-file-person-fill"></i></a>
                                <button type="button" class="btn btn-sm btn-outline-danger" data-idpengguna="${pengguna.permohonan_pengguna_hash}" onclick="deletePengguna(this)" title="Delete"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                    </div>
                `;
            }

            if(result.data.length == 0){
                html = `
                    <div class="d-flex flex-column align-items-center py-4">
                        <span class="fw-bold text-muted">Tidak ada pengguna</span>
                    </div>
                `;
            }
            $('#jum_pengguna').val(result.data.length);

            calcPrice();
            $('#pengguna-list-container').html(html);
            // $('#pengguna-placeholder').hide();
            // $('#pengguna-list-container').show();
            showPopupReload();
        }
    })
}
function deletePengguna(obj){
    let idPengguna = $(obj).data('idpengguna');

    ajaxDelete(`api/v1/permohonan/destroyPengguna/${idPengguna}`, result => {
        Swal.fire({
            icon: 'success',
            text: result.data.msg,
            timer: 1200,
            timerProgressBar: true,
            showConfirmButton: false
        }).then(() => {
            loadPengguna()
        });
    }, error => {
        Swal.fire({
            icon: "error",
            text: 'Server error',
        });
        console.error(error.responseJSON.data.msg);
    })
}

function loadKontrol(){
    let html = '';
    for (const [i,kode] of arrKontrolTmp.entries()) {
        html += `
            <div class="input-group mb-1">
                <input type="text" class="form-control" name="kodeLencanaKontrol" value="${kode.kode_lencana}" data-index="${i}" placeholder="Masukkan Kode Lencana" oninput="addFormKontrol(this)" />
                <button type="button" class="input-group-text btn btn-sm btn-outline-danger" data-index="${i}" onclick="removeFormKontrol(this)" title="Delete"><i class="bi bi-trash"></i></button>
            </div>
        `;
    }
    if(arrKontrolTmp.length == 0){
        html = `
            <div class="d-flex flex-column align-items-center py-4">
                <span class="fw-bold text-muted">Tidak ada kontrol</span>
            </div>
        `;
    }
    $('#jum_kontrol').val(arrKontrolTmp.length);
    calcPrice();

    $('#kontrol-list-container').html(html);
}
function addFormKontrol(obj = false) {
    if(obj){
        const index = $(obj).data('index');

        arrKontrolTmp[index].kode_lencana = $(obj).val();
    }else{
        arrKontrolTmp.push({
            "kode_lencana": ""
        });
        loadKontrol();
    }

}
function removeFormKontrol(obj){
    const index = $(obj).data('index');
    arrKontrolTmp.splice(index, 1);

    loadKontrol();
}
function calcPrice(){
    let price = window.price;
    let subTotal = price;
    let per = $('#periode-pemakaian').attr('data-jumperiode');
    let jumlah = Number($('#jum_pengguna').val()) + Number($('#jum_kontrol').val());

    if(per){
        subTotal *= Number(per);
    }

    if(jumlah != 0){
        subTotal *= jumlah;
    }
    $('#total_harga').val(subTotal);
    
    maskReload();
}

function showHideCollapse(obj){
    const collapse = obj;
    if(!collapse.classList.contains('show')) { 
        collapse.innerHTML = '<i class="bi bi-eye"></i> Tampilkan'; 
    } else { 
        collapse.innerHTML = '<i class="bi bi-eye-slash"></i> Lebih sedikit';
    } 
    collapse.classList.toggle('show');
}