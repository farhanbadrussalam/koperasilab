let nowTab = 1;
let buktiPenerima = false;
let buktiPengiriman = false;
let filterComp = false;
$(function () {
    loadData(1);

    filterComp = new FilterComponent('list-filter', {
        jenis: 'pengiriman',
        filter : {
            search: true,
            no_kontrak : true
        }
    });
    // SETUP FILTER
    filterComp.on('filter.change', () => loadData());

    detail = new Detail({
        jenis: 'pengiriman',
        tab: {
            items: true,
            bukti: true,
            log: true
        }
    });

    buktiPengiriman = new UploadComponent('showBuktiPengiriman', {
        mode: 'preview',
        camera: false
    });

    buktiPenerima = new UploadComponent('uploadBuktiPenerima', {
        camera: false,
        allowedFileExtensions: ['png', 'gif', 'jpeg', 'jpg']
    });

    $('#btnSendDocument').on('click', obj => {
        const dateRecived = $('#txt_date_diterima').val();
        const idPengiriman = $('#idPengiriman').val();
        const arrSelectDocument = document.getElementsByName('selectDocument');
        const arrImgBukti = buktiPenerima.getData();

        let isComplete = true;
        for (const selectDocument of arrSelectDocument) {
            if(!selectDocument.checked){
                isComplete = false;
                break;
            }
        }

        if(arrImgBukti.length === 0){
            Swal.fire({
                icon: "warning",
                text: "Tambahkan bukti penerima"
            });
            return;
        }

        if(isComplete){
            const isLhuSend = $('#isLhuSend').val();
            Swal.fire({
                title: 'Konfirmasi Penerimaan Dokumen',
                text: "Apakah Anda yakin ingin menandai dokumen ini sebagai diterima?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, terima!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('dateRecived', dateRecived);
                    formData.append('idPengiriman', idPengiriman);
                    formData.append('status', 2);
                    arrImgBukti.forEach((d) => {
                        formData.append('buktiPenerima[]', d.file);
                    });
                    isLhuSend == 'true' ? formData.append('statusPermohonan', 5) : false;

                    spinner('show', $(obj.target));
                    ajaxPost('api/v1/pengiriman/diterima', formData, result => {
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
                        spinner('hide', $(obj.target));
                    })
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
        idPelanggan: idPelanggan ? idPelanggan : false,
        filter: {}
    };

    let filterValue = filterComp && filterComp.getAllValue();

    filterValue.search && (params.filter.search = filterValue.search);
    filterValue.no_kontrak && (params.filter.no_kontrak = filterValue.no_kontrak);

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
        for (const [i, data] of result.data.entries()) {
            let htmlButton = '<button class="btn btn-outline-info btn-sm mb-2" onclick="showDetail(this)">Detail</button>';
            if(data.status == 1){
                htmlButton += `<button class="btn btn-outline-primary btn-sm mb-2" onclick="showModalDiterima(this)">Diterima</button>`;
            }

            const dataCard = {
                id: data.id_pengiriman,
                format: 'pengiriman',
                status: data.status,
                kontrak: data.kontrak?.no_kontrak,
                title: data.id_pengiriman,
                no_resi: data.no_resi,
                items: data.detail,
                alamat: data.alamat,
                send_at: data.send_at,
                recived_at: data.recived_at
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
 * Displays a modal with details of a received shipment.
 *
 * @param {Object} obj - The DOM element that triggered the function.
 */
function showModalDiterima(obj){
    const id = $(obj).parent().parent().data('id');
    ajaxGet(`api/v1/pengiriman/getById/${id}`, false, result => {
        const data = result.data;
        $('#idPengiriman').val(id);
        buktiPengiriman.addData(data.media_pengiriman);

        // Inisialiasi Date
        $('#txt_date_diterima').flatpickr({
            altInput: true,
            locale: "id",
            maxDate: 'today',
            dateFormat: "Y-m-d",
            altFormat: "j F Y",
            defaultDate: 'today'
        });

        $('#isLhuSend').val(false);
        resetForm();

        // Cek kelengkapan
        let htmlJenis = '';
        for (const detail of data.detail) {
            switch (detail.jenis) {
                case 'invoice':
                    htmlJenis += `
                        <li class="list-group-item d-flex justify-content-between align-items-center p-2">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold">Invoice + MoU</div>
                                ${data.permohonan.invoice.no_invoice}
                            </div>
                            <input type="checkbox" class="form-check-input" name="selectDocument" id="selectDocumentInvoice"
                                data-jenis="${detail.jenis}" data-id="${data.permohonan.invoice.keuangan_hash}"
                                autocomplete="off" >
                        </li>
                    `;
                    break;
                case 'lhu':
                    $('#isLhuSend').val(true);
                    let htmlPeriode = !detail.periode ? 'Zero Check' : `Periode ${detail.periode}`;
                    if(detail.periode == 1 && data.kontrak.is_zerocek == 1 && data.kontrak.is_have_tld == 1) {
                        htmlPeriode += ` + Zero Cek`;
                    }

                    htmlJenis += `
                        <li class="list-group-item d-flex justify-content-between align-items-center p-2">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold">LHU</div>
                                <div>${htmlPeriode}</div>
                            </div>
                            <input type="checkbox" class="form-check-input" name="selectDocument" id="selectDocumentLhu"
                                data-jenis="${detail.jenis}" data-id="${data.permohonan.lhu.lhu_hash}"
                                autocomplete="off" >
                        </li>
                    `;
                    break;
                case 'tld':
                    let jumPengguna = detail.data_tld.filter(d => d.jenis == 'pengguna').length;
                    let jumKontrol = detail.data_tld.filter(d => d.jenis == 'kontrol').length;

                    let periodeTld = detail.periode === 0 ? 1 : detail.periode;
                    let findPeriode = data.kontrak.periode.find(periode => periode.periode == periodeTld);
                    htmlJenis += `
                        <li class="list-group-item d-flex justify-content-between align-items-center p-2">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold">TLD ${findPeriode?.status == 2 ? 'Pengembalian' : 'Periode '+periodeTld } <span class="text-secondary fw-normal">- ${jumPengguna} Pengguna + ${jumKontrol} Kontrol</span></div>
                                <div></div>
                            </div>
                            <input type="checkbox" class="form-check-input" name="selectDocument" id="selectDocumentTld"
                                data-jenis="${detail.jenis}" autocomplete="off" >
                        </li>
                    `;

                    // Menampilkan dokumen surpeng
                    let htmlSurpeng = '';
                    let findKontrakPeriode = data.kontrak.periode.find(periode => periode.periode == detail.periode);
                    if(findKontrakPeriode?.nomer_surpeng){
                        htmlSurpeng += `
                            <div
                                class="d-flex align-items-center justify-content-between px-3 shadow-sm cursoron document border">
                                    <a class="d-flex align-items-center w-100" href="${base_url}/laporan/surpeng/${data.kontrak.kontrak_hash}/${data.periode ?? 0}" target="_blank">
                                        <div>
                                            <img class="my-3" src="${base_url}/icons/${iconDocument('application/pdf')}" alt=""
                                                style="width: 24px; height: 24px;">
                                        </div>
                                        <div class="flex-grow-1 ms-2">
                                            <div class="d-flex flex-column">
                                                <span class="caption text-main">SURAT PENGANTAR</span>
                                                <span class="text-submain caption text-secondary">${dateFormat(detail.created_at, 1)}</span>
                                            </div>
                                        </div>
                                    </a>
                                <div class="d-flex align-items-center"></div>
                            </div>
                        `;
                    }
                    $('#surpengDiv').html(htmlSurpeng);
                    break
                default:
                    htmlJenis += `
                        <li class="list-group-item d-flex justify-content-between align-items-center p-2">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold">${detail.jenis[0].toUpperCase() + detail.jenis.substring(1)} <span class="text-secondary fw-normal"></div>
                            </div>
                            <input type="checkbox" class="form-check-input" name="selectDocument" id="selectDocumentCustom"
                                data-jenis="${detail.jenis}" data-id="${data.permohonan.permohonan_hash}"
                                autocomplete="off" >
                        </li>
                    `;
                    break;
            }
        }

        $('#list-kelengkapan').html(htmlJenis);
        $('#modal-diterima').modal('show');
    });
}

function resetForm(){
    buktiPenerima.addData([]);
    $('#list-kelengkapan').html('');
}

function reload(){
    loadData();
}

function showDetail(obj){
    const id = $(obj).parent().parent().data("id");
    detail.show(`api/v1/pengiriman/getById/${id}`);
}

function clearFilter(){
    filterComp.clear();
    loadData();
}
