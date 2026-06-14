/**
 * penyimpanan.js
 * Halaman inventaris TLD di lab.
 * Struktur data API:
 *   { summary, tld_di_lab, tld_evaluasi, tld_sewa, tld_idle }
 */

// State Global untuk Client-side Pagination
let allLabTlds = [];
let allIdleTlds = [];
let currentPageLab = 1;
let currentPageIdle = 1;
const limitPerPage = 5;
let filterComp = false;

$(function () {
    // Inisialisasi FilterComponent
    filterComp = new FilterComponent('list-filter', {
        jenis: 'penyimpanan',
        filter: {
            no_kontrak: true,
            date_range: true,
            search: true,
            status: true
        },
        placeholder: {
            search: 'Cari Seri TLD atau Pengguna...'
        }
    });

    // Event listener saat filter berubah
    filterComp.on('filter.change', () => loadPenyimpananData());

    loadPenyimpananData();

    // Bind click events untuk pagination
    $(document).on('click', '#pagination-di-lab .page-link', function (e) {
        e.preventDefault();
        const pageno = parseInt($(this).attr('data-page'));
        if (pageno) {
            renderLabTlds(pageno);
        }
    });

    $(document).on('click', '#pagination-idle .page-link', function (e) {
        e.preventDefault();
        const pageno = parseInt($(this).attr('data-page'));
        if (pageno) {
            renderIdleTlds(pageno);
        }
    });
});

function loadPenyimpananData() {
    // Reset tampilan
    $('#placeholder-container').removeClass('d-none');
    $('#section-di-lab, #section-idle, #empty-state').addClass('d-none');
    $('#summary-counter').css('display', 'none');
    $('#total-label').text('');

    let params = {
        filter: {}
    };

    let filterValue = filterComp && filterComp.getAllValue();
    if (filterValue) {
        filterValue.no_kontrak && (params.filter.id_kontrak = filterValue.no_kontrak);
        (filterValue.date_range && filterValue.date_range.length == 2) && (params.filter.date_range = filterValue.date_range);
        filterValue.periode && (params.filter.periode = filterValue.periode);
        filterValue.search && (params.filter.search = filterValue.search);
        filterValue.status && (params.filter.status = filterValue.status);
    }

    // Tampilkan jumlah filter aktif di UI
    if (filterValue && Object.keys(params.filter).length > 0) {
        $('#countFilter').html(Object.keys(params.filter).length).removeClass('d-none');
    } else {
        $('#countFilter').addClass('d-none');
    }

    ajaxGet('api/v1/tld/getPenyimpanan', params, function (result) {
        $('#placeholder-container').addClass('d-none');

        const data = result.data || {};
        const summary = data.summary || {};
        const diLab = data.tld_di_lab || [];
        const evalasi = data.tld_evaluasi || [];
        const sewa = data.tld_sewa || [];
        const idle = data.tld_idle || [];
        const total = summary.total || 0;

        // Tampilkan summary counter di atas
        $('#count-di-lab').text(summary.tld_di_lab || 0);
        $('#count-evaluasi').text(summary.tld_evaluasi || 0);
        $('#count-sewa').text(summary.tld_sewa || 0);
        $('#count-idle').text(summary.tld_idle || 0);
        $('#summary-counter').css('display', '');
        $('#total-label').html(`<i class="bi bi-activity text-primary me-1"></i> Total <b>${total}</b> TLD terpantau`);

        if (total === 0) {
            $('#empty-state').removeClass('d-none');
            return;
        }

        // ---------- Gabungkan data TLD di LAB (Terikat Kontrak, Evaluasi, Sewa) ----------
        allLabTlds = [];

        diLab.forEach(function (tld) {
            allLabTlds.push({
                ...tld,
                tipe_penyimpanan: 'di_lab',
                label_tipe: 'Di Lab'
            });
        });

        evalasi.forEach(function (tld) {
            allLabTlds.push({
                ...tld,
                tipe_penyimpanan: 'evaluasi',
                label_tipe: 'Evaluasi'
            });
        });

        sewa.forEach(function (tld) {
            allLabTlds.push({
                ...tld,
                tipe_penyimpanan: 'sewa',
                label_tipe: 'Sewa'
            });
        });

        // ---------- TLD Idle ----------
        allIdleTlds = idle;

        // ---------- Render Data Terpaginasi ----------
        if (allLabTlds.length > 0) {
            $('#badge-di-lab').text(allLabTlds.length);
            $('#section-di-lab').removeClass('d-none');
            renderLabTlds(1);
        } else {
            $('#section-di-lab').addClass('d-none');
        }

        if (allIdleTlds.length > 0) {
            $('#badge-idle').text(allIdleTlds.length);
            $('#section-idle').removeClass('d-none');
            renderIdleTlds(1);
        } else {
            $('#section-idle').addClass('d-none');
        }

    }, function (err) {
        $('#placeholder-container').addClass('d-none');
        $('#empty-state').removeClass('d-none');
        console.error('Error fetching penyimpanan TLD data:', err);
    });
}

