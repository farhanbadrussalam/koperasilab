/**
 * Initializes the page by loading the first tab.
 */
let detail = false;
let buktiPengiriman = false;
let filterComp = false;
let isUpdateMode = false;
$(function () {
    loadData(1);
    detail = new Detail({
        jenis: 'pengiriman',
        tab: {
            items: true,
            bukti: true,
            log: true,
            dokumen: true
        }
    });

    filterComp = new FilterComponent('list-filter', {
        jenis: 'pengiriman',
        filter : {
            search: true,
            no_kontrak : true
        }
    });
    // SETUP FILTER
    filterComp.on('filter.change', () => loadData());

    buktiPengiriman = new UploadComponent('uploadBuktiPengiriman', {
        camera: false,
        allowedFileExtensions: ['png', 'gif', 'jpeg', 'jpg']
    });

    $('#modal-kirim-dokumen').on('hidden.bs.modal', function (e) {
        resetModal();
    });
});

/**
 * Loads data for the specified page and menu.
 * @param {number} [page=1] - The page number to load.
 * @param {string} menu - The menu type to load data for.
 */
function loadData(page = 1) {
    let params = {
        limit: 10,
        page: page,
        filter: {}
    };
    let filterValue = filterComp && filterComp.getAllValue();

    filterValue.search && (params.filter.search = filterValue.search);
    filterValue.no_kontrak && (params.filter.id_kontrak = filterValue.no_kontrak);
    if(Object.keys(params.filter).length > 0) {
        $('#countFilter').html(Object.keys(params.filter).length);
        $('#countFilter').removeClass('d-none');
    } else {
        $('#countFilter').addClass('d-none');
    }

    $(`#list-placeholder-pengiriman`).show();
    $(`#list-container-pengiriman`).hide();
    ajaxGet(`api/v1/pengiriman/list`, params, result => {
        let html = '';
        let btnUpdateResi = `<button class="btn btn-outline-warning btn-sm" onclick="showFormUpdateResi(this)">Update Resi</button>`;
        for (const [i, data] of result.data.entries()) {
            let htmlButton = `<button class="btn btn-outline-info btn-sm" onclick="showDetail(this)">Detail</button>`;

            if(data.status == 3){
                htmlButton += `<button class="btn btn-outline-primary btn-sm" onclick="showFormPengiriman(this)">Kirim</button>`;
                htmlButton += `<button class="btn btn-outline-danger btn-sm" onclick="removePengiriman(this)">Delete</button>`;
            } else if(data.status == 1) {
                htmlButton += btnUpdateResi;
                htmlButton += `<button class="btn btn-outline-danger btn-sm" onclick="batalKirimDokumen(this)">Batal kirim</button>`;
            } else if(data.status == 2) {
                if(data.no_resi == null) {
                    htmlButton += btnUpdateResi;
                }
            }

            const dataCard = {
                id: data.id_pengiriman,
                format: 'pengiriman',
                status: data.status,
                created_at: data.created_at,
                kontrak: data.kontrak?.no_kontrak ?? '',
                title: data.id_pengiriman,
                no_resi : data.no_resi,
                pelanggan: data.kontrak.pelanggan.name,
                items: data.detail,
                alamat: data.alamat,
                perusahaan: data.kontrak.pelanggan.perusahaan.nama_perusahaan
            }

            html += cardComponent(dataCard, {
                btnAction: htmlButton
            });
        }
        if(result.data.length == 0){
            html = htmlNoData();
        }

        $(`#list-container-pengiriman`).html(html);

        $(`#list-pagination-pengiriman`).html(createPaginationHTML(result.pagination));

        $(`#list-placeholder-pengiriman`).hide();
        $(`#list-container-pengiriman`).show();
    });
}

/**
 * Removes a pengiriman (shipment) by its ID.
 * @param {Object} obj - The DOM element that triggered the removal.
 */
