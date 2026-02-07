const periode = [];
const idPermohonan = $('#id_permohonan').val();
let arrKontrolTmp = [];
let typeLayanan = '';
let typeLayanan2 = '';
let datatable_ = false;
let arrListPengguna = [];
let JL = '';
let haveTldChecked = false;
let useZeroCek = true;

let inventoryTldPengguna = false;
let tmpArrTldPengguna = [];
let tmpArrTldKontrol = [];

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

let tldSelector,penggunaForm;

const optionsUploadKTP = {
    allowedFileExtensions: ['png', 'gif', 'jpeg', 'jpg']
};

// let ktpPenggunaUpload = false;
$(function () {
    inventoryTld = new Inventory_tld({preview: true});
    inventoryTld.on('inventory.selected', (e) => {
        const detail = e.detail;
        const split = detail.selected;

        const params = new FormData();

        if(detail.data_tld.jenis == 'pengguna'){
            let index = tmpArrTldPengguna.findIndex(d => d.index == split);
            if(index > -1){
                params.append('id', tmpArrTldPengguna[index].id);
            }
        } else {
            let index = tmpArrTldKontrol.findIndex(d => d.index == split);
            if(index > -1){
                params.append('id', tmpArrTldKontrol[index].id);
            }
        }

        params.append('id_tld', detail.data_tld.tld_hash);

        ajaxPost(`api/v1/permohonan/action_tld`, params, result => {
            if(detail.data_tld.jenis == 'pengguna'){
                loadPengguna();
            } else {
                loadKontrol();
            }
        });
    });

    tldSelector = new TldPenggunaSelector({
        apiUrl: `${base_url}/management/getDataPengguna`,
        type: 'selected'
    });
    penggunaForm = new PenggunaForm();

    $('#btn-add-pengguna').on('click', () => {
        tldSelector.show();
    });

    document.addEventListener('pengguna.request_create', () => {
        penggunaForm.showAdd(); // Buka form create kosong
    });

    document.addEventListener('pengguna.request_edit', (event) => {
        penggunaForm.showEdit(event.detail?.data?.id); // Buka form edit
    });

    document.addEventListener('pengguna.saved', (e) => {
        tldSelector.reload();
        tldSelector.show();
    })

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
        // formZeroCek.hide();

        $('#form-switch').hide();

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

        if(dataPermohonan.layanan_jasa) return;

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

    $('#simpanDraf').on('click', obj => {
        let valjenisTld = $('#jenis_tld').val();
        let valperiodePemakaian = $('#periode-pemakaian').attr('data-periode');
        let valPeriodeNext = $('#periode_next').attr('data-periode');
        let valjumPengguna = $('#jum_pengguna').val();
        let valjumKontrol = $('#jum_kontrol').val();
        let valAlamat = $('#selectAlamat').val();
        let valtotalHarga = $('#total_harga').val();
        let valHargaLayanan = window.price;
        let haveTld = $('#haveTld').is(':checked');
        let useZeroCek = $('#useZeroCek').is(':checked');

        Swal.fire({
            text: "Simpan permohonan sebagai draf?",
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

                formData.append('alamat', valAlamat);
                formData.append('tipeKontrak', 'kontrak baru');
                formData.append('jenisTld', valjenisTld);
                formData.append('periodePemakaian', valperiodePemakaian);
                formData.append('periodeNext', valPeriodeNext);
                formData.append('jumlahPengguna', valjumPengguna);
                formData.append('jumlahKontrol', valjumKontrol);
                formData.append('hargaLayanan', valHargaLayanan);
                formData.append('totalHarga', valtotalHarga);
                formData.append('haveTld', haveTld ? 1 : 0);
                formData.append('is_zerocek', useZeroCek ? 1 : 0);
                formData.append('note', '');
                formData.append('status', 80);

                if(haveTld && useZeroCek) {
                    formData.append('periode', 1);
                } else {
                    formData.append('periode', useZeroCek ? 0 : 1);
                }

                spinner('show', obj.target);
                ajaxPost(`api/v1/permohonan/tambahPengajuan`, formData, result => {
                    Swal.fire({
                        icon: 'success',
                        text: 'Pengajuan disimpan sebagai draf',
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
        })
    })

    $('#simpanPengajuan').on('click', obj => {
        let valjenisTld = $('#jenis_tld').val();
        let valperiodePemakaian = $('#periode-pemakaian').attr('data-periode');
        let valPeriodeNext = $('#periode_next').attr('data-periode');
        let valjumPengguna = $('#jum_pengguna').val();
        let valjumKontrol = $('#jum_kontrol').val();
        let valAlamat = $('#selectAlamat').val();
        let valtotalHarga = $('#total_harga').val();
        let valHargaLayanan = window.price;
        let haveTld = $('#haveTld').is(':checked');
        let useZeroCek = $('#useZeroCek').is(':checked');

        dataPermohonan.pelanggan.perusahaan.alamat[valAlamat] ? valAlamat = dataPermohonan.pelanggan.perusahaan.alamat[valAlamat].alamat_hash : false;

        const sanityCek = [];

        const periodeNextShow = formPeriodeNext.is(':visible');
        if(periodeNextShow && !valPeriodeNext) sanityCek.push('Periode Selanjutnya');

        if (!valjenisTld) sanityCek.push('Jenis TLD');
        if (!valperiodePemakaian) sanityCek.push('Periode Pemakaian');
        if (valjumPengguna == 0) sanityCek.push('Jumlah Pengguna');
        // if (valjumKontrol == 0) sanityCek.push('Jumlah Kontrol');

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

                // formData.append('pic', valpic);
                // formData.append('noHp', valnoHp);
                formData.append('alamat', valAlamat);

                formData.append('tipeKontrak', 'kontrak baru');
                formData.append('jenisTld', valjenisTld);
                formData.append('periodePemakaian', valperiodePemakaian);
                formData.append('periodeNext', valPeriodeNext);
                formData.append('jumlahPengguna', valjumPengguna);
                formData.append('jumlahKontrol', valjumKontrol);
                formData.append('hargaLayanan', valHargaLayanan);
                formData.append('totalHarga', valtotalHarga);
                formData.append('haveTld', haveTld ? 1 : 0);
                formData.append('is_zerocek', useZeroCek ? 1 : 0);
                formData.append('note', '');

                if(haveTld && useZeroCek) {
                    formData.append('periode', 1);
                } else {
                    formData.append('periode', useZeroCek ? 0 : 1);
                }

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

    $('#btn-clear-periode-next').on('click', obj => {
        $('#periode_next').val('');
        $('#periode_next').attr('data-periode', '');
        $('#periode_next').attr('data-jumperiode', '');
        $('#btn-clear-periode-next').addClass('d-none').removeClass('d-block');
        periodeNextJs.addData([]);
    });

    $('#btn-buat-form').on('click', obj => {
        spinner('show', obj.target);
        const jenisLayanan = $('#jenis_layanan').val();
        const jenisLayanan2 = $('#jenis_layanan_2').val();
        const layananJasa = $('#layanan_jasa').val();
        typeLayanan = $('#jenis_layanan').find(':selected').text();
        typeLayanan2 = $('#jenis_layanan_2').find(':selected').text();
        JL = jenislayanan({name: $('#jenis_layanan').find(':selected').text()}, {name: $('#jenis_layanan_2').find(':selected').text()});

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

    $('#tanggal_lahir').flatpickr({
        enableTime: false,
        dateFormat: "Y-m-d"
    });

    $('#haveTld').on('change', obj => {
        let layananActive = jenislayanan({name: $('#jenis_layanan').find(':selected').text()}, {name: $('#jenis_layanan_2').find(':selected').text()});
        if (obj.target.checked) {
            $('#useZeroCek').prop('checked', false);
            if(StringZerocek == layananActive) {
                $('#useZeroCek').prop('checked', true);
            } else {
                $('#switch-zerocek').show();
            }
        } else {
            if(StringZerocek == layananActive) {
                $('#useZeroCek').prop('checked', true);
            } else {
                $('#useZeroCek').prop('checked', false);
                $('#switch-zerocek').hide();
            }
        }

        loadPengguna();
        loadKontrol();
    });

    document.addEventListener('pengguna.pilih', (event) => {
        const obj = event.detail.html;

        btnPilihPengguna(obj);
    })

    resetForm();
    // cek jika id_layanan sudah ada
    cekLayanan();
})
// js add periode
let getPeriode = $('#periode-pemakaian').attr('data-periode');
let getPeriodeNext = $('#periode_next').attr('data-periode');
const periodeJs = new Periode(getPeriode, {
    id_element: 1,
});
const periodeNextJs = new Periode(getPeriodeNext, {
    max: 1,
    textPeriode: 'Periode Berikutnya',
    id_element: 2
});

$('#btn-periode').on('click', obj => {
    periodeJs.show();
});

periodeJs.on('periode.simpan.1', simpanPeriode);

$('#btn-periode-next').on('click', obj => {
    periodeNextJs.show();
});

periodeNextJs.on('periode.simpan.2', simpanPeriodeNext);

function simpanPeriodeNext() {
    const dataPeriode = periodeNextJs.getData();
    if(dataPeriode){
        $('#periode_next').attr('data-periode', JSON.stringify(dataPeriode));
        if(dataPeriode.length == 1) {
            $('#periode_next').val(`${dateFormat(dataPeriode[0].start_date, 4)} - ${dateFormat(dataPeriode[0].end_date, 4)}`);
        } else {
            $('#periode_next').val(dataPeriode.length + ' Periode');
        }
        $('#periode_next').attr('data-jumperiode', dataPeriode.length);
        $('#btn-clear-periode-next').addClass('d-block').removeClass('d-none');
    }
}
function simpanPeriode() {
    const dataPeriode = periodeJs.getData();
    if(dataPeriode){
        $('#periode-pemakaian').attr('data-periode', JSON.stringify(dataPeriode));
        if(dataPeriode.length == 1) {
            $('#periode-pemakaian').val(`${dateFormat(dataPeriode[0].start_date, 4)} - ${dateFormat(dataPeriode[0].end_date, 4)}`);
        } else {
            $('#periode-pemakaian').val(dataPeriode.length + ' Periode');
        }
        $('#periode-pemakaian').attr('data-jumperiode', dataPeriode.length);
        $('#btn-clear-periode').addClass('d-block').removeClass('d-none');
    }

    calcPrice();
}

function loadPengguna(){
    let params = {
        idPermohonan: idPermohonan
    }
    tmpArrTldPengguna = [];
    let haveTld = $('#haveTld').is(':checked');
    ajaxGet(`api/v1/permohonan/listPengguna`, params, result => {
        if(result.meta.code == 200){
            let html = '';
            if(result.data){
                for (const [i, value] of result.data.entries()) {
                    let pengguna = value.entitas;
                    let fileKtp = pengguna.media_ktp ? `${base_url}/storage/${pengguna.media_ktp.file_path}/${pengguna.media_ktp.file_hash}` : '';

                    const dataCard = {
                        index: i,
                        idHash: value.permohonan_detail_hash,
                        name: pengguna.name,
                        divisi: pengguna.divisi?.name || '',
                        isCheckedEvaluasi: false,
                        radiasi: pengguna.radiasi?.map(r => r.nama_radiasi),
                        fileKtp: fileKtp,
                        no_seri_tld: value.tld?.no_seri_tld || '',
                        htmlDisabled: true
                    }

                    html += cardPenggunaComponent(dataCard, {
                        is_have_tld: (tmpArrEvaluasi.includes(JL) || StringZerocek == JL) && haveTld,
                        status: value.type,
                        label_tld: false
                    });

                    tmpArrTldPengguna.push({
                        id: value.permohonan_detail_hash,
                        index: `tldNoSeri_${i}_pengguna`
                    });
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
            showPopupReload();
        }
    });
}
function removePengguna(obj){
    let idPengguna = $(obj).data('id');

    ajaxDelete(`api/v1/permohonan/destroyPengguna/${idPengguna}/${idPermohonan}`, result => {
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
    let idDivisi = $(obj).data('id');

    if(!idDivisi){
        idDivisi = 'default';
    }
    ajaxDelete(`api/v1/permohonan/destroyKontrol/${idPermohonan}/${idDivisi}`, result => {
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
    let haveTld = $('#haveTld').is(':checked');
    tmpArrTldKontrol = [];
    ajaxGet(`api/v1/permohonan/loadTld`, {idPermohonan: idPermohonan}, result => {
        // mengambil data kontrol
        arrKontrolTmp = result.data.tldPermohonan;
        let jumKontrol = 0;
        for (const [key, value] of Object.entries(arrKontrolTmp)) {
            let firstData = value[0];

            let data = {
                index: key,
                name: `Kontrol ${firstData.entitas?.name ?? ''} C`,
                kode: 'C',
                isCheckedEvaluasi: false,
                tldHash: firstData.id_pengguna_divisi,
                no_seri_tld: firstData.tld?.no_seri_tld || false,
                htmlDisabled: true,
                rincian: value
            };

            html += cardKontrolComponent(data, {
                is_btn_remove: true,
                label_tld: false,
                add_kontrol: true,
                is_have_tld: (tmpArrEvaluasi.includes(JL) || StringZerocek == JL) && haveTld
            });

            value.map((info, i) => {
                tmpArrTldKontrol.push({
                    id: info.permohonan_detail_hash,
                    index: `tldNoSeri_${key}_${i}_kontrol`
                });
            })

            jumKontrol += value.length;
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
    $('#modal-add-kontrol').modal('show');

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
    let arr = [];
    if(jenis == 'pengguna'){
        arr = tmpArrTldPengguna;
    } else if(jenis == 'kontrol'){
        arr = tmpArrTldKontrol;
    }
    inventoryTld.show(id, arr, jenis);
}

function btnPilihPengguna(obj){
    let id = $(obj).length > 0 ? $(obj).data('id') : obj;

    const params = new FormData();
    params.append('idPengguna', id);
    params.append('idPermohonan', idPermohonan);
    spinner('show', $(obj));
    ajaxPost(`api/v1/permohonan/tambahPengguna`, params, result => {
        loadPengguna();
        loadKontrol();
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
    $('#form-kode-lencana-pengguna').hide();
    arrKontrolTmp = [];

    // disable untuk form jenisLayanan, jenisLayanan2, dan layananJasa
    $('#jenis_layanan').attr('disabled', true).addClass('bg-secondary-subtle');
    $('#jenis_layanan_2').attr('disabled', true).addClass('bg-secondary-subtle');
    $('#layanan_jasa').attr('disabled', true).addClass('bg-secondary-subtle');

    if(layanan == ''){
        $('#form-inputan').addClass('d-none').removeClass('d-block');
    }else{
        ajaxGet(`api/v1/permohonan/getJenisTld/${layanan}`, false, result => {
            if(result.meta.code == 200){
                let list = result.data;

                list.forEach(value => {
                    html += `<option value="${value.jenis_tld.jenis_tld_hash}">${value.jenis_tld.name}</option>`
                });

                $('#jenis_tld').html(html);
                if(tmpArrSewa.includes(JL)) {
                    $('#useZeroCek').prop('checked', true);
                    $('#haveTld').prop('checked', false);
                    loadKontrol();
                    loadPengguna();
                } else {
                    dataPermohonan.is_zerocek == 0 ? $('#useZeroCek').prop('checked', false).trigger('change') : $('#useZeroCek').prop('checked', true).trigger('change');
                    dataPermohonan.is_have_tld == 0 ? $('#haveTld').prop('checked', false).trigger('change') : $('#haveTld').prop('checked', true).trigger('change');
                    // $('#useZeroCek').prop('checked', false);
                    // $('#haveTld').prop('checked', true);
                    $('#switch-zerocek').show();
                    $('#form-switch').show();
                }

                switch (typeLayanan.toLowerCase()) {
                    case 'kontrak':
                        // load data
                        $('#jenis_tld').val(dataPermohonan.jenis_tld?.jenis_tld_hash).trigger('change');
                        periodeJs.addData(dataPermohonan.periode_pemakaian);
                        simpanPeriode();

                        // show
                        btnAddPengguna.addClass('d-block').removeClass('d-none');
                        formTipeKontrak.show();
                        formPeriode.show();
                        formJenisTld.show();
                        formJumPengguna.show();
                        formJumKontrol.show();
                        formTotalHarga.show();
                        break;
                    case 'evaluasi':
                        // load data
                        $('#jenis_tld').val(dataPermohonan.jenis_tld?.jenis_tld_hash).trigger('change');
                        periodeJs.addData(dataPermohonan.periode_pemakaian);
                        simpanPeriode();
                        periodeNextJs.addData(dataPermohonan.periode_next);
                        simpanPeriodeNext();

                        // show
                        formTipeKontrak.show();
                        formPeriode.show();
                        formJenisTld.show();
                        formJumKontrol.show();
                        formJumPengguna.show();
                        formAlamat.show();
                        formTotalHarga.show();
                        formPeriodeNext.show();
                        periodeJs.maxPeriode = 1;
                        formJenisTld.addClass('col-md-12').removeClass('col-md-6');
                        $('#form-switch').hide();
                        break;
                    case 'zero cek':
                        formTipeKontrak.show();
                        formPeriode.show();
                        formJenisTld.show();
                        formJumKontrol.show();
                        formJumPengguna.show();
                        formAlamat.show();
                        formTotalHarga.show();
                        periodeJs.maxPeriode = 2;
                        $('#useZeroCek').prop('checked', true);
                        $('#switch-zerocek').hide();
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

                $('#form-inputan').addClass('d-block').removeClass('d-none');
            }
        });
    }
    return;
}

function cekLayanan(){
    if(dataPermohonan.layanan_jasa){
        $('#id_layanan').val(dataPermohonan.layanan_jasa.layanan_hash).trigger('change');
        $('#jenis_layanan').val(dataPermohonan.jenis_layanan_parent.jenis_layanan_hash).trigger('change');
        $('#jenis_layanan_2').html(`<option value="${dataPermohonan.jenis_layanan.jenis_layanan_hash}">${dataPermohonan.jenis_layanan.name}</option>`);

        typeLayanan = dataPermohonan.jenis_layanan_parent.name;
        typeLayanan2 = dataPermohonan.jenis_layanan.name;
        JL = jenislayanan(dataPermohonan.jenis_layanan_parent, dataPermohonan.jenis_layanan);


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
    let id = $(obj).data('id');
    const params = new FormData();
    if(type == 'plus'){
        params.append('aksi', 'tambah');
    } else {
        if(count == 1){
            Swal.fire({
                icon: 'warning',
                text: 'Kontrol tidak bisa dihapus',
                timer: 1200,
                timerProgressBar: true,
                showConfirmButton: false
            })
            return;
        }
        params.append('aksi', 'hapus');
    }

    params.append('id_permohonan', idPermohonan);
    if(id)
        params.append('id_divisi', id);

    params.append('jenis', 'kontrol');
    params.append('type', 'baru');

    ajaxPost('api/v1/permohonan/action_tld', params, result => {
        loadKontrol();
    })
}