function renderLabTlds(page = 1) {
    currentPageLab = page;
    const startIndex = (page - 1) * limitPerPage;
    const endIndex = startIndex + limitPerPage;
    const paginatedData = allLabTlds.slice(startIndex, endIndex);

    let html = '';
    if (paginatedData.length === 0) {
        html = `
            <div class="text-center py-4 text-muted">
                <i class="bi bi-inbox fs-3 d-block mb-2 text-secondary"></i>
                Tidak ada data TLD Aktif di LAB.
            </div>
        `;
        $('#container-di-lab').html(html);
        $('#pagination-di-lab').html('');
        return;
    }

    paginatedData.forEach(function (tld) {
        // Tentukan warna border dan badge berdasarkan tipe
        let borderClass = '';
        let statusBadge = '';
        let actionBtn = '';

        if (tld.tipe_penyimpanan === 'di_lab') {
            borderClass = 'border-left-lab';
            statusBadge = `<span class="badge bg-primary-subtle text-primary rounded-pill tld-badge"><i class="bi bi-archive me-1"></i>Di Lab</span>`;

            actionBtn = tld.penyelia_hash
                ? `<a href="/staff/lhu?md=${tld.penyelia_hash}" class="btn btn-outline-primary btn-sm fw-semibold">
                        <i class="bi bi-arrow-up-right-circle me-1"></i>Update LHU
                   </a>`
                : `<button class="btn btn-outline-secondary btn-sm fw-semibold" disabled title="Alur LHU belum tersedia">
                        <i class="bi bi-exclamation-circle me-1"></i>Belum Ada LHU
                   </button>`;
        } else if (tld.tipe_penyimpanan === 'evaluasi') {
            borderClass = 'border-left-evaluasi';
            statusBadge = `<span class="badge bg-warning-subtle text-warning rounded-pill tld-badge"><i class="bi bi-graph-up-arrow me-1"></i>Evaluasi</span>`;
        } else if (tld.tipe_penyimpanan === 'sewa') {
            borderClass = 'border-left-sewa';
            statusBadge = `<span class="badge rounded-pill tld-badge" style="background:rgba(139,92,246,.12);color:#8b5cf6;"><i class="bi bi-box-seam me-1"></i>Sewa</span>`;
        }

        const periodeLbl = tld.periode == 0 ? 'Zero Check' : 'Periode ' + tld.periode;
        let rangeDate = '';
        if (tld.periodenow && tld.periodenow.periode != 0) {
            rangeDate = ': ' + range_date(tld.periodenow.start_date, tld.periodenow.end_date, 1).start +
                ' - ' + range_date(tld.periodenow.start_date, tld.periodenow.end_date, 1).end;
        }

        html += `
            <div class="card ${borderClass} mb-1 hover-effect transition-all">
                <div class="card-body d-flex flex-row align-items-center g-3">
                    <div class="col-12 col-md-4">
                        <div class="d-flex align-items-center gap-2">
                            <span class="font-monospace fw-bold text-dark fs-3">${tld.no_seri_tld}</span>
                        </div>
                        <div class="text-secondary small">
                            <div class="d-flex align-items-center gap-2">
                                ${statusBadge}
                                <span class="badge bg-light text-secondary border rounded-pill py-1 px-2">
                                    <i class="bi bi-tag me-1"></i>${tld.jenis_tld || '-'}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-5">
                        <div class="fw-semibold text-dark fs-8">${tld.perusahaan}</div>
                        <div class="text-muted small mt-1">
                            <span class="me-2"><i class="bi bi-hash"></i><b>${tld.no_kontrak}</b></span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted fs-8">Digunakan : <i class="bi bi-person me-1"></i>${tld.pengguna || '-'}</span>
                        </div>
                        <div class="text-muted small mt-1">
                            <span><i class="bi bi-calendar-range me-1"></i>${periodeLbl}${rangeDate}</span>
                        </div>
                    </div>
                    <div class="col-12 col-md-3 text-md-end">
                        <div>
                            ${actionBtn}
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    $('#container-di-lab').html(html);

    // Render pagination
    const totalPages = Math.ceil(allLabTlds.length / limitPerPage);
    const paginationHtml = createPaginationHTML({
        current_page: page,
        last_page: totalPages
    });
    $('#pagination-di-lab').html(paginationHtml);
}

function renderIdleTlds(page = 1) {
    currentPageIdle = page;
    const startIndex = (page - 1) * limitPerPage;
    const endIndex = startIndex + limitPerPage;
    const paginatedData = allIdleTlds.slice(startIndex, endIndex);

    let html = '';
    if (paginatedData.length === 0) {
        html = `
            <div class="text-center py-4 text-muted">
                <i class="bi bi-inbox fs-3 d-block mb-2 text-secondary"></i>
                Tidak ada data TLD Idle.
            </div>
        `;
        $('#container-idle').html(html);
        $('#pagination-idle').html('');
        return;
    }

    paginatedData.forEach(function (tld) {
        let historyHtml = '';
        if (tld.last_history) {
            const usedAt = tld.last_history.used_at ? dateFormat(tld.last_history.used_at, 2) : '-';
            historyHtml = `
                <span class="badge history-badge rounded-pill mt-1">
                    <i class="bi bi-clock-history me-1"></i>
                    Terakhir: ${tld.last_history.no_kontrak} — Periode ${tld.last_history.periode} (${usedAt})
                </span>
            `;
        } else {
            historyHtml = `
                <span class="badge bg-light text-muted border rounded-pill mt-1">
                    <i class="bi bi-star me-1"></i>Belum pernah digunakan
                </span>
            `;
        }

        html += `
            <div class="card border-left-idle mb-1 hover-effect transition-all">
                <div class="card-body d-flex flex-row align-items-center g-3">
                    <div class="col-12 col-md-4">
                        <div class="d-flex align-items-center gap-2">
                            <span class="font-monospace fw-bold text-dark fs-3">${tld.no_seri_tld}</span>
                        </div>
                        <div class="text-secondary small">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-success-subtle text-success rounded-pill tld-badge"><i class="bi bi-check2-circle me-1"></i>Idle</span>
                                <span class="badge bg-light text-secondary border rounded-pill py-1 px-2">
                                    <i class="bi bi-tag me-1"></i>${tld.jenis_tld || '-'}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-5">
                        <div class="fw-semibold text-secondary small">History Terakhir:</div>
                        <div class="mt-1">${historyHtml}</div>
                    </div>
                    <div class="col-12 col-md-3 text-md-end">
                        ${tld.merk ? `
                        <div>
                            <span class="badge bg-light text-dark border rounded-pill py-1 px-2">
                                <i class="bi bi-tools me-1"></i>${tld.merk}
                            </span>
                        </div>` : ''}
                    </div>
                </div>
            </div>
        `;
    });

    $('#container-idle').html(html);

    // Render pagination
    const totalPages = Math.ceil(allIdleTlds.length / limitPerPage);
    const paginationHtml = createPaginationHTML({
        current_page: page,
        last_page: totalPages
    });
    $('#pagination-idle').html(paginationHtml);
}

function clearFilter() {
    filterComp.clear();
    loadPenyimpananData();
}

function reload() {
    loadPenyimpananData();
}
