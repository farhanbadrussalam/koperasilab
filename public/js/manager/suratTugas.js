$(function () {
    loadData();

    detail = new Detail({
        jenis: 'surattugas',
        tab: {
            proses: true,
            log: true
        },
        activeTab: 'proses'
    });
});

function loadData(page=1) {
    let params = {
        limit: 10,
        page: page,
        menu: 'ttd-surat'
    }

    $(`#list-placeholder-surat-tugas`).show();
    $(`#list-container-surat-tugas`).hide();
    ajaxGet(`api/v1/penyelia/list`, params, result => {
        let html = '';
        const divTimelineTugas = [];
        for (const [i, lhu] of result.data.entries()) {
            const permohonan = lhu.permohonan;
            let arrPeriode = permohonan.kontrak?.periode ?? permohonan.periode_pemakaian.map((d, i) => ({...d, periode: i + 1}));
            let tgl_periode = arrPeriode.find(d => d.periode == lhu.periode);
            let btnAction = '<button class="btn btn-sm btn-outline-secondary me-1" title="Show detail" onclick="showDetail(this)"><i class="bi bi-info-circle"></i></button>';

            if(lhu.status == 2) {
                btnAction += `<a class="btn btn-outline-primary btn-sm" title="Verifikasi" href="${base_url}/manager/surat_tugas/v/${lhu.penyelia_hash}"><i class="bi bi-check2-circle"></i> Verifikasi</a>`
            }else{
                btnAction += `<a class="btn btn-outline-info btn-sm" href="${base_url}/manager/surat_tugas/s/${lhu.penyelia_hash}"><i class="bi bi-eye"></i> Show</a>`;
            }

            let divInfoTugas = `
                <div class="col-md-12">
                    <div class="rounded bg-secondary-subtle p-2 text-body-secondary d-flex justify-content-between">
                        <span>Status : ${statusFormat('penyelia', lhu.status)}</span>
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
                        <div class="col-12 col-md-3">
                            <div class="title">Layanan ${permohonan.layanan_jasa.nama_layanan}</div>
                            <small class="subdesc text-body-secondary fw-light lh-sm">
                                <div>${permohonan.jenis_tld.name}</div>
                                <div>${lhu.periode == 0 ? 'Zero cek' : `Periode ${lhu.periode} :`} </div>
                                ${lhu.periode == 0 ? '' : `<div>${tgl_periode ? dateFormat(tgl_periode.start_date, 5)+' - '+dateFormat(tgl_periode.end_date, 5) : ''}</div>`}
                                <div>Created : ${dateFormat(permohonan.created_at, 4)}</div>
                            </small>
                        </div>
                        <div class="col-6 col-md-3 my-3 text-end text-md-start">
                            <div>${permohonan.tipe_kontrak}</div>
                            <small class="subdesc text-body-secondary fw-light lh-sm">${permohonan.kontrak?.no_kontrak ?? ''}</small>
                        </div>
                        <div class="col-6 col-md-4 text-center">
                            <div class="fw-bolder">Start date</div>
                            <div>${dateFormat(lhu.start_date, 4)}</div>
                            <div class="fw-bolder">End date</div>
                            <div>${dateFormat(lhu.end_date, 4)}</div>
                        </div>
                        <div class="col-6 col-md-2 text-center" data-idpenyelia='${lhu.penyelia_hash}' data-surattugas='${lhu.no_surat_tugas}'>
                            ${btnAction}
                        </div>
                        ${timeline.elementCreate()}
                        ${divInfoTugas}
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

        $(`#list-container-surat-tugas`).html(html);

        $(`#list-pagination-surat-tugas`).html(createPaginationHTML(result.pagination));
        divTimelineTugas.map(d => d.render());
        $(`#list-placeholder-surat-tugas`).hide();
        $(`#list-container-surat-tugas`).show();
    });
}

function reload() {
    loadData();
}
function showDetail(obj){
    const idPenyelia = $(obj).parent().data("idpenyelia");
    detail.show(`api/v1/penyelia/getById/${idPenyelia}`);
}