let nowTab = 1;
const arrImgBukti = [];
$(function () {
    loadData(1);

    $('#btnTambahBukti').on('click', obj => {
        let imgtmp = $('#uploadBuktiPenerima')[0].files[0];

        if(imgtmp && arrImgBukti.length < 5){
            spinner('show', $(obj.target));
            
            arrImgBukti.push(imgtmp);
            loadPreviewBukti()
            spinner('hide', $(obj.target));
            $('#uploadBuktiPenerima').val('');
        }
    });

    $('#btnSendDocument').on('click', obj => {
        const dateRecived = $('#txt_date_diterima').val();
        const idPengiriman = $('#idPengiriman').val();

        const arrSelectDocument = document.getElementsByName('selectDocument');
        
        let isComplete = true;
        for (const selectDocument of arrSelectDocument) {
            if(!selectDocument.checked){
                isComplete = false;
                break;
            }
        }

        if(arrImgBukti.length === 0){
            Swal.fire({
                icon: "error",
                text: "Tambahkan bukti penerima"
            });
            return;
        }

        if(isComplete){
            const formData = new FormData();
            formData.append('dateRecived', dateRecived);
            formData.append('idPengiriman', idPengiriman);
            formData.append('status', 2);
            arrImgBukti.forEach((file, index) => {
                formData.append('buktiPenerima[]', file);
            });

            spinner('show', $(obj.target));
            ajaxPost('api/v1/pengiriman/action', formData, result => {
                spinner('hide', $(obj.target));
                if(result.meta.code == 200) {
                    Swal.fire({
                        icon: "success",
                        text: "Document diterima"
                    }).then(() => {
                        $('#modal-diterima').modal('hide');
                        resetForm();
                        loadData(1);
                    });
                }
            }, error => {
                const result = error.responseJSON;
                if(result?.meta?.code && result?.meta?.code == 500){
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
                    console.error(error);
                }
            })
        } else {
            Swal.fire({
                icon: "error",
                text: "Dokumen belum lengkap"
            });
        }
    });
});

