let nowTab = 1;
$(function () {
    switchLoadTab(1);
});

function switchLoadTab(menu){
    nowTab = menu;
    switch (menu) {
        case 1:
            menu = 'start';
            break;
    
        case 2:
            menu = 'anealing';
            break;
    
        case 3:
            menu = 'pembacaan';
            break;
    
        case 4:
            menu = 'selesai';
            break;
    }

    loadData(1, menu);
}

function loadData(page = 1, menu) {
    let params = {
        limit: 10,
        page: page,
        menu: menu
    };
    
    $(`#list-placeholder-${menu}`).show();
    $(`#list-container-${menu}`).hide();
    ajaxGet(`api/v1/penyelia/list`, params, result => {
        let html = '';
        for (const [i, lhu] of result.data.entries()) {
            const permohonan = lhu.permohonan;
            let periode = JSON.parse(permohonan.periode_pemakaian);
            let btnAction = '';

            if(menu == 'selesai') {
                btnAction = '';
            }else {
                btnAction = '<button class="btn btn-outline-primary btn-sm" title="Verifikasi" onclick="openProgressModal(this)"><i class="bi bi-check2-circle"></i> update progress</button>';
            }

            html += `
                <div class="card mb-2">
                    <div class="card-body row align-items-center">
                        <div class="col-12 col-md-3">
                            <div class="title">Layanan ${permohonan.layanan_jasa.nama_layanan}</div>
                            <small class="subdesc text-body-secondary fw-light lh-sm">
                                <div>${permohonan.jenis_tld.name}</div>
                                <div>Periode : ${periode.length} Bulan</div>
                                <div>Created : ${dateFormat(permohonan.created_at, 4)}</div>
                            </small>
                        </div>
                        <div class="col-6 col-md-2 my-3">${lhu.petugas.length} Petugas</div>
                        <div class="col-6 col-md-2 my-3 text-end text-md-start">
                            <div>${permohonan.tipe_kontrak}</div>
                            <small class="subdesc text-body-secondary fw-light lh-sm">${permohonan.no_kontrak}</small>
                        </div>
                        <div class="col-6 col-md-3 text-center">
                            <div class="fw-bolder">Start date</div>
                            <div>${dateFormat(lhu.start_date, 4)}</div>
                            <div class="fw-bolder">End date</div>
                            <div>${dateFormat(lhu.end_date, 4)}</div>
                        </div>
                        <div class="col-6 col-md-2 text-center" data-lhu='${JSON.stringify(lhu)}' data-surattugas='${lhu.no_surat_tugas}'>
                            ${btnAction}
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

        $(`#list-container-${menu}`).html(html);

        $(`#list-pagination-${menu}`).html(createPaginationHTML(result.pagination));

        $(`#list-placeholder-${menu}`).hide();
        $(`#list-container-${menu}`).show();
    }, error => {
        const result = error.responseJSON;
        if(result.meta?.code && result.meta.code == 500){
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
    })
}

function openProgressModal(obj){
    const lhu = $(obj).parent().data("lhu");
    const arrProgress = [
        {
            id: '3',
            val: 'Anealing'
        },
        {
            id: '4',
            val: 'Pembacaan'
        },
        {
            id: '5',
            val: 'Penerbitan LHU'
        },
    ];
    let filter = arrProgress.filter(d => d.id != lhu.status);
    let html = '<option value="">Pilih</option>';
    for (const select of filter) {
        html += `<option value="${select.id}">${select.val}</option>`;
    }

    $('#inputProgress').html(html);

    $('#updateProgressModal').modal('show');
    $('#txtIdPenyelia').val(lhu.penyelia_hash);
}

function simpanProgress(obj){
    let progress = $('#inputProgress').val();
    let note = $('#inputNote').val();
    let idPenyelia = $('#txtIdPenyelia').val();

    const form = new FormData();
    form.append('idPenyelia', idPenyelia);
    form.append('status', progress);
    form.append('note', note);

    spinner('show', $(obj));
    ajaxPost(`api/v1/penyelia/action`, form, result => {
        spinner('hide', $(obj));
        if(result.meta.code == 200){
            Swal.fire({
                icon: "success",
                text: 'Progress berhasil diupdate',
            });
            $('#updateProgressModal').modal('hide');
            switchLoadTab(nowTab);
        }else{
            Swal.fire({
                icon: "error",
                text: result.data.msg,
            });
        }
    }, error => {
        Swal.fire({
            icon: "error",
            text: 'Server error',
        });
        spinner('hide', $(obj));
        console.error(error.responseJSON.data.msg);
    });
}