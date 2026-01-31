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

$(function () {
    loadPeriode();
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

    periodeJs = new Periode(false, {
        id_element: 1,
        defaultDate: dataKontrak.periode[dataKontrak.periode.length - 1].end_date
    });

    $('#btn-periode').on('click', obj => {
        periodeJs.show();
    });

    periodeJs.on('periode.simpan.1', simpanPeriode);

    $('#btn-clear-periode').on('click', obj => {
        $('#periode-pemakaian').val('');
        $('#periode-pemakaian').attr('data-periode', '');
        $('#periode-pemakaian').attr('data-jumperiode', '');
        $('#btn-clear-periode').addClass('d-none').removeClass('d-block');
        periodeJs.addData([]);
    });

    $('#btn-add-pengguna').click(() => {
        $('#modal-add-tld-pengguna').modal('show');
    })

    document.addEventListener('pengguna.pilih', (event) => {
        const obj = event.detail;

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

function loadPeriode(){
    let listPeriode = document.getElementById("list-periode");
    let periode = dataKontrak.periode;

    periode.forEach((data, index) => {
        if(data.periode == 0) return;

        listPeriode.innerHTML += `
            <div class="d-flex justify-content-between align-items-center p-2 mb-2 bg-light rounded-3 border mt-1">
                <div>
                    <span class="badge bg-secondary mb-1">Periode ${data.periode}</span>
                    <div class="small fw-bold text-dark">${dateFormat(data.start_date, 4)} - ${dateFormat(data.end_date, 4)}</div>
                </div>
                <i class="bi bi-lock-fill text-secondary fs-5" title="Tidak dapat dihapus"></i>
            </div>
        `;
    })
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
            status: 'adendum',
            id: id,
            is_have_tld: dataKontrak.is_have_tld,
            target: false,
            id_tld: false,
            detail: value
        });
    }

    loadHtmlPengguna();
}

function loadKontrol(){
    let htmlKontrol = '';

    // load tld kontrol
    for (const [i, value] of arr_kontrol.entries()) {
        let tldHash = '';
        let no_seri_tld = '';

        for (let idx = 0; idx < value.count; idx++) {
            if(value.tld) {
                tldHash = value.tld[idx].tld_hash;
                no_seri_tld = value.tld[idx].no_seri_tld;
            } else {
                tldHash = '';
                no_seri_tld = '';
            }

            let kodeLencana = value.count > 1 ? `C${idx+1}` : 'C';

            let data = {
                name: `Kontrol ${value.divisi?.name ?? ''} ${kodeLencana}`,
                kode: kodeLencana,
                index: idx,
                tldHash: value.kontrak_tld_hash,
                no_seri_tld: no_seri_tld,
                htmlDisabled: true
            }

            htmlKontrol += cardKontrolComponent(data);
        }
    }

    $('#tld-kontrol').html(htmlKontrol);
}

function loadHtmlPengguna(){
    tmpArrTld = [];
    let htmlPengguna = '';
    for (const [i, value] of arrOption.pengguna.entries()) {
        let fileKtp = value.detail.pengguna.media_ktp ? `${base_url}/storage/${value.detail.pengguna.media_ktp.file_path}/${value.detail.pengguna.media_ktp.file_hash}` : '';

        let data = {
            index: i,
            idHash: value.detail.pengguna.pengguna_hash,
            name: value.detail.pengguna.name,
            divisi: value.detail.pengguna.divisi?.name || '',
            isCheckedEvaluasi: false,
            radiasi: value.detail.pengguna.radiasi?.map(d => d.nama_radiasi),
            fileKtp: fileKtp,
            no_seri_tld: value.detail.tld ? value.detail.tld[0].no_seri_tld : '',
            htmlDisabled: true
        };

        htmlPengguna += cardPenggunaComponent(data, {
            status: value.status,
            id: value.id,
            target: value.target,
            id_tld: value.id_tld,
            is_have_tld: value.is_have_tld
        });

        tmpArrTld.push({
            id: `${data.idHash}|${i+1}`,
            tld: value.detail.tld ? value.detail.tld[0].tld_hash : '',
            index: `tldNoSeri_${i}_pengguna`
        });
    }
    $('#tld-pengguna').html(htmlPengguna);

    showPopupReload();
}

function btnPilihPengguna(obj) {
    let id = $(obj).data('id');

    const data = arrOption.pengguna.find(v => v.id == id)

    if(!data){
        ajaxGet(`api/v1/pengguna/getDataById/${id}`, false, result => {
            arrOption.pengguna.push({
                status: 'baru',
                id: id,
                target: false,
                id_tld: false,
                is_have_tld: dataKontrak.is_have_tld,
                detail: {
                    pengguna: result.data
                }
            });

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
