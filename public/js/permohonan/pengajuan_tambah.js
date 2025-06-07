const periode = [];
const idPermohonan = $('#id_permohonan').val();
let arrKontrolTmp = [];
let typeLayanan = '';
let typeLayanan2 = '';
let datatable_ = false;
let arrListPengguna = [];

let inventoryTldPengguna = false;
let tmpArrTldPengguna = [];

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

const modalNamaPengguna = $('#nama_pengguna');
const modalJenisRadiasi = $('#jenis_radiasi');

const optionsUploadKTP = {
    allowedFileExtensions: ['png', 'gif', 'jpeg', 'jpg']
};
$(function () {
    // cek jika id_layanan sudah ada
    cekLayanan();

    inventoryTld = new Inventory_tld({preview: true});
    inventoryTld.on('inventory.selected', (e) => {
        const detail = e.detail;

        const params = new FormData();
        params.append('id_permohonan_tld', detail.selected);
        params.append('id_tld', detail.data_tld.tld_hash);

        ajaxPost(`api/v1/permohonan/action_tld`, params, result => {
           loadPengguna();
           loadKontrol();
        });
    });

    setDropify('init', '#uploadKtpPengguna', optionsUploadKTP);

    resetForm();
    loadPengguna();

    let htmlAlamat = '<option value="">Pilih alamat</option>';

    if(dataPermohonan?.pelanggan?.perusahaan?.alamat){
        for (const [i,value] of Object.entries(dataPermohonan.pelanggan.perusahaan.alamat)) {
            htmlAlamat += `<option value='${i}'>Alamat ${value.jenis}</option>`;
        }
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
        datatable_.ajax.reload();
        $('#modal-add-tld-pengguna').modal('show');
    });
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

        spinner('show', $('#label-jenis-layanan-2'), {place: 'after'});
        ajaxGet(`api/v1/permohonan/getChildJenisLayanan/${jenisLayanan}`, false, (result) => {
            if(result.meta.code == 200){
                let parent = result.data;

                let html = '<option value="">Pilih</option>';

                parent.child.forEach((list) => {
                    html += `<option value='${list.jenis_layanan_hash}'>${list.name}</option>`;
                });

                formInputan.addClass('d-none').removeClass('d-block');
                $('#jenis_layanan_2').html(html);
                html = '';
                spinner('hide', $('#label-jenis-layanan-2'));
            }
        })
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
                formData.append('periode', 1);

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

    $('#btn-buat-form').on('click', obj => {
        spinner('show', obj.target);
        const jenisLayanan = $('#jenis_layanan').val();
        const jenisLayanan2 = $('#jenis_layanan_2').val();
        const layananJasa = $('#layanan_jasa').val();
        typeLayanan = $('#jenis_layanan').find(':selected').text();
        typeLayanan2 = $('#jenis_layanan_2').find(':selected').text();

        if(jenisLayanan == '' || jenisLayanan2 == '' || layananJasa == ''){
            Swal.fire({
                icon: 'warning',
                title: 'Oops...',
                text: 'Data berikut masih kosong: Jenis Layanan, Layanan Jasa'
            });
            return spinner('hide', obj.target);
        }

        const formData = new FormData();
        formData.append('idPermohonan', idPermohonan);
        formData.append('jenisLayanan1', jenisLayanan);
        formData.append('jenisLayanan2', jenisLayanan2);
        formData.append('idLayanan', layananJasa);
        formData.append('status', 80); // masih draft

        ajaxPost(`api/v1/permohonan/tambahPengajuan`, formData, result => {
            openForm();

            // disable untuk form jenisLayanan, jenisLayanan2, dan layananJasa
            $('#jenis_layanan').attr('readonly', true).addClass('bg-secondary-subtle');
            $('#jenis_layanan_2').attr('readonly', true).addClass('bg-secondary-subtle');
            $('#layanan_jasa').attr('readonly', true).addClass('bg-secondary-subtle');

            // menghilangkan button buat form
            $('#div-buat-form').addClass('d-none').removeClass('d-block');

            spinner('hide', obj.target);
        }, error => {
            spinner('hide', obj.target);
        });
    });

    $('#btn-tambah-pengguna').on('click', obj => {
        spinner('show', obj.target);
        const namaPengguna = modalNamaPengguna.val();
        const divisiPengguna = $('#divisi_pengguna').val();
        const jenisRadiasi = modalJenisRadiasi.val();
        const imageKtp = $('#uploadKtpPengguna')[0].files[0];
        const nikPengguna = $('#nik_pengguna').val();
        const jenisKelamin = $('#jenis_kelamin').val();
        const tanggalLahir = $('#tanggal_lahir').val();
        const tempatLahir = $('#tempat_lahir').val();
        const kodeLencana = $('#kode_lencana').val();
        const isAktif = $('#is_aktif').is(':checked') ? 1 : 0;

        const formData = new FormData();
        formData.append('nik', nikPengguna);
        formData.append('kode_lencana', kodeLencana);
        formData.append('is_aktif', isAktif);
        formData.append('jenis_kelamin', jenisKelamin);
        formData.append('tanggal_lahir', tanggalLahir);
        formData.append('tempat_lahir', tempatLahir);
        formData.append('ktp', imageKtp);
        formData.append('name', namaPengguna);
        formData.append('divisi', divisiPengguna);
        formData.append('radiasi', JSON.stringify(jenisRadiasi));

        ajaxPost(`api/v1/pengguna/action`, formData, result => {
            if (result.meta.code == 200) {
                Swal.fire({
                    icon: "success",
                    text: result.data.msg,
                });
                btnPilih(result.data.id);
                spinner('hide', obj.target);
                $('#modal-add-tld-pengguna').modal('hide');
            } else {
                Swal.fire({
                    icon: "error",
                    text: result.data.msg,
                });
                spinner('hide', obj.target);
            }
        }, error => {
            spinner('hide', obj.target);
        })
    });

    $('#modal-add-tld-pengguna').on('hidden.bs.modal', event => {
        $('#nama_pengguna').val('');
        $('#divisi_pengguna').val('');
        $('#jenis_radiasi').val(null).trigger('change');
        $('#noSeriPengguna').val('');
        tmpArrTldPengguna = [];
        setDropify('reset', '#uploadKtpPengguna', optionsUploadKTP);
    });

    $('#btn-close-pengguna').on('click', obj => {
        $('#modal-add-pengguna').modal('hide');
        $('#nama_pengguna').val('');
        $('#divisi_pengguna').val(null).trigger('change');
        $('#jenis_radiasi').val(null).trigger('change');
        $('#uploadKtpPengguna').val('');

        $('#modal-add-tld-pengguna').modal('show');
    });

    $('#tanggal_lahir').flatpickr({
        enableTime: false,
        dateFormat: "Y-m-d"
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
            if(result.data){
                for (const [i, value] of result.data.entries()) {
                    let txtRadiasi = '';
                    value.radiasi?.map(nama_radiasi => txtRadiasi += `<span class="badge rounded text-bg-secondary me-1 mb-1">${nama_radiasi}</span>`);
                    let pengguna = value.pengguna;

                    let htmlEvaluasi = `
                        <hr class="my-2">
                        <div class="col-12">
                            <div class="input-group">
                                <input type="text" class="form-control form-control-sm" value="${value.tld?.no_seri_tld ?? ''}" placeholder="Pilih No Seri" readonly>
                                <button type="button" class="input-group-text btn btn-sm btn-outline-secondary" data-id="${value.permohonan_tld_hash}" title="Change" onclick="openInventory(this, 'pengguna')"><i class="bi bi-pencil"></i> Ganti</button>
                            </div>
                        </div>
                    `;
                    html += `
                        <div class="card mb-1 shadow-sm p-1">
                            <div class="card-body row align-items-center p-1 px-3">
                                <div class="col-md-7 lh-sm align-items-center">
                                    <div>${pengguna.name}</div>
                                    <small class="text-body-secondary fw-light">${pengguna.divisi?.name ?? ''}</small>
                                    <div class="d-flex flex-wrap">
                                        ${txtRadiasi}
                                    </div>
                                </div>
                                <div class="col-auto text-end ms-auto">
                                    <a class="btn btn-sm btn-outline-secondary show-popup-image" href="${base_url}/storage/${pengguna.media_ktp.file_path}/${pengguna.media_ktp.file_hash}" title="Show ktp"><i class="bi bi-file-person-fill"></i></a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-idpengguna="${value.pengguna.pengguna_hash}" onclick="deletePengguna(this)" title="Delete"><i class="bi bi-trash"></i></button>
                                </div>
                                ${typeLayanan2 == 'Evaluasi' ? htmlEvaluasi : ``}
                            </div>
                        </div>
                    `;
                }
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
    });

    if(!datatable_){
        datatable_ = $('#table-pengguna').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: `${base_url}/management/getDataPengguna`,
                data: {
                    type: 'selected'
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'name', name: 'name' },
                { data: 'divisi', name: 'divisi' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
            ],
            pageLength: 5
        })

        datatable_.on('draw.dt', function () {
            showPopupReload();
        });
    }
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

