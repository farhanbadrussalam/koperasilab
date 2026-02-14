let periodeJs = false;
let arrOption = {
    periode: [],
    pengguna: [],
    kontrol: [],
    subTotal: 0
};
let arr_pengguna = [];
let arr_kontrol = [];
let tmpArrTld = [];
let pengguna_selected = [];
let pengguna_old = false;

let tldSelector;

$(function () {
    loadKontrakTld();

    $('#btn-periode').on('click', obj => {
        $('#modal-pilih-periode').modal('show');

        loadPeriode();
    });

    $('#btn-clear-periode').on('click', obj => {
        $('#periode-pemakaian').val('');
        $('#periode-pemakaian').attr('data-periode', '');
        $('#periode-pemakaian').attr('data-jumperiode', '');
        $('#btn-clear-periode').addClass('d-none').removeClass('d-block');
    });

    $('#btn-add-pengguna').click(() => {
        tldSelector.show(pengguna_selected);
    })

    $('#btn-add-kontrol').click(() => {
        arrOption.kontrol.push({
            kontrol: '',
            id: ''
        })
    })

    $('#modal-pilih-periode').on('hide.bs.modal', () => {
        $("#content-pilih-periode").html('');
    })

    tldSelector = new TldPenggunaSelector({
        apiUrl: `${base_url}/management/getDataPengguna`,
        type: 'selected'
    });

    penggunaForm = new PenggunaForm();

    // B. Jika user klik "Buat Baru" di dalam modal pencarian
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

    document.addEventListener('pengguna.pilih', (event) => {
        const obj = event.detail.html;
        btnPilihPengguna(obj);
    })

    document.addEventListener('pengguna.hide', (event) => {
        pengguna_old = false;
    })
});

function simpanAdendum(obj){
    const note = $('#catatan').val();

    // Validasi
    if(arrOption.periode.length == 0){
        return Swal.fire({
            icon: 'warning',
            text: 'Tolong pilih periode!'
        })
    }

    const arrPengguna = arrOption.pengguna
        .filter(d => d.status != 'lama')
        .map(value => ({
            pengguna: value.pengguna.pengguna_hash,
            pengguna_baru: value.pengguna_baru?.pengguna_hash,
            status: value.status
        }));

    const arrKontrol = arrOption.kontrol
        .filter(d => d.status != 'lama')
        .map(value => ({
            kontrol: value.kontrol.kontrol_hash,
            kontrol_baru: value.kontrol_baru?.kontrol_hash,
            status: value.status
        }));

    const periode = arrOption.periode;
    const subTotal = arrOption.subTotal;

    const params = new FormData();
    params.append('note', note);
    params.append('pengguna', JSON.stringify(arrPengguna));
    params.append('kontrol', JSON.stringify(arrKontrol));
    params.append('idPeriode', periode.periode_hash);
    params.append('sub_total', subTotal);
    params.append('id_kontrak', dataKontrak.kontrak_hash);

    spinner('show', $(obj));
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
                ajaxPost('api/v1/permohonan/tambahAdendum', params, result => {
                    Swal.fire({
                        icon: 'success',
                        text: 'Adendum berhasil disimpan',
                        timer: 1200,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = base_url+"/permohonan/pengajuan";
                    })
                }, error => {
                    spinner('hide', $(obj));
                })
            } else {
                spinner('hide', $(obj));
            }
        })
}

function simpanPeriode(){
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
}

/**
 * Load periode list in modal select periode
 * @param {Element} listPeriode - Element of periode list
 * @param {Object} periode - Object of periode data
 */
