/**
 * penyimpanan.js
 * Halaman penyimpanan TLD — tampilan dikelompokkan per nomor kontrak.
 * Struktur API response: { summary, kontrak[] }
 * Setiap kontrak berisi: no_kontrak, perusahaan, tipe, kontrak_pengguna,
 *   kontrak_kontrol, semua_kembali, tlds[]
 */

// State Global untuk Client-side Pagination
let allKontrakData = [];
let currentPage = 1;
const limitPerPage = 8;
let filterComp = false;

$(function () {
    // Inisialisasi FilterComponent
    filterComp = new FilterComponent('list-filter', {
        jenis: 'penyimpanan',
        filter: {
            no_kontrak: true,
        },
        placeholder: {
            search: 'Cari No. Kontrak, Seri TLD, atau Pengguna...'
        }
    });

    // Event listener saat filter berubah
    filterComp.on('filter.change', () => loadPenyimpananData());

    loadPenyimpananData();

    // Pagination
    $(document).on('click', '#pagination-kontrak .page-link', function (e) {
        e.preventDefault();
        const pageno = parseInt($(this).attr('data-page'));
        if (pageno) renderKontrak(pageno);
    });

    // Buka modal detail via event delegation (hindari masalah escaping JSON di onclick)
    $(document).on('click', '.btn-detail-tld', function () {
        const idx = parseInt($(this).attr('data-idx'));
        if (!isNaN(idx) && allKontrakData[idx]) {
            showDetailModal(allKontrakData[idx]);
        }
    });
});

// ============================================================
// LOAD DATA
// ============================================================
function loadPenyimpananData() {
    // Reset tampilan
    $('#placeholder-container').removeClass('d-none');
    $('#section-kontrak, #empty-state').addClass('d-none');
    $('#summary-counter').css('display', 'none');
    // $('#total-label').text('');

    let params = { filter: {} };
    let filterValue = filterComp && filterComp.getAllValue();
    if (filterValue) {
        filterValue.no_kontrak && (params.filter.id_kontrak = filterValue.no_kontrak);
        filterValue.search && (params.filter.search = filterValue.search);
        filterValue.status && (params.filter.status = filterValue.status);
        (filterValue.date_range && filterValue.date_range.length == 2) && (params.filter.date_range = filterValue.date_range);
    }

    // Tampilkan jumlah filter aktif
    if (filterValue && Object.keys(params.filter).length > 0) {
        $('#countFilter').html(Object.keys(params.filter).length).removeClass('d-none');
    } else {
        $('#countFilter').addClass('d-none');
    }

    ajaxGet('api/v1/tld/getPenyimpanan', params, function (result) {
        $('#placeholder-container').addClass('d-none');

        const data = result.data || {};
        const summary = data.summary || {};
        const kontrak = data.kontrak || [];

        // Update summary counter
        // $('#count-kontrak').text(summary.total_kontrak || 0);
        // $('#count-belum-kembali').text(summary.belum_kembali || 0);
        // $('#count-sudah-kembali').text(summary.sudah_kembali || 0);
        $('#summary-counter').css('display', '');
        // $('#total-label').html(
        //     `<i class="bi bi-activity text-primary me-1"></i> Total <b>${summary.total_tld || 0}</b> TLD terpantau`
        // );

        if (!kontrak.length) {
            $('#empty-state').removeClass('d-none');
            return;
        }

        allKontrakData = kontrak;
        $('#badge-kontrak').text(kontrak.length);
        $('#section-kontrak').removeClass('d-none');
        renderKontrak(1);

    }, function (err) {
        $('#placeholder-container').addClass('d-none');
        $('#empty-state').removeClass('d-none');
        console.error('Error fetching penyimpanan TLD data:', err);
    });
}

