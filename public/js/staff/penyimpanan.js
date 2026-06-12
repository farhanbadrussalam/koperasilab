$(function () {
    loadPenyimpananData();
});

function loadPenyimpananData() {
    $('#placeholder-container').removeClass('d-none');
    $('#data-container').addClass('d-none');
    $('#empty-state').addClass('d-none');

    ajaxGet('api/v1/tld/getPenyimpanan', {}, function (result) {
        $('#placeholder-container').addClass('d-none');
        
        if (!result.data || result.data.length === 0) {
            $('#empty-state').removeClass('d-none');
            return;
        }

        let html = '';
        result.data.forEach(function (group) {
            let tldListHtml = '';
            group.tlds.forEach(function (tld) {
                tldListHtml += `
                    <div class="d-flex align-items-center justify-content-between p-2 mb-2 rounded bg-light border-start border-3 border-info transition-hover">
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar-mini bg-info-subtle text-info rounded p-1 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                <i class="bi bi-cpu fs-9"></i>
                            </div>
                            <div>
                                <div class="fw-semibold text-dark fs-8">${tld.no_seri_tld}</div>
                                <div class="text-muted" style="font-size: 0.7rem;">Jenis: ${tld.jenis_tld ?? '-'}</div>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1 fs-9" title="Nama Pengguna TLD">
                                <i class="bi bi-person me-1"></i>${tld.pengguna}
                            </span>
                        </div>
                    </div>
                `;
            });

            let btnShortcut = '';
            if (group.penyelia_hash) {
                btnShortcut = `
                    <a href="/staff/lhu?md=${group.penyelia_hash}" class="btn btn-outline-primary btn-xs rounded-pill px-3 py-1 fw-semibold shadow-xs hover-slide">
                        <i class="bi bi-arrow-up-right-circle me-1"></i> Update Progress
                    </a>
                `;
            } else {
                btnShortcut = `
                    <button class="btn btn-outline-secondary btn-xs rounded-pill px-3 py-1 fw-semibold" disabled title="Alur LHU belum tersedia">
                        <i class="bi bi-exclamation-circle me-1"></i> Belum Ada Alur LHU
                    </button>
                `;
            }

            html += `
                <div class="col-12 col-md-6 col-lg-4 mb-4">
                    <div class="card border-0 shadow-sm h-100 card-premium">
                        <div class="card-header bg-white border-0 pt-3 pb-0">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1.5 fw-bold fs-9">
                                    Periode ${group.periode}
                                </span>
                                ${btnShortcut}
                            </div>
                            <h5 class="card-title fw-bold text-dark mb-1 text-truncate" title="${group.perusahaan}">
                                ${group.perusahaan}
                            </h5>
                            <div class="text-muted fs-8 mb-2 d-flex align-items-center gap-1.5">
                                <i class="bi bi-file-earmark-text"></i>
                                <span>Kontrak: <strong class="text-secondary">${group.no_kontrak}</strong></span>
                            </div>
                        </div>
                        <div class="card-body pt-2">
                            <hr class="mt-0 mb-3 text-secondary opacity-25">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="fw-bold text-secondary fs-8"><i class="bi bi-list-check me-1"></i> Daftar TLD (${group.tlds.length})</span>
                            </div>
                            <div class="tld-scroll-container" style="max-height: 200px; overflow-y: auto;">
                                ${tldListHtml}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        $('#data-container').html(html).removeClass('d-none');
    }, function (err) {
        $('#placeholder-container').addClass('d-none');
        $('#empty-state').removeClass('d-none');
        console.error('Error fetching penyimpanan TLD data:', err);
    });
}

function reload() {
    loadPenyimpananData();
}
