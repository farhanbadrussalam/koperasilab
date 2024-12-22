let dataPermohonan = false;
$(function () {
    loadData();
});

function loadData(page = 1, menu) {
    let params = {
        limit: 3,
        page: page,
        menu: menu
    };

    $(`#list-placeholder-list`).show();
    $(`#list-container-list`).hide();
    ajaxGet(`api/v1/pengiriman/listPermohonan`, params, result => {
        // Mengambil periode
        dataPermohonan = result.data;
        let html = '';
        for (const [i, data] of result.data.entries()) {
            let arrPeriode = data.kontrak.periode;
            let urlLaporanInvoice = data.invoice?.status == 5 ? `<a href="${base_url}/laporan/invoice/${data.invoice.keuangan_hash}" class="text-black" target="_blank" ><i class="bi bi-printer-fill"></i> Cetak Invoice</a>` : '<i class="bi bi-printer-fill"></i> Cetak Invoice';
            let urlDocLhu = data.lhu?.status == 3 ? `<a href="${base_url}/storage/${data.lhu.media.file_path}/${data.lhu.media.file_hash}" class="text-black" target="_blank" ><i class="bi bi-printer-fill"></i> Cetak LHU</a>` : '<i class="bi bi-printer-fill"></i> Cetak LHU';

            // Data Invoice
            let htmlInvoice = '';
            data.invoice ? htmlInvoice = `
                <div class="col-md-12 mt-2">
                    <div class="border-top py-2 d-flex justify-content-between align-items-center">
                        <div class="px-2">
                            <span class="fw-semibold fs-6">Invoice</span>
                            <small class="text-body-tertiary"> - ${data.invoice.no_invoice}</small>
                            <small>${statusFormat('pengiriman', data.invoice.pengiriman?.status)}</small>
                        </div>
                        <div class="d-flex align-items-center gap-3 text-secondary">
                            <small><i class="bi bi-calendar-fill"></i> ${dateFormat(data.invoice.created_at, 4)}</small>
                            <small>${statusFormat('invoice', data.invoice.status)}</small>
                            <small class="bg-body-tertiary rounded-pill ${data.invoice.status == 5 ? "cursoron" : "cursordisable"} hover-1 border border-dark-subtle px-2">${urlLaporanInvoice}</small>
                        </div>
                    </div>
                </div>
            ` : false;

            // Data layanan jasa (TLD)
            let htmlTld = `
                <div class="col-md-12 mt-2">
                    <div class="border-top py-2 d-flex justify-content-between align-items-center">
                        <div class="px-2">
                            <span class="fw-semibold fs-6">${data.layanan_jasa.nama_layanan}</span>
                            <small class="text-body-tertiary"> - ${data.jumlah_pengguna + data.jumlah_kontrol} PCS</small>
                            <small>${statusFormat('pengiriman', data.pengiriman?.status)}</small>
                        </div>
                        <div class="d-flex align-items-center gap-3 text-secondary">
                        </div>
                    </div>
                </div>
            `;

            // Data LHU
            let htmlLhu = '';
            data.lhu ? htmlLhu = `
                <div class="col-md-12 mt-2">
                    <div class="border-top py-2 d-flex justify-content-between align-items-center">
                        <div class="px-2">
                            <span class="fw-semibold fs-6">LHU</span>
                            <small class="text-body-tertiary"> - Periode ${data.lhu.periode}</small>
                            <small>${statusFormat('pengiriman', data.lhu.pengiriman?.status)}</small>
                        </div>
                        <div class="d-flex align-items-center gap-3 text-secondary">
                            <small><i class="bi bi-calendar-fill"></i> ${dateFormat(data.lhu.created_at, 4)}</small>
                            <small>${statusFormat('penyelia', data.lhu.status)}</small>
                            <small class="bg-body-tertiary rounded-pill ${data.lhu.status == 3 ? "cursoron" : "cursordisable"} hover-1 border border-dark-subtle px-2">${urlDocLhu}</small>
                        </div>
                    </div>
                </div>
            ` : false;

            let htmlBtn = '';
            if(!data.invoice.pengiriman && !data.lhu.pengiriman){
                htmlBtn += `<a class="btn btn-outline-primary" href="${base_url}/staff/pengiriman/permohonan/kirim/${data.permohonan_hash}"><i class="bi bi-send-fill"></i> Kirim document</a>`;
            }
            html += `
                <div class="card mb-2">
                    <div class="card-body row align-items-center py-2">
                        <div class="col-auto">
                            <div class="">
                                <span class="badge bg-primary-subtle fw-normal rounded-pill text-secondary-emphasis">${data.tipe_kontrak}</span>
                                <span class="badge bg-secondary-subtle fw-normal rounded-pill text-secondary-emphasis">${data.jenis_layanan_parent.name} - ${data.jenis_layanan.name}</span>
                            </div>
                            <div class="fs-5 my-2"><span class="fw-bold">${data.jenis_tld.name} - ${data.pelanggan.perusahaan.nama_perusahaan}</span> <span class="text-body-tertiary">${data.kontrak ? "#"+data.kontrak.no_kontrak : ''}</span></div>
                            <div class="d-flex gap-3 text-body-tertiary">
                                <div class="bg-body-tertiary rounded-pill cursoron hover-1 border border-dark-subtle px-2" onclick="showPeriode(${i})">${arrPeriode.length} Periode</div>
                                <div><i class="bi bi-person-check-fill"></i> ${data.pelanggan.name}</div>
                                <div><i class="bi bi-calendar-fill"></i> ${dateFormat(data.created_at, 4)}</div>
                            </div>
                        </div>
                        <div class="col-auto ms-auto">
                            ${htmlBtn}
                        </div>
                        ${htmlTld}
                        ${htmlInvoice}
                        ${htmlLhu}
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

        $(`#list-container-list`).html(html);

        $(`#list-pagination-list`).html(createPaginationHTML(result.pagination));

        $(`#list-placeholder-list`).hide();
        $(`#list-container-list`).show();
    }, error => {
        const result = error.responseJSON;
        if(result?.meta?.code && result?.meta?.code == 500){
            Swal.fire({
                icon: "error",
                text: 'Server error',
            });
            console.error(result.data.msg);
        }else{
            Swal.fire({
                icon: "error",
                text: 'Server error',
            });
            console.error(error);
        }
    });
}

$('#list-pagination-list').on('click', 'a', function (e) {
    e.preventDefault();
    const pageno = e.target.dataset.page;
    
    loadData(pageno);
});

function showPeriode(index) {
    const arrPeriode = dataPermohonan[index].kontrak.periode;
    const periodeJs = new Periode(arrPeriode, {
        preview: true,
        max: arrPeriode.length
    });

    periodeJs.show();
    periodeJs.on('periode.hide.modal', () => {
        periodeJs.destroy();
    });
}