function removePengiriman(obj){
    let idPengiriman = $(obj).parent().parent().data('id');
    ajaxDelete(`api/v1/pengiriman/destroy/${idPengiriman}`, result => {
        Swal.fire({
            icon: 'success',
            text: result.data.msg,
            timer: 1200,
            timerProgressBar: true,
            showConfirmButton: false
        }).then(() => {
            loadData(1);
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
    })
}

/**
 * Shows the detail modal for a pengiriman (shipment).
 */
function showDetailPengiriman(){
    $('#modal-detail-pengiriman').modal('show');
}

/**
 * Shows the form modal for sending a pengiriman (shipment).
 * @param {Object} obj - The DOM element that triggered the form display.
 */
function showFormPengiriman(obj){
    let idPengiriman = $(obj).parent().parent().data('id');
    $('#no_pengiriman').val(idPengiriman);

    isUpdateMode = false;
    $('#kirimDokumenModalLabel').text('Kirim Dokumen');
    $('#uploadBuktiPengiriman').parent().show(); // Show proof upload container
    $('#noResi').val('');
    $('#jasa_kurir').val('');

    $('#modal-kirim-dokumen').modal('show');
}

/**
 * Shows the form modal for updating a pengiriman (shipment) receipt info.
 * @param {Object} obj - The DOM element that triggered the form display.
 */
function showFormUpdateResi(obj){
    let idPengiriman = $(obj).parent().parent().data('id');
    $('#no_pengiriman').val(idPengiriman);

    isUpdateMode = true;
    $('#kirimDokumenModalLabel').text('Update No Resi / Jasa Kurir');
    $('#uploadBuktiPengiriman').parent().hide(); // Hide proof upload container

    // Fetch current details to pre-fill
    showLoadingSwal('show');
    ajaxGet(`api/v1/pengiriman/getById/${idPengiriman}`, false, res => {
        showLoadingSwal('hide');
        let data = res.data;
        $('#noResi').val(data.no_resi ?? '');
        if (data.ekspedisi) {
            $('#jasa_kurir').val(data.ekspedisi.ekspedisi_hash);
        } else {
            $('#jasa_kurir').val('');
        }
        $('#modal-kirim-dokumen').modal('show');
    }, error => {
        showLoadingSwal('hide');
        Swal.fire({ icon: 'error', text: 'Gagal mengambil data pengiriman' });
    });
}

/**
 * Sends or updates a pengiriman (shipment) document.
 * @param {Object} obj - The DOM element that triggered the action.
 */
function kirimDokumen(obj){
    let idPengiriman = $('#no_pengiriman').val();
    let noResi = $('#noResi').val();
    let idEkspedisi = $('#jasa_kurir').val();
    let arrImgBukti = buktiPengiriman.getData();

    // Check for empty fields and show warning if any
    if(!isUpdateMode && arrImgBukti.length == 0){
        Swal.fire({ icon: 'warning', text: 'Bukti pengiriman tidak boleh kosong' });
        return;
    }
    if (!idEkspedisi) {
        Swal.fire({ icon: 'warning', text: 'Ekspedisi tidak boleh kosong' });
        return;
    }

    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: isUpdateMode ? "Apakah Anda ingin memperbarui informasi pengiriman?" : "Apakah Anda ingin mengirim dokumen ini?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: isUpdateMode ? 'Ya, perbarui!' : 'Ya, kirim!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            let data = new FormData();
            data.append('idPengiriman', idPengiriman);
            data.append('noResi', noResi ?? '');
            data.append('idEkspedisi', idEkspedisi);
            if (!isUpdateMode) {
                data.append('status', 1);
                data.append('sendAt', new Date().toISOString());
                arrImgBukti.forEach((d) => {
                    data.append('buktiPengiriman[]', d.file);
                });
            }

            spinner('show', $(obj));
            ajaxPost(`api/v1/pengiriman/action`, data, result => {
                Swal.fire({
                    icon: 'success',
                    text: isUpdateMode ? 'Informasi berhasil diperbarui' : 'Dokumen berhasil dikirim',
                    timer: 1200,
                    timerProgressBar: true,
                    showConfirmButton: false
                }).then(() => {
                    $('#modal-kirim-dokumen').modal('hide');
                    spinner('hide', $(obj));
                    loadData(1);
                });
            }, error => {
                spinner('hide', $(obj));
            });
        }
    });
}

/**
 * Handles the cancellation of document delivery.
 *
 * This function triggers a confirmation dialog using Swal.fire to confirm the cancellation of a document delivery.
 * If confirmed, it sends an AJAX POST request to update the delivery status to cancelled.
 *
 * @param {Object} obj - The DOM element that triggered the function.
 *
 * @example
 * // Assuming `this` is the DOM element that triggered the function
 * batalKirimDokumen(this);
 *
 * @fires Swal.fire - To show confirmation and success/error messages.
 * @fires ajaxPost - To send the cancellation request to the server.
 */
function batalKirimDokumen(obj){
    let idPengiriman = $(obj).parent().parent().data('id');

    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Apakah Anda ingin membatalkan pengiriman dokumen ini?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, batalkan!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            let data = new FormData();
            data.append('idPengiriman', idPengiriman);
            data.append('status', 3);
            data.append('noResi', '');
            data.append('idEkspedisi', '');

            spinner('show', $(obj));
            ajaxPost(`api/v1/pengiriman/action`, data, result => {
                Swal.fire({
                    icon: 'success',
                    text: 'Pengiriman berhasil dibatalkan',
                    timer: 1200,
                    timerProgressBar: true,
                    showConfirmButton: false
                }).then(() => {
                    $('#modal-kirim-dokumen').modal('hide');
                    spinner('hide', $(obj));
                    loadData(1);
                });
            }, error => {
                spinner('hide', $(obj));
            });
        }
    });
}

function reload(){
    loadData(1);
}

// pagination
$('#list-pagination-pengiriman').on('click', 'a', function (e) {
    e.preventDefault();
    const pageno = e.target.dataset.page;
    loadData(pageno);
});

function showDetail(obj){
    const id = $(obj).parent().parent().data("id");
    detail.show(`api/v1/pengiriman/getById/${id}`);
}

function resetModal(){
    $('#noResi').val('');
    $('#jasa_kurir').val('');
    buktiPengiriman.clearFile();
}

function clearFilter(){
    filterComp.clear();
    loadData();
}
