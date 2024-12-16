let nowTab = 1;
const arrImgBukti = [];
$(function () {
    switchLoadTab(1);

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
                    switchLoadTab(1);
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
    });
});

function switchLoadTab(menu){
    nowTab = menu;
    switch (menu) {
        case 1:
            menu = 'list';
            break;
    
        case 2:
            menu = 'selesai';
            break;
    }

    loadData(1, menu);
}

function loadData(page = 1, menu) {
    let params = {
        limit: 10,
        page: page,
        menu: menu,
        idPelanggan: idPelanggan ? idPelanggan : false
    };

    $(`#list-placeholder-${menu}`).show();
    $(`#list-container-${menu}`).hide();
    ajaxGet(`api/v1/pengiriman/list`, params, result => {
        let html = '';
        for (const [i, data] of result.data.entries()) {
            let periode = JSON.parse(data.periode);
            let jenis = data.jenis_pengiriman.split(',');
            
            let htmlJenis = '';
            for (const v of jenis) {
                htmlJenis += `<div>${v}</div>`;
            }

            let htmlButton = '';
            if(data.status == 1){
                htmlButton = `<button class="btn btn-outline-primary btn-sm mb-2" onclick="showModalDiterima(this)">Dokumen diterima</button>`;
            }

            html += `
                <div class="card mb-2">
                    <div class="card-body row align-items-center py-2">
                        <div class="col-12 col-md-3">
                            <div class="fw-bolder">${data.no_resi}</div>
                            <small class="subdesc text-body-secondary fw-light lh-sm">
                                <div>${data.no_kontrak}</div>
                                <div>Periode: </div>
                                <div class="badge text-bg-secondary">${dateFormat(periode.start_date, 4)} s/d ${dateFormat(periode.end_date, 4)}</div>
                            </small>
                        </div>
                        <div class="col-6 col-md-1">
                            ${htmlJenis}
                        </div>
                        <div class="col-6 col-md-2">
                            <span>${data.permohonan.pelanggan.perusahaan.nama_perusahaan}</span>
                            <small class="subdesc text-body-secondary fw-light lh-sm">
                                <div>Alamat: </div>
                                <div>${data.alamat}</div>
                            </small>
                        </div>
                        <div class="col-6 col-md-2 text-center">
                            ${statusFormat('pengiriman', data.status)}
                        </div>
                        <div class="col-6 col-md-2 text-center">
                            <div class="row">
                                <span>Dikirim</span>
                                <small class="subdesc text-body-secondary fw-light lh-sm">
                                    ${data.created_at ? dateFormat(data.created_at, 4) : '-'}
                                </small>
                                <span>Diterima</span>
                                <small class="subdesc text-body-secondary fw-light lh-sm">
                                    ${data.recived_at ? dateFormat(data.recived_at, 4) : '-'}
                                </small>
                            </div>
                        </div>
                        <div class="col-6 col-md-2 text-center" data-id="${data.pengiriman_hash}">
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

        $(`#list-container-${menu}`).html(html);

        $(`#list-pagination-${menu}`).html(createPaginationHTML(result.pagination));

        $(`#list-placeholder-${menu}`).hide();
        $(`#list-container-${menu}`).show();
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
        let jenisPengiriman = data.jenis_pengiriman.split(',');
        let htmlJenis = '';
        for (const jenis of jenisPengiriman) {
            switch (jenis) {
                case 'invoice':
                    let badgeStatus = '';
                    if(data.permohonan.invoice.status == 5){
                        badgeStatus = `<span class="badge text-bg-primary rounded-pill">Status : Sudah bayar</span>`
                    }else{
                        badgeStatus = `<span class="badge text-bg-danger rounded-pill">Status : Belum bayar</span>`
                    }
                    htmlJenis += `
                        <li class="list-group-item d-flex justify-content-between align-items-center p-2">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold">Invoice</div>
                                ${data.permohonan.invoice.no_invoice}
                            </div>
                            ${badgeStatus}
                        </li>
                    `;
                    break;
                case 'lhu':
                
                    htmlJenis += `
                        <li class="list-group-item d-flex justify-content-between align-items-center p-2">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold">LHU</div>
                                <a class="p-2 rounded border cursoron document" target="_blank" href="${base_url}/storage/${data.permohonan.lhu.media.file_path}/${data.permohonan.lhu.media.file_hash}">
                                    <img class="my-2" src="${base_url}/icons/${iconDocument(data.permohonan.lhu.media.file_type)}" style="width: 24px; height: 24px;">
                                    <span class="caption text-main">${data.permohonan.lhu.media.file_ori}</span>
                                </a>
                            </div>
                            ${statusFormat('penyelia',data.permohonan.lhu.status)}
                        </li>
                    `;

                    break;
                default:
                    break;
            }
        }

        $('#list-kelengkapan').html(htmlJenis);
        console.log(result);
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