function loadPeriode(){
    const listPeriode = document.getElementById("content-pilih-periode");
    const periode = dataKontrak.periode;
    let aktifPeriode = false;
    let html = '';

    periode.forEach((data, index) => {
        if(data.periode == 0) return;

        let htmlAktif = '';
        let is_aktif = false;

        if(!data.selesai && !aktifPeriode){
            aktifPeriode = true;
            is_aktif = true;
            htmlAktif = `<span class="badge bg-info-subtle text-dark">Aktif</span>`;
        }

        const btnPilih = (arrOption.periode && arrOption.periode.periode == data.periode)
            ? '<span class="text-muted">Dipilih</span>'
            : `<button type="button" class="btn btn-sm btn-primary ${is_aktif ? 'd-none' : 'd-block'}" data-periode="${index}" data-aktif="${is_aktif}" onclick="pilihPeriode(this)">Pilih</button>`;

        html += `
            <div class="d-flex justify-content-between align-items-center p-2 mb-2 bg-light rounded-3 border mt-1">
                <div>
                    <span class="badge bg-secondary mb-1">Periode ${data.periode}</span>
                    ${htmlAktif}
                    <div class="small fw-bold text-dark">${dateFormat(data.start_date, 4)} - ${dateFormat(data.end_date, 4)}</div>
                </div>
                ${btnPilih}
            </div>
        `;
    });

    listPeriode.innerHTML = html;
}

function loadKontrakTld(){
    let params = {
        id_kontrak : dataKontrak.kontrak_hash
    }

    ajaxGet(`api/v1/kontrak/getKontrakTld`, params, result => {
        // pisahkan pengguna dan kontrol
        arr_pengguna = result.data.filter(d => d.jenis == 'pengguna');
        arr_kontrol = result.data.filter(d => d.jenis == 'kontrol');

        loadPengguna();
        loadKontrol();

        showPopupReload();
    });
}

function loadPengguna(){
    // load tld pengguna
    for (const [i, value] of arr_pengguna.entries()) {
        arrOption.pengguna.push({
            status: 'lama',
            pengguna: value.entitas,
            pengguna_baru: false
        });
    }

    loadHtmlPengguna();
}

/**
 * Load tld kontrol into the page.
 *
 * This function will load tld kontrol from the given data into the page.
 * It will generate the html for the tld kontrol list.
 * The html will be inserted into the "#tld-kontrol" element.
 *
 * @param {Array} arr_kontrol - array of tld kontrol data
 */
function loadKontrol(){
    arr_kontrol.map((value, idx) => {
        arrOption.kontrol.push({
            status: 'lama',
            divisi: value.entitas?.name ?? '',
            id: value.kontrak_detail_hash
        });
    }).join('');

    loadHtmlKontrol();
}

/**
 * Load pengguna into the page.
 *
 * This function will load pengguna from the given data into the page.
 * It will generate the html for the pengguna list.
 * The html will be inserted into the "#tld-pengguna" element.
 *
 * @param {Array} arrOption.pengguna - array of pengguna data
 * @return {String} htmlPengguna - the generated html for the pengguna list
 */
function loadHtmlPengguna(){
    tmpArrTld = [];
    const htmlPengguna = arrOption.pengguna.map((value, i) => {
        let pengguna = value.pengguna;

        const fileKtp = pengguna.media_ktp
            ? `${base_url}/storage/${pengguna.media_ktp.file_path}/${pengguna.media_ktp.file_hash}`
            : '';

        const findPergantian = arrOption.pengguna.find(d => d.status == 'ganti' && d.pengguna.pengguna_hash == pengguna.pengguna_hash);
        if(value.status != 'ganti'){
            const data = {
                index: i,
                idHash: pengguna.pengguna_hash,
                name: pengguna.name,
                divisi: pengguna.divisi?.name || '',
                isCheckedEvaluasi: false,
                radiasi: pengguna.radiasi?.map(d => d.nama_radiasi),
                fileKtp: fileKtp,
                htmlDisabled: true,
                pengguna_baru: findPergantian?.pengguna_baru || false,
            };

            return cardPenggunaComponent(data, {
                status: value.status,
                is_have_tld: false,
                label_tld: false,
                is_adendum: true
            });
        }
    }).join('');

    $('#tld-pengguna').html(htmlPengguna);

    showPopupReload();
    calcPrice();
}

function loadHtmlKontrol(){
    const htmlKontrol = arrOption.kontrol.map((value, idx) => {
        const kodeLencana = idx >= 1 ? `C${idx}` : 'C';
        const data = {
            name: `Kontrol ${value.divisi} ${kodeLencana}`,
            kode: kodeLencana,
            index: idx,
            tldHash: value.id,
            htmlDisabled: true
        };

        return cardKontrolComponent(data, {
            label_tld: false
        });
    })

    $('#tld-kontrol').html(htmlKontrol);
    calcPrice();
}

