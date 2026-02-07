let periodeJs = false;
let arrOption = {
    periode: [],
    pengguna: [],
    note: false
};
let arr_pengguna = [];
let arr_kontrol = [];
let inventoryTld = false;
let tmpArrTld = [];
let periode_select = false;

let tldSelector;

$(function () {
    loadKontrakTld();

    inventoryTld = new Inventory_tld({preview: true});
    inventoryTld.on('inventory.selected', (e) => {
        const detail = e.detail;

        $(`#${detail.selected}`).val(detail.data_tld.no_seri_tld);
        $(`#${detail.selected}_view`).html(detail.data_tld.no_seri_tld);

        // reset tmpArrTld
        let index = tmpArrTld.findIndex(d => d.index == detail.selected);

        if(index > -1){
            tmpArrTld[index].tld = detail.data_tld.tld_hash;
        }
    });

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
        tldSelector.show();
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
});

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

        const btnPilih = (periode_select && periode_select.periode == data.periode)
            ? '<span class="text-muted">Dipilih</span>'
            : `<button type="button" class="btn btn-sm btn-primary" data-periode="${index}" data-aktif="${is_aktif}" onclick="pilihPeriode(this)">Pilih</button>`;

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
        arr_pengguna = result.data.filter(tld => tld.pengguna);
        arr_kontrol = result.data.filter(tld => !tld.pengguna);

        loadPengguna();
        loadKontrol();

        showPopupReload();
    });
}

function loadPengguna(){
    // load tld pengguna
    for (const [i, value] of arr_pengguna.entries()) {
        arrOption.pengguna.push({
            status: 'stay',
            is_have_tld: dataKontrak.is_have_tld,
            detail_lama: {
                pengguna: value.pengguna,
                tld: value.tld
            },
            detail_baru: {
                pengguna: false,
                tld: false
            }
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
    const htmlKontrol = arr_kontrol.map(value => {
        return Array.from({ length: value.count }, (_, idx) => {
            const tld = value.tld?.[idx];
            const no_seri_tld = tld?.no_seri_tld || '';
            const kodeLencana = value.count > 1 ? `C${idx + 1}` : 'C';

            const data = {
                name: `Kontrol ${value.divisi?.name ?? ''} ${kodeLencana}`,
                kode: kodeLencana,
                index: idx,
                tldHash: value.kontrak_tld_hash,
                no_seri_tld: no_seri_tld,
                htmlDisabled: true
            };

            return cardKontrolComponent(data);
        }).join('');
    }).join('');

    $('#tld-kontrol').html(htmlKontrol);
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
        let pengguna = false;
        let tld = false;
        switch (value.status) {
            case 'baru':
                pengguna = value.detail_baru.pengguna;
                tld = value.detail_baru.tld;
                break;
            case 'stay':
                pengguna = value.detail_lama.pengguna;
                tld = value.detail_lama.tld;
                break;
        }
        const fileKtp = pengguna.media_ktp
            ? `${base_url}/storage/${pengguna.media_ktp.file_path}/${pengguna.media_ktp.file_hash}`
            : '';

        const data = {
            index: i,
            idHash: pengguna.pengguna_hash,
            name: pengguna.name,
            divisi: pengguna.divisi?.name || '',
            isCheckedEvaluasi: false,
            radiasi: pengguna.radiasi?.map(d => d.nama_radiasi),
            fileKtp: fileKtp,
            no_seri_tld: tld?.[0]?.no_seri_tld || '',
            htmlDisabled: true
        };

        tmpArrTld.push({
            id: `${data.idHash}|${i + 1}`,
            tld: tld?.[0]?.tld_hash || '',
            index: `tldNoSeri_${i}_pengguna`
        });

        return cardPenggunaComponent(data, {
            status: value.status,
            is_have_tld: value.is_have_tld
        });
    }).join('');

    $('#tld-pengguna').html(htmlPengguna);

    showPopupReload();
}

function btnPilihPengguna(obj) {
    let id = $(obj).data('id');

    const data = arrOption.pengguna.find(v => v.id == id)

    if(!data){
        ajaxGet(`api/v1/pengguna/getDataById/${id}`, false, result => {
            let params = {};

            params = {
                status: 'baru',
                is_have_tld: dataKontrak.is_have_tld,
                detail_lama: {
                    pengguna: false,
                    tld: false
                },
                detail_baru: {
                    pengguna: result.data,
                    tld: false
                }
            }
            arrOption.pengguna.push(params);

            $('#modal-add-tld-pengguna').modal('hide');

            loadHtmlPengguna();
        })
    } else {
        $('#modal-add-tld-pengguna').modal('hide');
    }
}

function openInventory(obj, jenis){
    let id = $(obj).data('id');
    inventoryTld.show(id, tmpArrTld, jenis);
}

function removePengguna(obj) {
    let id = $(obj).data('id');

    tmpArrTld.findIndex((value, index) => {
        let idAdd = value.id.split('|')[0];
        if(idAdd == id){
            tmpArrTld.splice(index, 1);
        }
    })

    arrOption.pengguna.findIndex((value, index) => {
        if(value.id == id){
            arrOption.pengguna.splice(index, 1);
        }
    })
    loadHtmlPengguna();
}

function pilihPeriode(obj){
    const index = $(obj).data('periode');
    const is_aktif = $(obj).data('aktif');
    const periode = dataKontrak.periode[index];

    periode_select = periode;
    $('#periode-pemakaian').val(`Periode ${periode.periode} (${dateFormat(periode.start_date, 4)} - ${dateFormat(periode.end_date, 4)})`);

    if(periode.selesai || is_aktif){
        $('#btn-add-pengguna').removeClass('d-block').addClass('d-none');
        arrOption.pengguna = arrOption.pengguna.filter(d => d.status != 'baru');
        loadHtmlPengguna();
    } else {
        $('#btn-add-pengguna').removeClass('d-none').addClass('d-block');
    }


    $('#modal-pilih-periode').modal('hide');
}

function gantiPengguna(obj){
    let id = $(obj).data('id');

}