// ============================================================
// RENDER KARTU PER KONTRAK
// ============================================================
function renderKontrak(page = 1) {
    currentPage = page;
    const startIndex = (page - 1) * limitPerPage;
    const paginatedData = allKontrakData.slice(startIndex, startIndex + limitPerPage);

    if (!paginatedData.length) {
        $('#container-kontrak').html(`
            <div class="text-center py-4 text-muted">
                <i class="bi bi-inbox fs-3 d-block mb-2 text-secondary"></i>
                Tidak ada data kontrak.
            </div>
        `);
        $('#pagination-kontrak').html('');
        return;
    }

    let html = '';
    paginatedData.forEach(function (k) {
        const tipeBadge = getTipeBadge(k.tipe);

        html += `
            <div class="card kontrak-card tipe-${k.tipe} mb-2 shadow-xs">
                <div class="card-body py-3">
                    <div class="row align-items-center">
                        <div class="col-12 col-md-6">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                ${tipeBadge}
                                <span class="fw-bold text-dark font-monospace">${k.no_kontrak}</span>
                            </div>
                            <div class="text-muted fs-8 mb-1">
                                <i class="bi bi-building me-1"></i>${k.perusahaan}
                            </div>
                        </div>

                        <div class="col-12 col-md-4 mt-2 mt-md-0">
                            <div class="text-muted fs-9 mb-1">Sesuai Kontrak</div>
                            <div class="fw-semibold text-dark fs-8">
                                <span class="me-2"><i class="bi bi-person me-1 text-primary"></i>${k.kontrak_pengguna} Pengguna</span>
                                <span><i class="bi bi-shield-check me-1 text-secondary"></i>${k.kontrak_kontrol} Kontrol</span>
                            </div>
                        </div>

                        <div class="col-12 col-md-2 text-md-end mt-3 mt-md-0">
                            <button class="btn btn-outline-primary btn-sm rounded-pill fw-semibold btn-detail-tld"
                                data-idx="${startIndex + paginatedData.indexOf(k)}">
                                <i class="bi bi-list-ul me-1"></i>Detail TLD
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    $('#container-kontrak').html(html);

    // Render pagination
    const totalPages = Math.ceil(allKontrakData.length / limitPerPage);
    $('#pagination-kontrak').html(createPaginationHTML({ current_page: page, last_page: totalPages }));
}

// ============================================================
// HELPER: Badge tipe layanan
// ============================================================
function getTipeBadge(tipe) {
    const map = {
        evaluasi: `<span class="badge rounded-pill" style="background:rgba(245,158,11,.12);color:#d97706;border:1px solid rgba(245,158,11,.25)"><i class="bi bi-graph-up-arrow me-1"></i>Evaluasi</span>`,
        sewa: `<span class="badge rounded-pill" style="background:rgba(139,92,246,.12);color:#8b5cf6;border:1px solid rgba(139,92,246,.25)"><i class="bi bi-box-seam me-1"></i>Sewa</span>`,
        di_lab: `<span class="badge rounded-pill" style="background:rgba(59,130,246,.12);color:#3b82f6;border:1px solid rgba(59,130,246,.25)"><i class="bi bi-archive me-1"></i>Di Lab</span>`,
    };
    return map[tipe] || `<span class="badge bg-secondary">${tipe}</span>`;
}

// ============================================================
// HELPER: Status pengembalian
// ============================================================
function getStatusHtml(semuaKembali) {
    if (semuaKembali) {
        return `<span class="badge rounded-pill text-bg-success py-1 px-2">
                    <i class="bi bi-check2-circle me-1"></i>Semua Sudah Kembali
                </span>`;
    }
    return `<span class="badge rounded-pill text-bg-warning py-1 px-2">
                <i class="bi bi-hourglass-split me-1"></i>Belum Kembali
            </span>`;
}

// ============================================================
// MODAL DETAIL TLD
// ============================================================
function showDetailModal(kontrak) {
    $('#modal-detail-tld-label').text(`Detail TLD — ${kontrak.no_kontrak}`);
    $('#modal-detail-tld-subtitle').text(kontrak.perusahaan);

    const tlds = kontrak.tlds || [];
    if (!tlds.length) {
        $('#modal-detail-tld-body').html(`
            <div class="text-center py-4 text-muted">
                <i class="bi bi-inbox fs-3 d-block mb-2"></i>Tidak ada data TLD.
            </div>
        `);
        $('#modal-detail-tld').modal('show');
        return;
    }

    // Kelompokkan TLD berdasarkan periode
    const tldsByPeriode = {};
    tlds.forEach(tld => {
        const p = tld.periode;
        if (!tldsByPeriode[p]) {
            tldsByPeriode[p] = [];
        }
        tldsByPeriode[p].push(tld);
    });

    // Urutkan periode ascending
    const periodes = Object.keys(tldsByPeriode).sort((a, b) => {
        const pA = parseInt(a);
        const pB = parseInt(b);
        return pA - pB;
    });

    let tabNavHtml = '<ul class="nav nav-tabs mb-3" id="tld-periode-tabs" role="tablist">';
    let tabContentHtml = '<div class="tab-content" id="tld-periode-tabs-content">';

    periodes.forEach((p, idx) => {
        const isActive = idx === 0;
        const activeClass = isActive ? 'active' : '';
        const showActiveClass = isActive ? 'show active' : '';

        // Urutkan TLD dalam satu periode berdasarkan no_seri_tld
        const tldsInPeriode = tldsByPeriode[p].sort((a, b) => (a.no_seri_tld || '').localeCompare(b.no_seri_tld || ''));
        const totalInPeriode = tldsInPeriode.length;

        // Tab Header
        const tabTitle = p == 0 ? 'Zero Check' : `Periode ${p}`;
        tabNavHtml += `
            <li class="nav-item" role="presentation">
                <button class="nav-link ${activeClass} fw-semibold" id="tab-p-${p}-tab" data-bs-toggle="tab" data-bs-target="#tab-p-${p}" type="button" role="tab">
                    ${tabTitle} <span class="badge bg-light text-dark border ms-1 fs-9">${totalInPeriode}</span>
                </button>
            </li>
        `;

        // Ringkasan untuk periode ini saja
        const sudahKembali = tldsInPeriode.filter(t => t.status_tld === 1 || t.status_tld === 5).length;
        const belumKembali = totalInPeriode - sudahKembali;

        const ringkasanHtml = belumKembali > 0
            ? `<div class="alert alert-warning py-2 mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span><b>${belumKembali}</b> dari <b>${totalInPeriode}</b> TLD belum kembali ke lab untuk periode ini.</span>
               </div>`
            : `<div class="alert alert-success py-2 mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-check2-circle-fill"></i>
                    <span>Semua <b>${totalInPeriode}</b> TLD sudah kembali ke lab untuk periode ini.</span>
               </div>`;

        // Baris tabel
        let rows = '';
        tldsInPeriode.forEach(function (tld, i) {
            const isKembali = tld.status_tld === 1 || tld.status_tld === 5;
            const rowClass = isKembali ? 'tld-row-kembali' : '';

            // Tentukan kelas badge & ikon berdasarkan status TLD yang baru
            let badgeClass = 'text-bg-secondary';
            let iconClass = 'bi-info-circle';
            if (isKembali) {
                badgeClass = 'text-bg-success';
                iconClass = 'bi-check2';
            } else if (tld.status_tld === 2) {
                badgeClass = 'text-bg-secondary';
                iconClass = 'bi-person';
            } else if (tld.status_tld === 3) {
                badgeClass = 'text-bg-warning text-dark';
                iconClass = 'bi-graph-up-arrow';
            } else if (tld.status_tld === 6) {
                badgeClass = 'text-bg-info text-dark';
                iconClass = 'bi-send';
            }

            const statusBadge = `<span class="badge ${badgeClass} rounded-pill py-1 px-2"><i class="bi ${iconClass} me-1"></i>${tld.label_status}</span>`;

            const aksiBtn = (isKembali && tld.penyelia_hash)
                ? `<a href="/staff/lhu?md=${tld.penyelia_hash}" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill">
                        <i class="bi bi-arrow-up-right-circle me-1"></i>LHU
                   </a>`
                : `<span class="text-muted fs-9">—</span>`;

            const jenisBadge = tld.jenis === 'kontrol'
                ? `<span class="badge bg-secondary-subtle text-secondary rounded-pill">Kontrol</span>`
                : '';

            rows += `
                <tr class="${rowClass}">
                    <td class="text-center text-muted">${i + 1}</td>
                    <td class="font-monospace fw-semibold">${tld.no_seri_tld} ${jenisBadge}</td>
                    <td>${tld.jenis === 'kontrol' ? '<span class="text-muted">—</span>' : tld.pengguna}</td>
                    <td>${statusBadge}</td>
                    <td class="text-end">${aksiBtn}</td>
                </tr>
            `;
        });

        // Tab Content Pane
        tabContentHtml += `
            <div class="tab-pane fade ${showActiveClass}" id="tab-p-${p}" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="3%" class="text-center">#</th>
                                <th width="25%">No. Seri TLD</th>
                                <th>Pemakai</th>
                                <th width="20%">Status</th>
                                <th width="12%" class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>
            </div>
        `;
    });

    tabNavHtml += '</ul>';
    tabContentHtml += '</div>';

    $('#modal-detail-tld-body').html(tabNavHtml + tabContentHtml);
    $('#modal-detail-tld').modal('show');
}

// ============================================================
// UTILS
// ============================================================
function clearFilter() {
    filterComp.clear();
    loadPenyimpananData();
}

function reload() {
    loadPenyimpananData();
}
