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

    ajaxGet(`api/v1/pengiriman/listAdendum`, params, result => {
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
                <div class="text-center py-5 bg-white rounded-4 shadow-sm">
                    <i class="bi bi-clipboard-x text-muted" style="font-size: 3rem;"></i>
                    <h5 class="fw-bold mt-3 text-dark">Tidak Ada Antrean Pengiriman</h5>
                    <p class="text-muted small">Semua dokumen adendum telah selesai diproses dan dikirim.</p>
                </div>
            `;
        } else {
            result.data.forEach((data, index) => {
                let jmlPergantian = data.permohonan_detail?.filter(detail => detail.type === 'ganti').length ?? 0;
                let jmlPenambahan = data.permohonan_detail?.filter(detail => detail.type === 'baru').length ?? 0;

                // Tipe Layanan & Periode
                let headerBadge = `<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill fw-semibold px-3 me-2">Adendum Periode ${data.periode}</span>`;
                let serviceBadge = `<span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle rounded-pill fw-normal px-3">${data.jenis_layanan_parent.name} - ${data.jenis_layanan.name}</span>`;

                // Cari status pengiriman LHU adendum
                let findLhuAdendum = data.lhu?.pengiriman;

                // Cari pengiriman Invoice adendum (hanya jika ada penambahan)
                let findInvoiceAdendum = (jmlPenambahan > 0 && data.invoice) ? data.invoice.pengiriman : null;

                // Dokumen status info
                let htmlInvoice = '';
                if (jmlPenambahan > 0) {
                    let statusInvoice = findInvoiceAdendum ? findInvoiceAdendum.status : 0;
                    let textStatusInvoice = statusFormat('pengiriman', statusInvoice);
                    if (data.invoice) {
                        let statusInv = statusFormat('invoice', data.invoice.status);
                        textStatusInvoice += ` (${statusInv})`;
                    }
                    htmlInvoice = `
                        <div class="col-md-6 border-top py-2 d-flex justify-content-between align-items-center">
                            <span class="small fw-semibold text-secondary"><i class="bi bi-receipt-cutoff me-2"></i>Invoice Adendum</span>
                            <span class="small">${textStatusInvoice}</span>
                        </div>
                    `;
                }

                let statusLhu = findLhuAdendum ? findLhuAdendum.status : 0;
                let htmlLhu = `
                    <div class="col-md-6 border-top py-2 d-flex justify-content-between align-items-center">
                        <span class="small fw-semibold text-secondary"><i class="bi bi-file-earmark-check me-2"></i>LHU Adendum</span>
                        <span class="small">${statusFormat('pengiriman', statusLhu)}</span>
                    </div>
                `;

                // Tombol aksi kirim
                let htmlBtn = `<a class="btn btn-primary rounded-pill px-4 shadow-sm btn-sm fw-bold d-flex align-items-center gap-1" href="${base_url}/staff/pengiriman/permohonan/kirim/${data.permohonan_hash}"><i class="bi bi-send-fill"></i> Kirim Dokumen</a>`;

                html += `
                    <div class="card mb-3 border-0 shadow-sm rounded-4 hover-shadow-transition" id="${data.permohonan_hash}">
                        <div class="card-body p-4">
                            <div class="row align-items-center mb-3">
                                <div class="col-12 col-md-8">
                                    <div class="mb-2">
                                        ${headerBadge}
                                        ${serviceBadge}
                                    </div>
                                    <h5 class="fw-bold text-dark mb-1">
                                        ${data.jenis_tld.name} - ${data.pelanggan.perusahaan.nama_perusahaan}
                                    </h5>
                                    <div class="d-flex flex-wrap gap-3 text-secondary small">
                                        <div><i class="bi bi-person-circle"></i> ${data.pelanggan.name}</div>
                                        <div><i class="bi bi-hash"></i> ${data.kontrak ? data.kontrak.no_kontrak : '-'}</div>
                                        <div><i class="bi bi-calendar-check"></i> Disetujui: ${dateFormat(data.created_at, 4)}</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4 text-md-end mt-3 mt-md-0">
                                    <div class="fw-bold fs-5 text-primary-emphasis mb-2">${formatRupiah(data.total_harga)}</div>
                                    <div class="d-flex gap-2 justify-content-md-end mb-2">
                                        <span class="badge rounded-pill bg-info-subtle text-info-emphasis border border-info-subtle fw-normal">
                                            Ganti: ${jmlPergantian}
                                        </span>
                                        <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle fw-normal">
                                            Baru: ${jmlPenambahan}
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-md-end">
                                        ${htmlBtn}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row g-3 px-2">
                                ${htmlInvoice}
                                ${htmlLhu}
                            </div>
                        </div>
                    </div>
                `;
            });
        }

        $(`#list-container-adendum`).html(html);
        $(`#list-pagination-adendum`).html(createPaginationHTML(result.pagination));

        $(`#list-placeholder-adendum`).hide();
        $(`#list-container-adendum`).show();
    }, error => {
        $(`#list-placeholder-adendum`).hide();
        $(`#list-container-adendum`).html(`
            <div class="text-center py-5 bg-white rounded-4 shadow-sm text-danger">
                <i class="bi bi-exclamation-triangle-fill" style="font-size: 3rem;"></i>
                <h5 class="fw-bold mt-3">Gagal Memuat Data</h5>
                <p class="text-secondary small">Terjadi kesalahan pada server saat mengambil data antrean.</p>
                <button class="btn btn-sm btn-outline-danger rounded-pill px-3 mt-2" onclick="reload()"><i class="bi bi-arrow-clockwise"></i> Coba Lagi</button>
            </div>
        `).show();
    });
}

function showSkeleton() {
    let skeletonHtml = '';
    for (let i = 0; i < 3; i++) {
        skeletonHtml += `
            <div class="card mb-3 placeholder-glow border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <span class="placeholder col-3 mb-2 rounded-pill" style="height: 24px;"></span>
                            <span class="placeholder col-2 mb-2 rounded-pill" style="height: 24px;"></span>
                            <h5 class="placeholder col-8 mb-3 rounded" style="height: 28px;"></h5>
                            <div class="d-flex gap-3">
                                <span class="placeholder col-3 rounded" style="height: 16px;"></span>
                                <span class="placeholder col-2 rounded" style="height: 16px;"></span>
                            </div>
                        </div>
                        <div class="col-auto ms-auto text-end">
                            <span class="placeholder col-12 rounded-pill" style="height: 38px; width: 120px;"></span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    $('#list-placeholder-adendum').html(skeletonHtml).show();
    $('#list-container-adendum').hide();
}

function reload() {
    loadData(currentPage);
}

function clearFilter() {
    filterComp.clear();
    loadData();
}