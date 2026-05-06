let modalDoc = new ModalDocument();
$(function () {
    loadData();

    $('#kode_perusahaan').on('input', (obj) => {
        const kode = obj.target.value;
        if(kode){
            // spinner('show', $('#labelKodePerusahaan'), {place: 'after'});
            ajaxGet(`api/v1/profile/getPerusahaan/${kode}`, false, result => {
                if(result.data){
                    $('#errorKodePerusahaan').html(`<small class="text-danger">Kode sudah di gunakan</small>`);
                    $('#errorKodePerusahaan').show();
                    $('#btnVerifikasiBaru').attr('disabled', true);
                }else{
                    $('#errorKodePerusahaan').hide();
                    $('#errorKodePerusahaan').html('');
                    $('#btnVerifikasiBaru').attr('disabled', false);
                }
                // spinner('hide', $('#labelKodePerusahaan'), {place: 'after'});
            });
        } else {
            $('#errorKodePerusahaan').hide();
            $('#errorKodePerusahaan').html('');
            $('#btnVerifikasiBaru').attr('disabled', false);
        }
    })
});

function loadData(page = 1) {
    let params = {
        limit: 10,
        page: page
    };

    $('#list-container').hide();
    // Panggil API untuk meload list approval pelanggan
    ajaxGet(`api/v1/pelanggan/approval/list`, params, result => {
        let html = '';
        for (const [i, req] of result.data.entries()) {
            // Flag Email Validation
            // let flagEmail = req.user.email_verified_at ? '<span class="badge bg-success">Email Terverifikasi</span>' : '<span class="badge bg-warning text-dark">Email Belum Terverifikasi</span>';
            let statusPelanggan = req.jenis == 'baru' ? '<span class="badge bg-info">Pelanggan Baru</span>' : '<span class="badge bg-secondary">Pelanggan Lama</span>';
            const profile = req.user.profile;

            let btnAction = `<button class="btn btn-outline-primary btn-sm" onclick="openModalVerifikasi('${req.request_user_hash}', '${req.jenis}')"><i class="bi bi-check-circle"></i> Verifikasi</button>`;
            btnAction += `
                <button class="btn btn-outline-info btn-sm"
                    data-url="storage/${profile.suratkuasa.file_path}/${profile.suratkuasa.file_hash}"
                    onclick="openModalDetail(this)">
                    <i class="bi bi-info-circle"></i> Surat Kuasa
                </button>
            `;

            html += `
                <div class="card mb-2 shadow-sm">
                    <div class="card-body row align-items-center">
                        <div class="col-12 col-md-5">
                            <div class="title"><span class="fw-bold">${req.perusahaan.nama_perusahaan ?? '-'}</span></div>
                            <small class="subdesc text-body-secondary fw-light lh-sm">
                                <div>PIC : ${req.user.name ?? '-'}</div>
                                <div class="mt-1">Email : ${req.user.email ?? '-'} </div>
                            </small>
                        </div>
                        <div class="col-6 col-md-4 text-center ms-auto">
                            ${statusPelanggan}
                        </div>
                        <div class="d-flex col-md-2 text-end gap-2 justify-content-end flex-column">
                            ${btnAction}
                        </div>
                    </div>
                </div>
            `;
        }

        if(result.data.length == 0){
            html = htmlNoData();
        }
        $('#list-container').html(html).show();
        $('#list-pagination').html(createPaginationHTML(result.pagination));
    });
}

function openModalVerifikasi(id, isNewCustomer) {
    $('#id_request').val(id);
    if(isNewCustomer == 'baru') {
        $('#kode_perusahaan').val('');
        $('#errorKodePerusahaan').hide();
        $('#modalVerifikasiBaru').modal('show');
    } else {
        $('#modalVerifikasiLama').modal('show');
    }
}

function verifikasiPelanggan(obj, type) {
    const id = $('#id_request').val();

    let params = new FormData();
    params.append('id_request', id);

    if (type === 'baru') {
        const kode_perusahaan = $('#kode_perusahaan').val();
        if (!kode_perusahaan) {
            return Swal.fire({ icon: 'warning', text: 'Kode perusahaan harus diisi' });
        }
        params.append('kode_perusahaan', kode_perusahaan);
    }

    spinner('show', $(obj));
    ajaxPost(`api/v1/pelanggan/approval/verifikasi`, params, result => {
        spinner('hide', $(obj));
        if(result.meta.code == 200) {
            Swal.fire({ icon: 'success', text: 'Pelanggan berhasil diverifikasi' });
            if (type === 'baru') {
                $('#modalVerifikasiBaru').modal('hide');
            } else {
                $('#modalVerifikasiLama').modal('hide');
            }
            loadData();
        }
    }, error => { spinner('hide', $(obj)); });
}

function tolakPelanggan(obj) {
    const id = $('#id_request').val();
    $('#modalVerifikasiBaru').modal('hide');
    $('#modalVerifikasiLama').modal('hide');

    showNoteAlertSwal((reason) => {
        let params = new FormData();
        params.append('id_request', id);
        params.append('catatan', reason);

        showLoadingSwal('show');
        ajaxPost(`api/v1/pelanggan/approval/tolak`, params, result => {
            if(result.meta.code == 200) {
                Swal.fire({ icon: 'success', text: 'Pelanggan berhasil ditolak' }).then(() => {
                    showLoadingSwal('hide');
                    loadData();
                });
            }
        }, error => {
            showLoadingSwal('hide');
        });
    }, 'Tolak Pelanggan', 'Silahkan berikan alasan penolakan');
}

function openModalDetail(obj) {
    const url = $(obj).data('url');
    modalDoc.show(url, {
        title: 'Surat Kuasa',
    })
}
