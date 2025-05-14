$(function () {
    loadData();

    // mengecek apakah kode perusahaan sudah ada atau belum, jika sudah ada tidak akan bisa di simpan
    $('#kodeEditPerusahaan').on('input', obj => {
        const kode = obj.target.value;
        const kodeNow = $('#kodePerusahaan').val();
        const typeModal = $('#typeModal').val();
        if(kode){
            if(typeModal == 'edit' && kodeNow == kode){
                $('#simpanEditPerusahaan').attr('disabled', true);
                $('#errorKodePerusahaan').hide();
                return;
            }

            ajaxGet(`api/v1/profile/getPerusahaan/${kode}`, false, result => {
                if(result.data){
                    $('#errorKodePerusahaan').html(`<small class="text-danger">Kode sudah di gunakan</small>`);
                    $('#simpanEditPerusahaan').attr('disabled', true);
                    $('#errorKodePerusahaan').show();
                }else{
                    $('#errorKodePerusahaan').hide();
                    $('#errorKodePerusahaan').html('');
                    $('#simpanEditPerusahaan').attr('disabled', false);
                }
            });
        }else{
            $('#errorKodePerusahaan').hide();
            $('#errorKodePerusahaan').html('');
            $('#simpanEditPerusahaan').attr('disabled', false);
        }
    });
});

function loadData(page = 1) {
    let params = {
        limit: 10,
        page: page,
        filter: {

        }
    };

    $('#list-placeholder').show();
    $('#list-container').hide();
    ajaxGet(`api/v1/profile/list/perusahaan`, params, result => {
        let html = '';
        for (const [i, perusahaan] of result.data.entries()) {
            let btnAction = '';
            if(perusahaan.kode_perusahaan){
                btnAction = `<button class="btn btn-outline-warning btn-sm" data-id="${perusahaan.perusahaan_hash}" data-kode="${perusahaan.kode_perusahaan}" onclick="openModalEdit(this, 'edit')"><i class="bi bi-pencil-square"></i> Edit kode</button>`;
            }else{
                btnAction = `<button class="btn btn-outline-primary btn-sm" data-id="${perusahaan.perusahaan_hash}" data-kode="${perusahaan.kode_perusahaan}" onclick="openModalEdit(this), 'tambah'"><i class="bi bi-plus"></i> Tambah kode</button>`;
            }
            html += `
                <div class="card mb-2">
                    <div class="card-body row align-items-center">
                        <div class="col-12 col-md-8">
                            <div class="title">${perusahaan.nama_perusahaan} - ${perusahaan.kode_perusahaan ?? '<span class="text-danger">Belum ada kode</span>'}</div>
                            <small class="subdesc text-body-secondary fw-light lh-sm">
                                <div>NPWP : ${perusahaan.npwp_perusahaan}</div>
                                <div>E-mail : ${perusahaan.email}</div>
                            </small>
                        </div>
                        <div class="col-6 col-md-2 text-center ms-auto">
                            <div class="cursoron hover-1">
                                <span class="text-secondary ms-2"><i class="bi bi-people-fill"></i> ${perusahaan.users?.length ?? 0} Users</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-2 text-center">
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

        console.log(result);
        $('#list-container').html(html);

        $('#list-pagination').html(createPaginationHTML(result.pagination));

        $('#list-placeholder').hide();
        $('#list-container').show();
    })

}

function openModalEdit(obj, type){
    const id = $(obj).data('id');
    const kode = $(obj).data('kode');

    $('#typeModal').val(type);
    $('#idEditPerusahaan').val(id);
    $('#kodePerusahaan').val(kode);
    kode ? $('#kodeEditPerusahaan').val(kode) : $('#kodeEditPerusahaan').val('');

    $('#errorKodePerusahaan').hide();
    $('#errorKodePerusahaan').html('');
    $('#simpanEditPerusahaan').attr('disabled', true);

    $('#modalEditPerusahaan').modal('show');
}

function simpanEditPerusahaan(obj){
    const id = $('#idEditPerusahaan').val();
    const kode = $('#kodeEditPerusahaan').val();

    let params = new FormData();
    params.append('idPerusahaan', id);
    params.append('kodePerusahaan', kode);

    spinner('show', $(obj));
    ajaxPost(`api/v1/profile/action/perusahaan`, params, result => {
        if(result.meta.code == 200){
            Swal.fire({
                icon: "success",
                text: 'Kode perusahaan berhasil diupdate',
            });
            $('#modalEditPerusahaan').modal('hide');
            loadData();
        }else{
            Swal.fire({
                icon: "error",
                text: result.data.msg,
            });
        }
        spinner('hide', $(obj));
    }, error => {
        spinner('hide', $(obj));
    });
}

$('#list-pagination').on('click', 'a', function (e) {
    e.preventDefault();
    const pageno = e.target.dataset.page;
    
    loadData(pageno);
});