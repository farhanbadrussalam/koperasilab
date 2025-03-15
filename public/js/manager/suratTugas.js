let filterComp = false;
$(function () {
    loadData();

    detail = new Detail({
        jenis: 'surattugas',
        activeTab: 'proses',
        tab: {
            proses: true,
            log: true
        },
    });

    filterComp = new FilterComponent('list-filter', {
        jenis: 'penyelia',
        filter : {
            status : true,
            jenis_tld : true,
            jenis_layanan : true,
            no_kontrak : true,
            perusahaan: true
        }
    })

    // SETUP FILTER
    filterComp.on('filter.change', () => loadData());
});

function loadData(page=1) {
    let params = {
        limit: 10,
        page: page,
        menu: 'ttd-surat',
        filter: {}
    };

    let filterValue = filterComp && filterComp.getAllValue();
    filterValue.status && (params.filter.status = filterValue.status);
    filterValue.jenis_tld && (params.filter.jenis_tld = filterValue.jenis_tld);
    filterValue.jenis_layanan && (params.filter.jenis_layanan_1 = filterValue.jenis_layanan);
    filterValue.jenis_layanan_child && (params.filter.jenis_layanan_2 = filterValue.jenis_layanan_child);
    filterValue.no_kontrak && (params.filter.id_kontrak = filterValue.no_kontrak);
    filterValue.perusahaan && (params.filter.id_perusahaan = filterValue.perusahaan);

    if(Object.keys(params.filter).length > 0) {
        $('#countFilter').html(Object.keys(params.filter).length);
        $('#countFilter').removeClass('d-none');
    } else {
        $('#countFilter').addClass('d-none');
    }

    $(`#list-placeholder`).show();
    $(`#list-container`).hide();
    ajaxGet(`api/v1/penyelia/list`, params, result => {
        let html = '';
        const divTimelineTugas = [];
        for (const [i, lhu] of result.data.entries()) {
            const permohonan = lhu.permohonan;
            let btnAction = '<button class="btn btn-sm btn-outline-secondary me-1" title="Show detail" onclick="showDetail(this)"><i class="bi bi-info-circle"></i></button>';

            if(lhu.status == 2) {
                btnAction += `<a class="btn btn-outline-primary btn-sm" title="Verifikasi" href="${base_url}/manager/surat_tugas/v/${lhu.penyelia_hash}"><i class="bi bi-check2-circle"></i> Verifikasi</a>`
            }else{
                btnAction += `<a class="btn btn-outline-info btn-sm" href="${base_url}/manager/surat_tugas/s/${lhu.penyelia_hash}"><i class="bi bi-eye"></i> Show</a>`;
            }

            let badgeClass = 'bg-primary-subtle';
            if(permohonan.tipe_kontrak == 'kontrak lama') {
                badgeClass = 'bg-success-subtle';
            }

            let divInfoTugas = `
                <div class="col-md-12 mt-2 fs-7">
                    <div class="rounded bg-secondary-subtle ps-2 text-body-secondary d-flex justify-content-between align-items-center">
                        <span>Durasi pelaksanaan layanan ${dateFormat(lhu.start_date, 4)} s/d ${dateFormat(lhu.end_date, 4)}</span>
                        <a class="py-1 px-2 text-decoration-none border rounded-2" href="#timeline-progress-${lhu.penyelia_hash}" data-bs-toggle="collapse"
                        onclick="showHideProgress(this)">Lihat Progress LAB</a>
                    </div>
                </div>
            `;

            const timeline = new Timeline({
                timeline: lhu.penyelia_map,
                status: lhu.status,
                id: lhu.penyelia_hash
            });
            divTimelineTugas.push(timeline);

            html += `
                <div class="card mb-2">
                    <div class="card-body row align-items-center">
                        <div class="col-auto">
                            <div class="">
                                <span class="badge ${badgeClass} fw-normal rounded-pill text-secondary-emphasis">${permohonan.tipe_kontrak}</span>
                                <span class="badge bg-secondary-subtle fw-normal rounded-pill text-secondary-emphasis">${permohonan.jenis_layanan_parent.name} - ${permohonan.jenis_layanan.name}</span>
                                <span> | ${statusFormat('penyelia', lhu.status)}</span>
                            </div>
                            <div class="fs-5 my-2">
                                <span class="fw-bold">${permohonan.jenis_tld?.name ?? '-'} - Layanan ${permohonan.layanan_jasa?.nama_layanan}</span>
                                <div class="text-body-tertiary fs-7">
                                    <div><i class="bi bi-building-fill"></i> ${permohonan.pelanggan.perusahaan.nama_perusahaan}</div>
                                </div>
                            </div>
                            <div class="d-flex gap-3 text-body-tertiary fs-7">
                                <div><i class="bi bi-person-check-fill"></i> ${permohonan.pelanggan.name}</div>
                                <span><i class="bi bi-calendar-range"></i> ${permohonan.periode ? `Periode ${permohonan.periode}` : 'Zero cek'}</span>
                                <div><i class="bi bi-calendar-fill"></i> ${dateFormat(permohonan.created_at, 4)}</div>
                                ${permohonan.kontrak ? `<div><i class="bi bi-file-text"></i> ${permohonan.kontrak.no_kontrak}</div>` : ''}
                            </div>
                        </div>
                        <div class="col-6 col-md-2 text-center ms-auto" data-idpenyelia='${lhu.penyelia_hash}' data-surattugas='${lhu.no_surat_tugas}'>
                            ${btnAction}
                        </div>
                        ${divInfoTugas}
                        <div class="col-md-12 collapse" id="timeline-progress-${lhu.penyelia_hash}">
                            ${timeline.elementCreate()}
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

        $(`#list-container`).html(html);

        $(`#list-pagination`).html(createPaginationHTML(result.pagination));
        divTimelineTugas.map(d => d.render());
        $(`#list-placeholder`).hide();
        $(`#list-container`).show();
    });
}

function reload() {
    loadData();
}
function showDetail(obj){
    const idPenyelia = $(obj).parent().data("idpenyelia");
    detail.show(`api/v1/penyelia/getById/${idPenyelia}`);
}
function clearFilter(){}
function showHideProgress(obj){
    const collapse = obj;
    if(!collapse.classList.contains('show')) { 
        collapse.innerText = 'Lebih sedikit';
    } else { 
        collapse.innerText = 'Lihat Progress LAB'; 
    } 
    collapse.classList.toggle('show');
}