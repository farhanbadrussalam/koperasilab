/**
 * Initializes the page by loading the first tab.
 */
let detail = false;
let buktiPengiriman = false;
$(function () {
    loadData(1);
    detail = new Detail({
        jenis: 'pengiriman',
        tab: {
            items: true,
            bukti: true
        }
    })

    buktiPengiriman = new UploadComponent('uploadBuktiPengiriman', {
        camera: false,
        allowedFileExtensions: ['png', 'gif', 'jpeg', 'jpg']
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
        page: page
    };

    $(`#list-placeholder-pengiriman`).show();
    $(`#list-container-pengiriman`).hide();
    ajaxGet(`api/v1/pengiriman/list`, params, result => {
        let html = '';
        for (const [i, data] of result.data.entries()) {
            let htmlButton = '';

            if(data.status == 3){
                htmlButton += `<button class="btn btn-outline-primary btn-sm me-1" onclick="showFormPengiriman(this)">Kirim</button>`;
                htmlButton += `<button class="btn btn-outline-danger btn-sm" onclick="removePengiriman(this)">Delete</button>`;
            } else if(data.status == 1) {
                htmlButton += `<button class="btn btn-outline-danger btn-sm me-1" onclick="batalKirimDokumen(this)">Batal kirim</button>`;
            }

            html += `
                <div class="card mb-2">
                    <div class="card-body row align-items-center py-2">
                        <div class="col-12 col-md-3">
                            <div class="fw-bolder">${data.id_pengiriman}</div>
                            <div class="fw-light">No resi : ${data.no_resi ?? 'Belum ada'}</div>
                            <small class="subdesc text-body-secondary fw-light lh-md">
                                <div>${data.kontrak?.no_kontrak ?? ''}</div>
                                <div>created at ${dateFormat(data.created_at, 1)}</div>
                            </small>
                        </div>
                        <div class="col-6 col-md-2">
                            ${data.detail.length} Item
                        </div>
                        <div class="col-6 col-md-2">
                            <small class="subdesc text-body-secondary fw-light lh-sm">
                                <div class="tooltip-container cursoron" data-bs-toggle="tooltip" data-bs-placement="top" title="${data.alamat.alamat}">
                                    Alamat ${data.alamat.jenis}
                                </div>
                            </small>
                        </div>
                        <div class="col-6 col-md-2 text-center">
                            ${statusFormat('pengiriman', data.status)}
                        </div>
                        <div class="col-6 col-md-3 text-center" data-id="${data.id_pengiriman}">
                            <button class="btn btn-outline-info btn-sm" onclick="showDetail(this)">Detail</button>
                            ${htmlButton}
                        </div>
                        <div class="col-md-12 mt-1">
                            <div class="text-body-tertiary fs-7">
                                <div><i class="bi bi-building-fill"></i> ${data.kontrak.pelanggan.perusahaan.nama_perusahaan}</div>
                            </div>
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
    let idPengiriman = $(obj).parent().data('id');
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
    let idPengiriman = $(obj).parent().data('id');
    $('#no_pengiriman').val(idPengiriman);
    $('#noResi').val('');

    $('#modal-kirim-dokumen').modal('show');
}

/**
 * Sends a pengiriman (shipment) document.
 * @param {Object} obj - The DOM element that triggered the send action.
 */
function kirimDokumen(obj){
    let idPengiriman = $('#no_pengiriman').val();
    let noResi = $('#noResi').val();
    let idEkspedisi = $('#jasa_kurir').val();

    // Check for empty fields and show warning if any
    if (!idEkspedisi) {
        Swal.fire({ icon: 'warning', text: 'Ekspedisi tidak boleh kosong' });
        return;
    }
    if (!noResi) {
        Swal.fire({ icon: 'warning', text: 'No resi tidak boleh kosong' });
        return;
    }

    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Apakah Anda ingin mengirim dokumen ini?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, kirim!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            let arrImgBukti = buktiPengiriman.getData();

            let data = new FormData();
            data.append('idPengiriman', idPengiriman);
            data.append('noResi', noResi);
            data.append('idEkspedisi', idEkspedisi);
            data.append('status', 1);
            data.append('sendAt', new Date().toISOString());
            arrImgBukti.forEach((d) => {
                data.append('buktiPengiriman[]', d.file);
            });

            spinner('show', $(obj));
            ajaxPost(`api/v1/pengiriman/action`, data, result => {
                Swal.fire({
                    icon: 'success',
                    text: 'Dokumen berhasil dikirim',
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
    let idPengiriman = $(obj).parent().data('id');

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
    const id = $(obj).parent().data("id");
    detail.show(`api/v1/pengiriman/getById/${id}`);
}
