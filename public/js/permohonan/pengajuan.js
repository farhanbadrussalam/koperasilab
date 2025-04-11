let thisTab = 1;
let thisStatus = false;
let detail = false;
let filterComp = false;
$(function () {
    switchLoadTab(1);

    detail = new Detail({
        jenis: 'permohonan',
        tab: {
            pengguna: true,
            periode: true,
            tld: true
        }
    });

    filterComp = new FilterComponent('pengajuan-filter', {
        filter : {
            jenis_tld : true,
            jenis_layanan : true,
            no_kontrak : true
        }
    })

    // SETUP FILTER
    filterComp.on('filter.change', () => switchLoadTab(thisTab));
})

$('#pagination_list').on('click', 'a', function (e) {
    e.preventDefault();
    const pageno = e.target.dataset.page;
    
    loadData(pageno, thisStatus);
});

function switchLoadTab(menu){
    thisTab = menu;
    switch (menu) {
        case 1:
            thisStatus = [1,2,3,4,5];
            break;

        case 2:
            thisStatus = [1];
            break;

        case 3:
            thisStatus = [2];
            break;

        case 4:
            thisStatus = [3];
            break;

        case 5:
            thisStatus = [5];
            break;

        case 6:
            thisStatus = [80];
            break;
    }
    loadData(1, thisStatus);
}

function loadData(page = 1, status) {
    let params = {
        limit: 4,
        page: page,
        status: status,
        filter: {}
    };

    let filterValue = filterComp && filterComp.getAllValue();
    
    filterValue.jenis_tld && (params.filter.jenis_tld = filterValue.jenis_tld);
    filterValue.status && (params.filter.status = filterValue.status);
    filterValue.jenis_layanan && (params.filter.jenis_layanan_1 = filterValue.jenis_layanan);
    filterValue.jenis_layanan_child && (params.filter.jenis_layanan_2 = filterValue.jenis_layanan_child);
    filterValue.no_kontrak && (params.filter.id_kontrak = filterValue.no_kontrak);

    if(Object.keys(params.filter).length > 0) {
        $('#countFilter').html(Object.keys(params.filter).length);
        $('#countFilter').removeClass('d-none');
    } else {
        $('#countFilter').addClass('d-none');
    }

    $('#pengajuan-placeholder').show();
    $('#pengajuan-list-container').hide();
    ajaxGet(`api/v1/permohonan/listPengajuan`, params, result => {
        let html = '';
        for (const [i, pengajuan] of result.data.entries()) {
            let btnEdit = `<a class="btn btn-sm btn-outline-warning" title="Edit" href="${base_url}/permohonan/pengajuan/edit/${pengajuan.permohonan_hash}"><i class="bi bi-pencil-square"></i> Edit</a>`;
            let btnRemove = `<button class="btn btn-sm btn-outline-danger" title="Delete" onclick="remove(this)"><i class="bi bi-trash"></i> Remove</button>`;

            let badgeClass = 'bg-primary-subtle';
            if(pengajuan.tipe_kontrak == 'kontrak lama') {
                badgeClass = 'bg-success-subtle';
            }

            if(thisTab == 6){
                html += `
                    <div class="card mb-2">
                        <div class="card-body row align-items-center">
                            <div class="col-12 col-md-4">
                                <div class="title">Layanan ${pengajuan.layanan_jasa?.nama_layanan ?? 'Untitled'}</div>
                                <small class="subdesc text-body-secondary fw-light lh-sm">
                                    <div>created : ${dateFormat(pengajuan.created_at, 4)}</div>
                                </small>
                            </div>
                            <div class="col-auto ms-auto">${statusFormat('permohonan', pengajuan.status)}</div>
                            <div class="col-auto text-end" data-id="${pengajuan.permohonan_hash}">
                                ${btnEdit}
                                ${btnRemove}
                            </div>
                        </div>
                    </div>`;
            } else {
                const params = {
                    tipeKontrak: pengajuan.tipe_kontrak,
                    jenisLayananParent: pengajuan.jenis_layanan_parent.name,
                    jenisLayanan: pengajuan.jenis_layanan.name,
                    format: 'permohonan',
                    status: pengajuan.status,
                    jenisTld: pengajuan.jenis_tld?.name ?? '-',
                    namaLayanan: pengajuan.layanan_jasa?.nama_layanan,
                    periode: pengajuan.periode,
                    created_at: pengajuan.created_at,
                    kontrak: pengajuan.kontrak?.no_kontrak,
                    id: pengajuan.permohonan_hash
                };
                const btnAction = `
                    <button class="btn btn-sm btn-outline-secondary" title="Show detail" onclick="showDetail(this)"><i class="bi bi-info-circle"></i> Detail</button>
                    ${pengajuan.status == 1 ? btnRemove : ''}
                `;

                html += cardComponent(params, {btnAction: btnAction});
            }
        }

        if(result.data.length == 0){
            html = htmlNoData();
        }

        $('#pengajuan-list-container').html(html);

        $('#pagination_list').html(createPaginationHTML(result.pagination));

        $('#pengajuan-placeholder').hide();
        $('#pengajuan-list-container').show();
    });

    countList();
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
            switchLoadTab(thisTab);
        });
    }, error => {
        const result = error.responseJSON;
        if(result?.meta?.code && result.meta.code == 500){
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
            console.error(result.message);
        }
    });
}

function showDetail(obj){
    const idPermohonan = $(obj).parent().data("id");
    let url = `api/v1/permohonan/getPengajuanById/${idPermohonan}`;
    detail.show(url);
}

function reload(){
    switchLoadTab(thisTab);
}

function clearFilter(){
    filterComp.clear();

    switchLoadTab(thisTab);
}

function countList(){
    ajaxGet('api/v1/permohonan/countList', false, result => {
        const count = result.data.reduce((acc, cur) => {
            acc[cur.name] = (acc[cur.name] || 0) + cur.total;
            return acc;
        }, {});
        Object.entries(count).forEach(([key, value]) => {
            const element = $(`#count${key}`);
            element.html(value === 0 ? "" : `(${value})`);
            element.toggle(value > 0);
        });
    });
}