function btnPilihPengguna(obj) {
    let id = $(obj).data('id');

    const data = arrOption.pengguna.find(v => v.pengguna.pengguna_hash == id)

    if(!data){
        ajaxGet(`api/v1/pengguna/getDataById/${id}`, false, result => {
            let params = {};

            if(pengguna_old){
                // hapus pengguna baru yang double
                arrOption.pengguna.findIndex((value, index) => {
                    if(value?.pengguna?.pengguna_hash == pengguna_old.pengguna_hash && value.status == 'ganti'){
                        pengguna_selected.findIndex((value_2, index) => {
                            if(value_2 == value.pengguna_baru.pengguna_hash){
                                pengguna_selected.splice(index, 1);
                                return false;
                            }
                        })
                        arrOption.pengguna.splice(index, 1);
                        return false;
                    }
                })

                params = {
                    status: 'ganti',
                    pengguna: pengguna_old,
                    pengguna_baru: result.data
                }
            } else {
                params = {
                    status: 'baru',
                    pengguna: result.data,
                    pengguna_baru: false
                }

            }

            arrOption.pengguna.push(params);

            $('#modal-add-tld-pengguna').modal('hide');

            pengguna_selected.push(result.data.pengguna_hash);
            loadHtmlPengguna();
        })
    } else {
        $('#modal-add-tld-pengguna').modal('hide');
    }
}

function removePengguna(obj) {
    let id = $(obj).data('id');

    arrOption.pengguna.findIndex((value, index) => {
        if(value){
            if(value.pengguna.pengguna_hash == id){
                arrOption.pengguna.splice(index, 1);
            }
        }
    })

    pengguna_selected.findIndex((value, index) => {
        if(value == id){
            pengguna_selected.splice(index, 1);
        }
    })
    loadHtmlPengguna();
}

function pilihPeriode(obj){
    const index = $(obj).data('periode');
    const is_aktif = $(obj).data('aktif');
    const periode = dataKontrak.periode[index];

    arrOption.periode = periode;
    $('#periode-pemakaian').val(`Periode ${periode.periode} (${dateFormat(periode.start_date, 4)} - ${dateFormat(periode.end_date, 4)})`);

    if(periode.selesai || is_aktif){
        $('#btn-add-pengguna').removeClass('d-block').addClass('d-none');
        // $('#btn-add-kontrol').removeClass('d-block').addClass('d-none');
        arrOption.pengguna = arrOption.pengguna.filter(d => d.status != 'baru');
        loadHtmlPengguna();
    } else {
        $('#btn-add-pengguna').removeClass('d-none').addClass('d-block');
        // $('#btn-add-kontrol').removeClass('d-none').addClass('d-block');
    }


    $('#modal-pilih-periode').modal('hide');
}

function gantiPengguna(obj){
    let id = $(obj).data('id');

    let find = arrOption.pengguna.find(d => d.pengguna.pengguna_hash == id);

    pengguna_old = find.pengguna;
    tldSelector.show(pengguna_selected);
}

function deletePergantian(obj){
    let id = $(obj).data('id');
    arrOption.pengguna.findIndex((value, index) => {
        if(value.pengguna_baru.pengguna_hash == id){
            arrOption.pengguna.splice(index, 1);
        }
    })
    pengguna_selected.findIndex((value, index) => {
        if(value == id){
            pengguna_selected.splice(index, 1);
        }
    })
    loadHtmlPengguna();
}

function calcPrice(){
    let hargaLayanan = Number(dataKontrak.harga_layanan);
    let jumPengguna = arrOption.pengguna.filter(d => d.status == 'baru').length;
    let jumKontrol = arrOption.kontrol.filter(d => d.status == 'baru').length;
    let jumlahPenambahan = jumPengguna + jumKontrol;
    let jumPeriode = dataKontrak.periode_all.jml_periode;

    let subTotal = hargaLayanan * jumPeriode;

    subTotal *= jumlahPenambahan;
    $('#total-harga').html(formatRupiah(subTotal));

    arrOption.subTotal = subTotal;
}
