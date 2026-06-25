let dataAdendum = [];
let currentPage = 1;
let searchTimeout = null;

let filterComp = false;
$(function () {
    filterComp = new FilterComponent('list-filter', {
        jenis: 'adendum',
        filter: {
            no_kontrak: true
        },
        showOnLoad: true
    })
    // SETUP FILTER
    filterComp.on('filter.change', () => loadData());

    loadData(1);

    // Filter pencarian dengan debounce 500ms
    $('#search-adendum').on('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            loadData(1);
        }, 500);
    });

    // Pagination Click Handler
    $('#list-pagination-adendum').on('click', 'a', function (e) {
        e.preventDefault();
        const page = $(this).data('page');
        if (page) {
            loadData(page);
        }
    });
});

function loadData(page = 1) {
    currentPage = page;

    const params = {
        limit: 5,
        page: page
    };

    let filterValue = filterComp && filterComp.getAllValue();
    filterValue.no_kontrak && (params.no_kontrak = filterValue.no_kontrak);

    showSkeleton();

    ajaxGet(`api/v1/adendum/list`, params, result => {
        dataAdendum = result.data;

        // Update sidebar badge
        const totalAdendum = result.pagination?.total ?? 0;
        if (totalAdendum > 0) {
            $('#adendum-sidebar-badge').text(totalAdendum).removeClass('d-none');
        } else {
            $('#adendum-sidebar-badge').addClass('d-none');
        }

        let html = '';

        if (result.data.length === 0) {
            html = `
                <div class="text-center py-5 bg-white rounded-4 shadow-sm border border-light-subtle">
                    <i class="bi bi-clipboard-x text-muted" style="font-size: 3rem;"></i>
                    <h5 class="fw-bold mt-3 text-dark">Tidak Ada Antrean Pengiriman</h5>
                    <p class="text-muted small">Semua dokumen adendum telah selesai diproses dan dikirim.</p>
                </div>
            `;
        } else {
            console.log(result.data);
            result.data.forEach((data, index) => {
                let jmlPergantian = data.permohonan_detail?.filter(detail => detail.type === 'ganti').length ?? 0;
                let jmlPenambahan = data.permohonan_detail?.filter(detail => detail.type === 'baru').length ?? 0;

                // Cari status pengiriman LHU adendum
                let findLhuAdendum = data.lhu?.pengiriman;
                let statusLhu = findLhuAdendum ? findLhuAdendum.status : 0;

                // Cari pengiriman Invoice adendum (hanya jika ada penambahan)
                let findInvoiceAdendum = (jmlPenambahan > 0 && data.invoice) ? data.invoice.pengiriman : null;

                // Dokumen status info (Column 1 Dokumen List)
                let htmlDocs = '';

                // 1. Invoice Adendum
                if (jmlPenambahan > 0) {
                    let statusInvoice = findInvoiceAdendum ? findInvoiceAdendum.status : 0;
                    let textStatusInvoice = statusFormat('pengiriman', statusInvoice);
                    let textInvoiceDetails = '';
                    if (data.invoice) {
                        let statusInv = statusFormat('invoice', data.invoice.status);
                        textInvoiceDetails = `<div class="fs-7 text-secondary">${statusInv}</div>`;
                    }
                    htmlDocs += `
                        <div class="col-4">
                            <div class="p-2 bg-light rounded-2 border border-light-subtle h-100">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-medium text-dark small"><i class="bi bi-receipt-cutoff text-muted me-2"></i>Invoice</span>
                                    <span class="small">${textStatusInvoice}</span>
                                </div>
                                ${textInvoiceDetails}
                            </div>
                        </div>
                    `;
                }

                // 2. LHU Adendum
                let textLhuDetails = '';
                if (data.lhu && data.lhu.status) {
                    textLhuDetails = `<div class="fs-7 text-secondary">${statusFormat('penyelia', data.lhu.status)}</div>`;
                }

                let textLhu = 'LHU';
                data.is_zerocek && (textLhu += ' + Zero Check');
                htmlDocs += `
                    <div class="col-4">
                        <div class="p-2 bg-light rounded-2 border border-light-subtle h-100">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-medium text-dark small"><i class="bi bi-file-earmark-check text-muted me-2"></i>${textLhu}</span>
                                <span class="small">${statusFormat('pengiriman', statusLhu)}</span>
                            </div>
                            ${textLhuDetails}
                        </div>
                    </div>
                `;

                // 3. TLD Adendum
                const detailTld = data.kontrak?.kontrak_detail?.find(d => d.periode_tld_1 === data.periode || d.periode_tld_2 === data.periode);
                const isTldSent = detailTld ? (detailTld.periode_tld_1 === data.periode ? detailTld.status_tld_1 : detailTld.status_tld_2) == 2 : false;
                
                let paramValidasi = {
                    is_zerocek: data.is_zerocek,
                    is_have_tld: data.is_have_tld,
                    is_periode_berjalan: data.is_periode_berjalan,
                    is_send_tld: isTldSent
                };

                if(validateTldAdendum(paramValidasi)){
                    const findTldAdendum = data.pengiriman_tld;
                    let statusTld = findTldAdendum ? findTldAdendum.status : 0;
                    htmlDocs += `
                        <div class="col-4">
                            <div class="p-2 bg-light rounded-2 border border-light-subtle h-100">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-medium text-dark small"><i class="bi bi-cpu text-muted me-2"></i>TLD</span>
                                    <span class="small">${statusFormat('pengiriman', statusTld)}</span>
                                </div>
                            </div>
                        </div>
                    `;
                }

                // Tombol aksi kirim (Column 3 Action)
                let htmlBtn = `<a class="btn btn-primary rounded-pill px-3 shadow-xs btn-sm fw-bold w-100 text-center d-flex align-items-center justify-content-center gap-1" href="${base_url}/staff/pengiriman/permohonan/kirim/${data.permohonan_hash}"><i class="bi bi-send-fill"></i> Kirim Dokumen</a>`;

                // Informasi (Column 2 Info)
                let htmlInfo = `
                    <div class="d-flex flex-column text-start gap-1">
                        <span class="text-uppercase small fw-bold text-muted mb-1" style="font-size: 0.65rem; opacity: 0.7;">Total Harga:</span>
                        <div class="fw-bold fs-6 text-primary-emphasis">${formatRupiah(data.total_harga)}</div>
                        <div class="mt-2 text-secondary small border-start ps-2" style="max-height: 70px; overflow-y: auto;">
                            ${data.note ? `<i class="bi bi-chat-right-text text-muted me-1"></i>Catatan: <i>${data.note}</i>` : '<i class="bi bi-chat-right-text text-muted me-1"></i>Tidak ada catatan'}
                        </div>
                    </div>
                `;

                html += `
                    <div class="card mb-3 border border-light-subtle shadow-sm rounded-4 bg-white" id="${data.permohonan_hash}">
                        <div class="card-body p-3">
                            <div class="row align-items-center">
                                <!-- Column 1: Info & Dokumen (col-md-6) -->
                                <div class="col-md-6 border-end border-light-subtle py-2">
                                    <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                                        <span class="fw-bold fs-6 text-primary-emphasis">Adendum Periode ${data.periode}</span>
                                        <span class="badge rounded-pill bg-info-subtle text-info-emphasis border border-info-subtle fw-normal">
                                            Ganti: ${jmlPergantian}
                                        </span>
                                        <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle fw-normal">
                                            Baru: ${jmlPenambahan}
                                        </span>
                                    </div>
                                    <div class="mb-3 text-start">
                                        <h6 class="fw-bold text-dark mb-1">${data.jenis_tld.name} - ${data.pelanggan.perusahaan.nama_perusahaan}</h6>
                                        <div class="d-flex flex-wrap gap-2 text-secondary fs-7">
                                            <div><i class="bi bi-person-circle"></i> PIC: <b>${data.pelanggan.name}</b></div>
                                            <div>|</div>
                                            <div><i class="bi bi-hash"></i> No Kontrak: <b>${data.kontrak ? data.kontrak.no_kontrak : '-'}</b></div>
                                            <div>|</div>
                                            <div><i class="bi bi-calendar-check"></i> Digunakan: <b>${findDate(data.periodenow.start_date, data.bulan_mulai)}</b></div>
                                        </div>
                                        <div class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle rounded-pill fw-normal px-3 mt-2 small">
                                            Layanan: ${data.jenis_layanan_parent.name} - ${data.jenis_layanan.name}
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Column 2: Informasi (col-md-3) -->
                                <div class="col-md-3 border-end border-light-subtle py-2 px-md-3 my-2 my-md-0 text-start">
                                    <span class="text-uppercase small fw-bold text-muted d-block mb-2">Informasi</span>
                                    ${htmlInfo}
                                </div>
                                
                                <!-- Column 3: Tindakan (col-md-3) -->
                                <div class="col-md-3 py-2 px-md-3 d-flex flex-column align-items-md-start align-items-start gap-1">
                                    <span class="text-uppercase small fw-bold text-muted d-block mb-2 w-100 text-start">Tindakan</span>
                                    <div class="d-flex flex-column w-100 gap-2">
                                        ${htmlBtn}
                                    </div>
                                </div>

                                <div class="col-md-12 border-top border-light-subtle py-2 px-md-3">
                                    <div class="row g-2 text-start">
                                        ${htmlDocs}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
        }

        $(`#list-container-adendum`).html(html);
        $(`#list-pagination-adendum`).html(createPaginationHTML(result.pagination));

        $(`#list-placeholder-adendum`).addClass('d-none').removeClass('d-flex');
        $(`#list-container-adendum`).removeClass('d-none').addClass('d-flex');
    }, error => {
        $(`#list-placeholder-adendum`).addClass('d-none').removeClass('d-flex');
        $(`#list-container-adendum`).html(`
            <div class="text-center py-5 bg-white rounded-4 shadow-sm text-danger border border-light-subtle">
                <i class="bi bi-exclamation-triangle-fill" style="font-size: 3rem;"></i>
                <h5 class="fw-bold mt-3">Gagal Memuat Data</h5>
                <p class="text-secondary small">Terjadi kesalahan pada server saat mengambil data antrean.</p>
                <button class="btn btn-sm btn-outline-danger rounded-pill px-3 mt-2" onclick="reload()"><i class="bi bi-arrow-clockwise"></i> Coba Lagi</button>
            </div>
        `).removeClass('d-none').addClass('d-flex');
    });
}

function showSkeleton() {
    let skeletonHtml = '';
    for (let i = 0; i < 3; i++) {
        skeletonHtml += `
            <div class="card border border-light-subtle rounded-4 shadow-sm mb-3 placeholder-glow bg-white">
                <div class="card-body p-3">
                    <div class="row align-items-center">
                        <div class="col-md-6 border-end border-light-subtle py-2">
                            <span class="placeholder col-4 mb-2 d-block" style="height: 1.2rem;"></span>
                            <span class="placeholder col-8 mb-3 d-block" style="height: 1.5rem;"></span>
                            <div class="row row-cols-1 row-cols-sm-2 g-2">
                                <div class="col"><div class="placeholder col-12 py-3 rounded-2" style="height: 38px;"></div></div>
                                <div class="col"><div class="placeholder col-12 py-3 rounded-2" style="height: 38px;"></div></div>
                            </div>
                        </div>
                        <div class="col-md-3 border-end border-light-subtle py-2 px-md-3">
                            <span class="placeholder col-6 mb-2 d-block" style="height: 0.8rem;"></span>
                            <span class="placeholder col-8 d-block mb-2" style="height: 1.2rem;"></span>
                            <span class="placeholder col-10 d-block" style="height: 1rem;"></span>
                        </div>
                        <div class="col-md-3 py-2 px-md-3">
                            <span class="placeholder col-6 mb-2 d-block" style="height: 0.8rem;"></span>
                            <span class="placeholder col-10 d-block rounded-pill" style="height: 31px;"></span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    $('#list-placeholder-adendum').html(skeletonHtml).removeClass('d-none').addClass('d-flex');
    $('#list-container-adendum').addClass('d-none').removeClass('d-flex');
}

function reload() {
    loadData(currentPage);
}

function clearFilter() {
    filterComp.clear();
    loadData();
}