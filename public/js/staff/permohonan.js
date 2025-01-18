let detail = false;
$(function () {
    loadData();
    detail = new Detail({
        jenis: 'permohonan',
        tab: {
            pengguna: true,
            activitas: false,
            dokumen: false,
            log: false
        }
    });
});

$('#list-pagination').on('click', 'a', function (e) {
    e.preventDefault();
    const pageno = e.target.dataset.page;
    
    loadPengajuan(pageno);
});

function loadData(page = 1) {
    let params = {
        limit: 10,
        page: page
    };

    $('#list-placeholder').show();
    $('#list-container').hide();
    ajaxGet(`api/v1/permohonan/listPengajuan`, params, result => {
        let html = '';
        for (const [i, pengajuan] of result.data.entries()) {
            let badgeClass = 'bg-primary-subtle';
            if(pengajuan.tipe_kontrak == 'kontrak lama') {
                badgeClass = 'bg-success-subtle';
            }

            let htmlAction = '';
            if(pengajuan.status == 1){
                htmlAction = `<a class="btn btn-outline-primary btn-sm" title="Verifikasi" href="${base_url}/staff/permohonan/verifikasi/${pengajuan.permohonan_hash}"><i class="bi bi-check2-circle"></i> Verifikasi</a>`;
            }
            console.log(pengajuan);

            html += `
                <div class="card mb-2 smooth-height">
                    <div class="card-body row align-items-center py-2">
                        <div class="col-auto">
                            <div class="">
                                <span class="badge ${badgeClass} fw-normal rounded-pill text-secondary-emphasis">${pengajuan.tipe_kontrak}</span>
                                <span class="badge bg-secondary-subtle fw-normal rounded-pill text-secondary-emphasis">${pengajuan.jenis_layanan_parent.name} - ${pengajuan.jenis_layanan.name}</span>
                            </div>
                            <div class="fs-5 my-2">
                                <span class="fw-bold">${pengajuan.jenis_tld?.name ?? '-'} - Layanan ${pengajuan.layanan_jasa?.nama_layanan}</span>
                            </div>
                            <div class="d-flex gap-3 text-body-tertiary fs-7">
                                <div><i class="bi bi-person-check-fill"></i> ${pengajuan.pelanggan.name}</div>
                                <span><i class="bi bi-calendar-range"></i> Periode ${pengajuan.periode}</span>
                                <div><i class="bi bi-calendar-fill"></i> ${dateFormat(pengajuan.created_at, 4)}</div>
                                <div><i class="bi bi-cash-stack"></i> ${formatRupiah(pengajuan.total_harga)}</div>
                            </div>
                        </div>
                        <div class="col-auto mx-auto">
                            <div>${statusFormat('permohonan', pengajuan.status)}</div>
                        </div>
                        <div class="col-md-2 ms-auto">
                            <div class="d-flex gap-1 flex-wrap justify-content-center" data-id="${pengajuan.permohonan_hash}">
                                <button class="btn btn-sm btn-outline-secondary" title="Show detail" onclick="showDetail(this)"><i class="bi bi-info-circle"></i> Detail</button>
                                ${htmlAction}
                            </div>
                        </div>
                        <div class="p-3" id="listPeriode" style="display:none"></div>
                    </div>
                </div>
            `;
        }

        if(result.data.length == 0){
            html = htmlNoData();
        }

        $('#list-container').html(html);

        $('#list-pagination').html(createPaginationHTML(result.pagination));

        $('#list-placeholder').hide();
        $('#list-container').show();
    })
}

function showDetail(obj){
    const idPermohonan = $(obj).parent().data("id");
    detail.show(`api/v1/permohonan/getPengajuanById/${idPermohonan}`);
}

function reload(){
    loadData();
}