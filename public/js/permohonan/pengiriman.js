let nowTab = 1;
let buktiPenerima = false;
let buktiPengiriman = false;
let filterComp = false;
let currentTab = 'progress';

$(function () {
    loadData(1);

    filterComp = new FilterComponent('list-filter', {
        jenis: 'pengiriman',
        filter: {
            search: true,
            no_kontrak: true
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
            if (!selectDocument.checked) {
                isComplete = false;
                break;
            }
        }

        if (arrImgBukti.length === 0) {
            Swal.fire({
                icon: "warning",
                text: "Tambahkan bukti penerima"
            });
            return;
        }

        if (isComplete) {
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
                        if (result.meta.code == 200) {
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

function switchLoadTab(tab) {
    currentTab = tab;
    $('.nav-link').removeClass('active');
    $(`#${tab}-tab`).addClass('active');
    loadData(1);
}

function loadData(page = 1) {
    let params = {
        limit: 10,
        page: page,
        idPelanggan: idPelanggan ? idPelanggan : false,
        tab: currentTab,
        filter: {}
    };

    let filterValue = filterComp && filterComp.getAllValue();

    filterValue.search && (params.filter.search = filterValue.search);
    filterValue.no_kontrak && (params.filter.no_kontrak = filterValue.no_kontrak);

    if (Object.keys(params.filter).length > 0) {
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
            if (data.status == 1) {
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
        if (result.data.length == 0) {
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
function showModalDiterima(obj) {
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
        $('#surpengDiv').html(''); // Reset surpeng div

        for (const detail of data.detail) {
            switch (detail.jenis) {
                case 'invoice':
                    htmlJenis += `
                        <li class="list-group-item d-flex justify-content-between align-items-center p-3 border rounded-3 mb-2 shadow-xs bg-white hover-shadow transition-all">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary-subtle text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="bi bi-file-earmark-text-fill fs-5"></i>
                                </div>
                                <div class="text-start">
                                    <div class="fw-bold text-dark mb-0 fs-7">Invoice + MoU</div>
                                    <small class="text-body-tertiary font-monospace">${data.permohonan.invoice.no_invoice}</small>
                                </div>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input type="checkbox" class="form-check-input cursor-pointer" style="width: 2.2em; height: 1.2em;" name="selectDocument" id="selectDocumentInvoice"
                                    data-jenis="${detail.jenis}" data-id="${data.permohonan.invoice.keuangan_hash}"
                                    autocomplete="off" >
                            </div>
                        </li>
                    `;
                    break;
                case 'lhu':
                    $('#isLhuSend').val(true);
                    let htmlPeriode = !detail.periode ? 'Zero Check' : `Periode ${detail.periode}`;
                    if (detail.periode == 1 && data.kontrak.is_zerocek == 1) {
                        htmlPeriode += ` + Zero Check`;
                    }

                    htmlJenis += `
                        <li class="list-group-item d-flex justify-content-between align-items-center p-3 border rounded-3 mb-2 shadow-xs bg-white hover-shadow transition-all">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-success-subtle text-success rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="bi bi-clipboard2-check-fill fs-5"></i>
                                </div>
                                <div class="text-start">
                                    <div class="fw-bold text-dark mb-0 fs-7">Laporan Hasil Uji (LHU)</div>
                                    <small class="text-body-tertiary">${htmlPeriode}</small>
                                </div>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input type="checkbox" class="form-check-input cursor-pointer" style="width: 2.2em; height: 1.2em;" name="selectDocument" id="selectDocumentLhu"
                                    data-jenis="${detail.jenis}" data-id="${data.permohonan.lhu.lhu_hash}"
                                    autocomplete="off" >
                            </div>
                        </li>
                    `;
                    break;
                case 'tld':
                    let jumPengguna = detail.data_tld.filter(d => d.jenis == 'pengguna').length;
                    let jumKontrol = detail.data_tld.filter(d => d.jenis == 'kontrol').length;

                    let periodeTld = detail.periode === 0 ? 1 : detail.periode;
                    let findPeriode = data.kontrak.periode.find(periode => periode.periode == periodeTld);
                    htmlJenis += `
                        <li class="list-group-item d-flex justify-content-between align-items-center p-3 border rounded-3 mb-2 shadow-xs bg-white hover-shadow transition-all">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-info-subtle text-info rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="bi bi-shield-check fs-5"></i>
                                </div>
                                <div class="text-start">
                                    <div class="fw-bold text-dark mb-0 fs-7">${findPeriode?.status == 2 ? 'Pengembalian TLD' : 'TLD ' + (detail.periode === 0 ? 'Zero Check' : 'Periode ' + detail.periode)}</div>
                                    <small class="text-body-tertiary">${jumPengguna} Pengguna + ${jumKontrol} Kontrol</small>
                                </div>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input type="checkbox" class="form-check-input cursor-pointer" style="width: 2.2em; height: 1.2em;" name="selectDocument" id="selectDocumentTld"
                                    data-jenis="${detail.jenis}" autocomplete="off" >
                            </div>
                        </li>
                    `;

                    // Menampilkan dokumen surpeng
                    let htmlSurpeng = '';
                    let findKontrakPeriode = data.kontrak.periode.find(periode => periode.periode == detail.periode);
                    if (findKontrakPeriode?.nomer_surpeng) {
                        htmlSurpeng += `
                            <div class="card border border-primary-subtle shadow-sm rounded-3 overflow-hidden bg-white hover-shadow transition-all mb-3">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bg-danger-subtle text-danger rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="bi bi-file-pdf-fill fs-4"></i>
                                            </div>
                                            <div class="text-start">
                                                <div class="fw-bold text-dark mb-0 fs-7">Surat Pengantar TLD</div>
                                                <small class="text-body-tertiary">Terbit: ${dateFormat(detail.created_at, 1)}</small>
                                            </div>
                                        </div>
                                        <a href="${base_url}/laporan/surpeng/${data.kontrak.kontrak_hash}/${data.kontrak.periode_next ? 1 : detail.periode ?? 0}" target="_blank" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                            <i class="bi bi-printer me-1"></i> Cetak PDF
                                        </a>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                    $('#surpengDiv').html(htmlSurpeng);
                    break
                default:
                    htmlJenis += `
                        <li class="list-group-item d-flex justify-content-between align-items-center p-3 border rounded-3 mb-2 shadow-xs bg-white hover-shadow transition-all">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-secondary-subtle text-secondary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="bi bi-file-earmark-arrow-down-fill fs-5"></i>
                                </div>
                                <div class="text-start">
                                    <div class="fw-bold text-dark mb-0 fs-7">${detail.jenis[0].toUpperCase() + detail.jenis.substring(1)}</div>
                                    <small class="text-body-tertiary">Dokumen Tambahan</small>
                                </div>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input type="checkbox" class="form-check-input cursor-pointer" style="width: 2.2em; height: 1.2em;" name="selectDocument" id="selectDocumentCustom"
                                    data-jenis="${detail.jenis}" data-id="${data.permohonan.permohonan_hash}"
                                    autocomplete="off" >
                            </div>
                        </li>
                    `;
                    break;
            }
        }

        $('#list-kelengkapan').html(htmlJenis);
        $('#modal-diterima').modal('show');
    });
}

function resetForm() {
    buktiPenerima.addData([]);
    $('#list-kelengkapan').html('');
}

function reload() {
    loadData();
}

function showDetail(obj) {
    const id = $(obj).parent().parent().data("id");
    detail.show(`api/v1/pengiriman/getById/${id}`);
}

function clearFilter() {
    filterComp.clear();
    loadData();
}
