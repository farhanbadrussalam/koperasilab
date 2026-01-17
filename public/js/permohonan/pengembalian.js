let filterComp = false;
$(function () {
    loadData();

    filterComp = new FilterComponent('pengajuan-filter', {
        filter : {
            jenis_tld : true,
            jenis_layanan : true,
            search: true,
            periode: true
        }
    })

    // SETUP FILTER
    filterComp.on('filter.change', () => loadData());
});

function loadData(page = 1) {
    let params = {
        limit: 10,
        page: page,
        status: [90],
        filter: {}
    };

    let filterValue = filterComp && filterComp.getAllValue();

    filterValue.jenis_tld && (params.filter.jenis_tld = filterValue.jenis_tld);
    filterValue.jenis_layanan && (params.filter.jenis_layanan = filterValue.jenis_layanan);
    filterValue.search && (params.filter.search = filterValue.search);
    filterValue.periode && (params.filter.periode = filterValue.periode);
    // (filterValue.date_range && filterValue.date_range.length == 2) && (params.filter.date_range = filterValue.date_range);

    if(Object.keys(params.filter).length > 0) {
        $('#countFilter').html(Object.keys(params.filter).length);
        $('#countFilter').removeClass('d-none');
    } else {
        $('#countFilter').addClass('d-none');
    }

    $('#list-placeholder').show();
    $('#list-container').hide();
    ajaxGet(`api/v1/permohonan/listPengajuan`, params, result => {
        let html = '';
        for (const [i, pengajuan] of result.data.entries()) {
            const params = {
                tipeKontrak: pengajuan.tipe_kontrak,
                jenisLayananParent: pengajuan.jenis_layanan_parent.name,
                jenisLayanan: pengajuan.jenis_layanan.name,
                format: 'permohonan',
                status: pengajuan.status,
                jenisTld: pengajuan.jenis_tld?.name ?? '-',
                namaLayanan: pengajuan.layanan_jasa?.nama_layanan ?? '-',
                periode: pengajuan.periode,
                created_at: pengajuan.created_at,
                kontrak: pengajuan.kontrak?.id_kontrak,
                id: pengajuan.permohonan_hash,
                is_have_tld: pengajuan.is_have_tld,
                is_zerocek: pengajuan.is_zerocek,
                note: pengajuan.note,
                pelanggan: pengajuan.pelanggan.name,
            }

            const btnAction = `
                <li>
                    <a class="dropdown-item small cursor-pointer" title="Edit" href="${base_url}/permohonan/pengajuan/edit/${pengajuan.permohonan_hash}">
                        <i class="bi bi-pencil-square me-2"></i> Edit
                    </a>
                </li>
                <li>
                    <a class="dropdown-item small cursor-pointer text-danger" title="Delete" onclick="remove(this)">
                        <i class="bi bi-trash me-2"></i> Remove
                    </a>
                </li>
            `;

            html += cardComponent(params, {btnMenuAction: btnAction});
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

function reload(){
    loadData();
}

function clearFilter(){
    filterComp.clear();

    loadData();
}