function deleteKontrol(obj){
    let idKontrol = $(obj).data('id');

    ajaxDelete(`api/v1/permohonan/destroyKontrol/${idKontrol}`, result => {
        Swal.fire({
            icon: 'success',
            text: result.data.msg,
            timer: 1200,
            timerProgressBar: true,
            showConfirmButton: false
        }).then(() => {
            loadKontrol()
        });
    }, error => {
        Swal.fire({
            icon: "error",
            text: 'Server error',
        });
        console.error(error.responseJSON.data.msg);
    })
}

{/* <div class="input-group mb-1">
<input type="text" class="form-control" name="kodeLencanaKontrol" value="${kode.kode_lencana}" data-index="${i}" placeholder="Masukkan Kode Lencana" oninput="addFormKontrol(this)" />
<button type="button" class="input-group-text btn btn-sm btn-outline-danger" data-index="${i}" onclick="removeFormKontrol(this)" title="Delete"><i class="bi bi-trash"></i></button>
</div> */}
function loadKontrol(){
    let html = '';
    ajaxGet(`api/v1/permohonan/loadTld`, {idPermohonan: idPermohonan}, result => {
        // mengambil data kontrol
        arrKontrolTmp = result.data.tldPermohonan.filter(tld => tld.id_divisi || (!tld.id_pengguna && !tld.id_divisi));
        let jumKontrol = 0;
        for (const [i,kode] of arrKontrolTmp.entries()) {
            let htmlEvaluasi = `
                <hr class="my-2">
                <div class="col-12">
                    <div class="input-group">
                        <input type="text" class="form-control form-control-sm" value="${kode.tld?.no_seri_tld ?? ''}" placeholder="Pilih No Seri" readonly>
                        <button type="button" class="input-group-text btn btn-sm btn-outline-secondary" data-id="${kode.permohonan_tld_hash}" title="Change" onclick="openInventory(this, 'kontrol')"><i class="bi bi-pencil"></i> Ganti</button>
                    </div>
                </div>
            `;
            html += `
                <div class="card mb-1 shadow-sm p-1">
                    <div class="card-body row align-items-center p-1 px-3">
                        <div class="col-md-7 lh-sm align-items-center">
                            <label class="form-label col-form-label fw-bold">Kontrol ${kode.divisi?.name ?? ''}<small class="text-body-secondary fw-light"> - ${kode.divisi?.kode_lencana ?? 'C'}</small></label>
                        </div>
                        <div class="col-auto text-end ms-auto d-flex justify-content-between gap-2">
                            <div class="cursor-pointer rounded-circle" data-id="${kode.permohonan_tld_hash}" onclick="changeCountKontrol('plus', ${kode.count}, this)">
                                <i class="bi bi-plus-circle text-primary"></i>
                            </div>
                            <div>${kode.count}</div>
                            <div class="cursor-pointer rounded-circle" data-id="${kode.permohonan_tld_hash}" onclick="changeCountKontrol('minus', ${kode.count}, this)">
                                <i class="bi bi-dash-circle text-danger"></i>
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <button type="button" class="btn btn-sm btn-outline-danger" data-id="${kode.permohonan_tld_hash}" onclick="deleteKontrol(this)" title="Delete"><i class="bi bi-trash"></i></button>
                        </div>
                        ${typeLayanan2 == 'Evaluasi' ? htmlEvaluasi : ''}
                    </div>
                </div>
            `;

            jumKontrol += kode.count;
        }
        if(arrKontrolTmp.length == 0){
            html = `
                <div class="d-flex flex-column align-items-center py-4">
                    <span class="fw-bold text-muted">Tidak ada kontrol</span>
                </div>
            `;
        }
        $('#jum_kontrol').val(jumKontrol);
        calcPrice();

        $('#kontrol-list-container').html(html);
    });
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

function openInventory(obj, jenis){
    let id = $(obj).data('id');
    inventoryTld.show(id, tmpArrTldPengguna, jenis);
}

function reload(){
    datatable_.ajax.reload();
}

function btnPilih(obj){
    let id = $(obj).length > 0 ? $(obj).data('id') : obj;

    const params = new FormData();
    params.append('idPengguna', id);
    params.append('idPermohonan', idPermohonan);
    spinner('show', $(obj));
    ajaxPost(`api/v1/permohonan/tambahPengguna`, params, result => {
        loadPengguna();
        loadKontrol();
        reload();
        $('#modal-add-tld-pengguna').modal('hide');
        $('#modal-add-pengguna').modal('hide');
        spinner('hide', $(obj));
    }, error => {
        spinner('hide', $(obj));
    })
}

function openForm(){
    const layanan = $('#jenis_layanan_2').val();
    let html = '<option value="">Pilih</option>';
    // $('#divKontrolEvaluasi').hide();
    $('#btnTambahKontrol').hide();
    $('#form-kode-lencana-pengguna').hide();
    // $('#jum_kontrol').attr('readonly', false).removeClass('bg-secondary-subtle');
    arrKontrolTmp = [];
    if(layanan == ''){
        $('#form-inputan').addClass('d-none').removeClass('d-block');
    }else{
        ajaxGet(`api/v1/permohonan/getJenisTld/${layanan}`, false, result => {
            if(result.meta.code == 200){
                switch (typeLayanan.toLowerCase()) {
                    case 'kontrak':
                        btnAddPengguna.addClass('d-block').removeClass('d-none');
                        formTipeKontrak.show();
                        formPeriode.show();
                        formJenisTld.show();
                        formJumPengguna.show();
                        formJumKontrol.show();
                        formTotalHarga.show();
                        break;
                    case 'evaluasi':
                        // btnAddPengguna.addClass('d-none').removeClass('d-block');
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
                let list = result.data;
                if(typeLayanan == 'Evaluasi') {
                    // $('#divKontrolEvaluasi').show();
                    // $('#btnTambahKontrol').show();
                    // $('#form-kode-lencana-pengguna').show();
                    // $('#jum_kontrol').attr('readonly', true).addClass('bg-secondary-subtle');
                }
                $('#form-inputan').addClass('d-block').removeClass('d-none');

                list.forEach(value => {
                    html += `<option value="${value.jenis_tld.jenis_tld_hash}">${value.jenis_tld.name}</option>`
                });

                $('#jenis_tld').html(html);
            }
        });
    }
    loadKontrol();
    return;
}

function cekLayanan(){
    if(dataPermohonan.layanan_jasa){
        $('#id_layanan').val(dataPermohonan.layanan_jasa.layanan_hash).trigger('change');
        $('#jenis_layanan').val(dataPermohonan.jenis_layanan_parent.jenis_layanan_hash).trigger('change');
        $('#jenis_layanan_2').html(`<option value="${dataPermohonan.jenis_layanan.jenis_layanan_hash}">${dataPermohonan.jenis_layanan.name}</option>`);

        typeLayanan = dataPermohonan.jenis_layanan_parent.name;
        typeLayanan2 = dataPermohonan.jenis_layanan.name;

        // disable untuk form jenisLayanan, jenisLayanan2, dan layananJasa
        $('#jenis_layanan').attr('disabled', true).addClass('bg-secondary-subtle');
        $('#jenis_layanan_2').attr('disabled', true).addClass('bg-secondary-subtle');
        $('#layanan_jasa').attr('disabled', true).addClass('bg-secondary-subtle');

        // menghilangkan button buat form
        $('#div-buat-form').addClass('d-none').removeClass('d-block');

        openForm();
    }
}

function remove(){
    ajaxDelete(`api/v1/permohonan/destroyPermohonan/${idPermohonan}`, result => {
        Swal.fire({
            icon: 'success',
            text: result.data.msg,
            timer: 1200,
            timerProgressBar: true,
            showConfirmButton: false
        }).then(() => {
            window.location.href = base_url+"/permohonan/pengajuan";
        });
    }, error => {
        const result = error.responseJSON;
        if(result?.meta?.code && result.meta.code == 500){
            Swal.fire({
                icon: "error",
                text: 'Server error',
            });
            console.error(result.data.msg);
        }else{
            Swal.fire({
                icon: "error",
                text: 'Server error',
            });
            console.error(result.message);
        }
    });
}

function changeCountKontrol(type, count, obj){
    if(type == 'minus') {
        if (count < 1) return;
    }
    count += type === 'plus' ? 1 : -1;

    const params = new FormData();
    params.append('id_permohonan_tld', $(obj).data('id'));
    params.append('count', count);

    ajaxPost('api/v1/permohonan/action_tld', params, result => {
        loadKontrol();
    })
}
