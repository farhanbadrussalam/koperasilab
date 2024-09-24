$(function () {
    loadData();
});

function loadData(page = 1) {
    let params = {
        limit: 10,
        page: page,
        status: [90]
    };

    $('#list-placeholder').show();
    $('#list-container').hide();
    ajaxGet(`api/v1/permohonan/listPengajuan`, params, result => {
        let html = '';
        for (const [i, pengajuan] of result.data.entries()) {
            let periode = JSON.parse(pengajuan.periode_pemakaian);
            html += `
                <div class="card mb-2">
                    <div class="card-body row align-items-center">
                        <div class="col-12 col-md-3">
                            <div class="title">Layanan ${pengajuan.layanan_jasa.nama_layanan}</div>
                            <small class="subdesc text-body-secondary fw-light lh-sm">
                                <div>${pengajuan.jenis_tld.name}</div>
                                <div>Periode : ${periode.length} Bulan</div>
                                <div>Created : ${dateFormat(pengajuan.created_at, 4)}</div>
                            </small>
                        </div>
                        <div class="col-6 col-md-3 my-3">${pengajuan.jenis_layanan_parent.name}-${pengajuan.jenis_layanan.name}</div>
                        <div class="col-6 col-md-2 my-3 text-end text-md-start">${pengajuan.tipe_kontrak}</div>
                        <div class="col-6 col-md-2">${statusFormat('permohonan', pengajuan.status)}</div>
                        <div class="col-6 col-md-2 text-end" data-id="${pengajuan.permohonan_hash}">
                            <button class="btn btn-sm btn-outline-warning" title="Edit"><i class="bi bi-pencil-square"></i></button>
                            <button class="btn btn-sm btn-outline-danger" title="Delete" onclick="remove(this)"><i class="bi bi-trash"></i></button>
                        </div>
                        <small class="d-flex justify-content-between bg-body-secondary rounded mt-2 mb-0">
                            <div>Note : <span class="text-danger">Data permohonan ditolak</span></div>
                            <a href="#">Show</a>
                        </small>
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

        $('#list-container').html(html);

        $('#list-pagination').html(createPaginationHTML(result.pagination));

        $('#list-placeholder').hide();
        $('#list-container').show();
    });
}

function remove(obj){
    const idLayanan = $(obj).parent().data("id");
    ajaxDelete(`api/v1/permohonan/destroyPermohonan/${idLayanan}`, result => {
        Swal.fire({
            icon: 'success',
            text: result.data.msg,
            timer: 1200,
            timerProgressBar: true,
            showConfirmButton: false
        }).then(() => {
            loadData()
        });
    }, error => {
        const result = error.responseJSON;
        if(result.meta.code == 500){
            Swal.fire({
                icon: "error",
                text: 'Server error',
            });
            console.error(result.data.msg);
        }
    });
}