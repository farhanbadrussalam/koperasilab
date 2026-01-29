let periodeJs = false;
let arrOption = {
    periode: [],
    pengguna: [],
    note: false
};
let arr_pengguna = [];
let arr_kontrol = [];
let inventoryTld = false;

$(function () {
    loadPeriode();
    loadKontrakTld();

    inventoryTld = new Inventory_tld({preview: true});
    inventoryTld.on('inventory.selected', (e) => {
        console.log(e);
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
    let htmlPengguna = '';
    // load tld pengguna
    for (const [i, value] of arr_pengguna.entries()) {
        // Radiasi
        let radiasi = value.pengguna.radiasi?.map(d => d.nama_radiasi);

        let fileKtp = value.pengguna.media_ktp ? `${base_url}/storage/${value.pengguna.media_ktp.file_path}/${value.pengguna.media_ktp.file_hash}` : '';

        let data = {
            index: i,
            idHash: value.kontrak_tld_hash,
            name: value.pengguna.name,
            divisi: value.pengguna.divisi?.name || '',
            isCheckedEvaluasi: false,
            radiasi: radiasi,
            fileKtp: fileKtp,
            no_seri_tld: value.tld[0].no_seri_tld,
            htmlDisabled: true
        };

        htmlPengguna += cardPenggunaComponent(data, {
            status: 'adendum'
        });
    }

    $('#tld-pengguna').html(htmlPengguna);
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

function btnPilihPengguna(obj) {
    let id = $(obj).data('id');

    arrOption.pengguna.push({
        status: 'baru',
        id: id,
        target: false,
        id_tld: false
    });

    ajaxGet(`api/v1/pengguna/getDataById/${id}`, false, result => {
        console.log(result);
        let lengthPengguna = arrOption.pengguna.length;
        let data = {
            index: arr_pengguna.length + lengthPengguna,
            name: result.data.name,
            divisi: result.data.divisi?.name || '',
            isCheckedEvaluasi: false,
            radiasi: result.data.radiasi?.map(d => d.nama_radiasi),
            fileKtp: result.data.media_ktp ? `${base_url}/storage/${result.data.media_ktp.file_path}/${result.data.media_ktp.file_hash}` : '',
            no_seri_tld: false,
            htmlDisabled: true,
            idHash: result.data.kontrak_tld_hash
        };

        $('#tld-pengguna').append(cardPenggunaComponent(data, {
            status: 'baru',
            id: id,
            target: false,
            id_tld: false
        }));

        $('#modal-add-tld-pengguna').modal('hide');
    })
}

function openInventory(obj, jenis){
    let id = $(obj).data('id');
    inventoryTld.show(id, [], jenis);
}
