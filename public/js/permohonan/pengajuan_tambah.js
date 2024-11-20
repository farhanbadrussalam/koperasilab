const periode = [];
const idPermohonan = $('#id_permohonan').val();
function loadPengguna(){
    let params = {
        idPermohonan: idPermohonan
    }
    $('#pengguna-placeholder').show();
    $('#pengguna-list-container').hide();
    ajaxGet(`api/v1/permohonan/listPengguna`, params, result => {
        if(result.meta.code == 200){
            let html = '';
            for (const [i,pengguna] of result.data.entries()) {
                let txtRadiasi = '';
                pengguna.radiasi?.map(nama_radiasi => txtRadiasi += `<span class="badge rounded-pill text-bg-secondary me-1 mb-1">${nama_radiasi}</span>`);
                
                html += `
                    <div class="card mb-2 shadow-sm border-dark">
                        <div class="card-body row align-items-center">
                            <div class="col-md-4 lh-sm d-flex align-items-center">
                                <span class="col-form-label me-2">${i + 1}</span>
                                <div class="mx-2">
                                    <div>${pengguna.nama}</div>
                                    <small class="text-body-secondary fw-light">${pengguna.posisi}</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                ${[1,2].includes(pengguna.status) ? '<span class="badge text-bg-success">Active</span>' : '<span class="badge text-bg-danger">Inactive</span>'}
                            </div>
                            <div class="col-md-3 d-flex flex-wrap justify-content-center">
                                ${txtRadiasi}
                            </div>
                            <div class="col-md-2 text-end">
                                <button class="btn btn-sm btn-outline-secondary" data-path="${pengguna.media.file_path}" data-file="${pengguna.media.file_hash}" onclick="showPreviewKtp(this)" title="Show ktp"><i class="bi bi-file-person-fill"></i></button>
                                <button class="btn btn-sm btn-outline-danger" data-idpengguna="${pengguna.permohonan_pengguna_hash}" onclick="deletePengguna(this)" title="Delete"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                    </div>
                `;
            }

            if(result.data.length == 0){
                html = `
                    <div class="d-flex flex-column align-items-center py-3">
                        <img src="${base_url}/images/no_data2_color.svg" style="width:220px" alt="">
                        <span class="fw-bold mt-3 text-muted">No Data Available</span>
                    </div>
                `;
            }
            $('#jum_pengguna').val(result.data.length);

            calcPrice();
            $('#pengguna-list-container').html(html);
            $('#pengguna-placeholder').hide();
            $('#pengguna-list-container').show();
        }
    }, error => {
        const result = error.responseJSON;
        if(result.meta.code == 500){
            Swal.fire({
                icon: "error",
                text: 'Server error',
            });
            console.error(result.data.msg);
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

$(function () {

    const formInputan = $('#form-inputan');
    const formTipeKontrak = $('#form-tipe-kontrak');
    const formNoKontrak = $('#form-no-kontrak');
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

    function resetForm(){
        formTipeKontrak.hide();
        formNoKontrak.hide();
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
    
        $('#tipe_kontrak').val('kontrak baru');
        $('#no_kontrak').val('');
        $('#durasi').val('');
        $('#jenis_tld').val('');
        $('#jum_kontrol').val('');
        $('#pic').val('');
        $('#nohp').val('');
        $('#alamat').val('');
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
        let html = '<option value="">Pilih</option>';
        if(layanan == ''){
            formInputan.addClass('d-none').removeClass('d-block');
        }else{
            ajaxGet(`api/v1/permohonan/getJenisTld/${layanan}`, false, result => {
                if(result.meta.code == 200){
                    let list = result.data;
                    formInputan.addClass('d-block').removeClass('d-none');
    
                    list.forEach(value => {
                        html += `<option value="${value.jenis_tld.jenis_tld_hash}">${value.jenis_tld.name}</option>`
                    });

                    $('#jenis_tld').html(html);
                }
            });
        }
        return;
    });

    $('#tipe_kontrak').on('change', obj => {
        let tipeKontrak = obj.target.value;
        switch (tipeKontrak) {
            case 'kontrak baru':
                formNoKontrak.hide();
                break;
            case 'perpanjangan':
                formNoKontrak.show();
                break;
        }
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
        let valtipeKontrak = $('#tipe_kontrak').val();
        let valnoKontrak = $('#no_kontrak').val();
        let valzerocek = $('#zero_cek').val();
        let valjenisTld = $('#jenis_tld').val();
        let valperiodePemakaian = $('#periode-pemakaian').attr('data-periode');
        let valjumPengguna = $('#jum_pengguna').val();
        let valjumKontrol = $('#jum_kontrol').val();
        let valpic = $('#pic').val();
        let valnoHp = $('#nohp').val();
        let valalamat = $('#alamat').val();
        let valtotalHarga = $('#total_harga').val();
        let valHargaLayanan = window.price;

        const formData = new FormData();
        formData.append('idPermohonan', idPermohonan);
        formData.append('idLayanan', vallayananJasa);
        formData.append('jenisLayanan1', valjenisLayanan1);
        formData.append('jenisLayanan2', valjenisLayanan2);

        formData.append('tipeKontrak', valtipeKontrak);
        formData.append('jenisTld', valjenisTld);
        formData.append('periodePemakaian', valperiodePemakaian);
        formData.append('jumlahPengguna', valjumPengguna);
        formData.append('jumlahKontrol', valjumKontrol);
        formData.append('hargaLayanan', valHargaLayanan);
        formData.append('totalHarga', valtotalHarga);

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
            Swal.fire({
                icon: "error",
                text: 'Server error',
            });
            spinner('hide', obj.target);
            console.error(error.responseJSON.data.msg);
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

        const formData = new FormData();
        formData.append('ktp', imageKtp);
        formData.append('nama', namaPengguna);
        formData.append('divisi', divisiPengguna);
        formData.append('radiasi', JSON.stringify(jenisRadiasi));
        formData.append('idPermohonan', idPermohonan);
        
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
            Swal.fire({
                icon: "error",
                text: 'Server error',
            });
            console.error(error.responseJSON.data.msg);
            spinner('hide', obj.target);
        });
    });

    $('#modal-add-pengguna').on('hidden.bs.modal', event => {
        $('#nama_pengguna').val('');
        $('#divisi_pengguna').val('');
        $('#jenis_radiasi').val(null).trigger('change');
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