function loadData(page = 1) {
    let params = {
        limit: 10,
        page: page,
        idPelanggan: idPelanggan ? idPelanggan : false
    };

    $(`#list-placeholder-pengiriman`).show();
    $(`#list-container-pengiriman`).hide();
    ajaxGet(`api/v1/pengiriman/list`, params, result => {
        let html = '';
        for (const [i, data] of result.data.entries()) {
            let htmlButton = '';
            if(data.status == 1){
                htmlButton = `<button class="btn btn-outline-primary btn-sm mb-2" onclick="showModalDiterima(this)">Dokumen diterima</button>`;
            }

            html += `
                <div class="card mb-2">
                    <div class="card-body row align-items-center py-2">
                        <div class="col-12 col-md-3">
                            <div class="fw-bolder">${data.id_pengiriman}</div>
                            <div class="fw-light">No resi : ${data.no_resi ?? 'Belum ada'}</div>
                            <small class="subdesc text-body-secondary fw-light lh-md">
                                <div>${data.no_kontrak}</div>
                                <div>created at ${dateFormat(data.created_at, 1)}</div>
                            </small>
                        </div>
                        <div class="col-6 col-md-1">
                            ${data.detail.length} Item
                        </div>
                        <div class="col-6 col-md-2">
                            <span>${data.permohonan.pelanggan.perusahaan.nama_perusahaan}</span>
                            <small class="subdesc text-body-secondary fw-light lh-sm">
                                <div class="tooltip-container cursoron" data-bs-toggle="tooltip" data-bs-placement="top" title="${data.alamat.alamat}">
                                    Alamat ${data.alamat.jenis}
                                </div>
                            </small>
                        </div>
                        <div class="col-6 col-md-2 text-center">
                            ${statusFormat('pengiriman', data.status)}
                        </div>
                        <div class="col-6 col-md-2 text-center">
                            <div class="row">
                                <span>Dikirim</span>
                                <small class="subdesc text-body-secondary fw-light lh-sm">
                                    ${data.send_at ? dateFormat(data.send_at, 4) : '-'}
                                </small>
                                <span>Diterima</span>
                                <small class="subdesc text-body-secondary fw-light lh-sm">
                                    ${data.recived_at ? dateFormat(data.recived_at, 4) : '-'}
                                </small>
                            </div>
                        </div>
                        <div class="col-6 col-md-2 text-center" data-id="${data.id_pengiriman}">
                            <button class="btn btn-outline-info btn-sm mb-2">Detail</button>
                            ${htmlButton}
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
    }, error => {
        const result = error.responseJSON;
        if(result?.meta?.code && result?.meta?.code == 500){
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
            console.error(error);
        }
    });
}

/**
 * Displays a modal with details of a received shipment.
 *
 * @param {Object} obj - The DOM element that triggered the function.
 */
function showModalDiterima(obj){
    const id = $(obj).parent().data('id');
    ajaxGet(`api/v1/pengiriman/getById/${id}`, false, result => {
        const data = result.data;
        $('#idPengiriman').val(id);

        // Inisialiasi Date
        $('#txt_date_diterima').flatpickr({
            altInput: true,
            locale: "id",
            maxDate: 'today',
            dateFormat: "Y-m-d",
            altFormat: "j F Y",
            defaultDate: 'today'
        });

        // Cek kelengkapan
        let htmlJenis = '';
        for (const detail of data.detail) {
            switch (detail.jenis) {
                case 'invoice':
                    htmlJenis += `
                        <li class="list-group-item d-flex justify-content-between align-items-center p-2">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold">Invoice</div>
                                ${data.permohonan.invoice.no_invoice}
                            </div>
                            <input type="checkbox" class="btn-check" name="selectDocument" id="selectDocumentInvoice" 
                                data-jenis="${detail.jenis}" data-id="${data.permohonan.invoice.keuangan_hash}" 
                                autocomplete="off" checked>
                            <label class="btn btn-outline-success btn-sm" for="selectDocumentInvoice">Sesuai</label><br>
                        </li>
                    `;
                    break;
                case 'lhu':
                    htmlJenis += `
                        <li class="list-group-item d-flex justify-content-between align-items-center p-2">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold">LHU</div>
                                <div>Periode ${detail.periode}</div>
                            </div>
                            <input type="checkbox" class="btn-check" name="selectDocument" id="selectDocumentLhu" 
                                data-jenis="${detail.jenis}" data-id="${data.permohonan.lhu.lhu_hash}" 
                                autocomplete="off" checked>
                            <label class="btn btn-outline-success btn-sm" for="selectDocumentLhu">Sesuai</label><br>
                        </li>
                    `;

                    break;
                default:
                    break;
            }
        }

        $('#list-kelengkapan').html(htmlJenis);
        $('#modal-diterima').modal('show');
    }, error => {
        const result = error.responseJSON;
        if(result?.meta?.code && result?.meta?.code == 500){
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
            console.error(error);
        }
    });
}

/**
 * Loads and displays a preview of images in the `arrImgBukti` array.
 * Each image is displayed as a thumbnail with a remove button.
 * Clicking the remove button will remove the image from the array and reload the previews.
 */
function loadPreviewBukti(){
    $('#list-preview-bukti').html('');
    for (const [i,img] of arrImgBukti.entries()) {
        const reader = new FileReader();
        reader.onload = function(e) {
            let divMain = document.createElement('div');
            divMain.className = '';
            divMain.style.width = '100px';
            divMain.style.height = '100px';

            const preview = document.createElement('img');
            preview.src = e.target.result;
            preview.className = 'img-thumbnail';
            preview.style.width = '100px';
            preview.style.height = '100px';
            preview.style.cursor = 'pointer';
            preview.onclick = () => {
                // $('#modal-preview-image').attr('src', e.target.result);

                // $('#modal-preview').modal('show');
            }

            const btnRemove = document.createElement('button');
            btnRemove.className = 'btn btn-danger btn-sm position-absolute mt-2 ms-2';
            btnRemove.innerHTML = '<i class="bi bi-trash"></i>';
            btnRemove.onclick = () => {
                arrImgBukti.splice(i, 1);
                loadPreviewBukti();
            }

            divMain.append(btnRemove);
            divMain.append(preview);

            document.getElementById('list-preview-bukti').appendChild(divMain);
        };
        reader.readAsDataURL(img);
    }
}

function resetForm(){
    arrImgBukti.splice(0, arrImgBukti.length);
    loadPreviewBukti();
    $('#list-kelengkapan').html('');
}