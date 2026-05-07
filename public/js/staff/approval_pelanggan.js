let modalDoc = new ModalDocument();
let filterComp = false;

$(function () {
    loadData();

    // filterComp = new FilterComponent('list-filter', {
    //     filter: {
    //         jenis_layanan: true,
    //         status: true,
    //     }
    // })

    $('#kode_perusahaan').on('input', (obj) => {
        const kode = obj.target.value;
        if (kode) {
            // spinner('show', $('#labelKodePerusahaan'), {place: 'after'});
            ajaxGet(`api/v1/profile/getPerusahaan/${kode}`, false, result => {
                if (result.data) {
                    $('#errorKodePerusahaan').html(`<small class="text-danger">Kode sudah di gunakan</small>`);
                    $('#errorKodePerusahaan').show();
                    $('#btnVerifikasi').attr('disabled', true);
                } else {
                    $('#errorKodePerusahaan').hide();
                    $('#errorKodePerusahaan').html('');
                    $('#btnVerifikasi').attr('disabled', false);
                }
                // spinner('hide', $('#labelKodePerusahaan'), {place: 'after'});
            });
        } else {
            $('#errorKodePerusahaan').hide();
            $('#errorKodePerusahaan').html('');
            $('#btnVerifikasi').attr('disabled', false);
        }
    })

    $('#list-pagination').on('click', 'a', function (e) {
        e.preventDefault();
        const page = e.target.dataset.page;
        loadData(page);
    });
});

function loadData(page = 1) {
    let params = {
        limit: 10,
        page: page
    };

    $('#list-container').hide();
    $('#list-placeholder').show();
    // Panggil API untuk meload list approval pelanggan
    ajaxGet(`api/v1/pelanggan/approval/list`, params, result => {
        let html = '';
        for (const [i, req] of result.data.entries()) {
            let statusPelanggan = req.jenis == 'baru' ? '<span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill fw-normal px-3">Pelanggan Baru</span>' : '<span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle rounded-pill fw-normal px-3">Pelanggan Lama</span>';
            const profile = req.user.profile;

            const params = {
                title: `${req.user.name ?? '-'} <br><small class="fw-light text-muted">Email : ${req.user.email ?? '-'}</small>`,
                perusahaan: req.perusahaan.nama_perusahaan || '-',
                htmlLeftTime: statusPelanggan,
                created_at: req.created_at,
                id: req.request_user_hash,
            };

            let btnAction = `
                <div class="d-flex justify-content-end gap-2 flex-column">
                    <button class="btn btn-outline-info btn-sm" onclick="openModalDetail(this)" data-url="storage/${profile.suratkuasa.file_path}/${profile.suratkuasa.file_hash}" title="Surat Kuasa">
                        <i class="bi bi-file-earmark me-2"></i> Surat Kuasa
                    </button>
                    <button class="btn btn-outline-primary btn-sm" onclick="openModalVerifikasi('${req.request_user_hash}', '${req.jenis}')">
                        <i class="bi bi-check-circle"></i> Verifikasi
                    </button>
                </div>
            `;

            html += cardComponent(params, { btnAction: btnAction });
        }

        if (result.data.length == 0) {
            html = htmlNoData();
        }
        $('#list-container').html(html).show();
        $('#list-pagination').html(createPaginationHTML(result.pagination));
        $('#list-placeholder').hide();
    });
}

function openModalVerifikasi(id, type) {
    $('#id_request').val(id);
    $('#jenis_pelanggan').val(type);

    if (type === 'baru') {
        $('#modalVerifikasiTitle').text('Verifikasi Pelanggan Baru');
        $('#modalVerifikasiDesc').text('Pastikan kelengkapan data perusahaan dan surat kuasa pelanggan baru sudah sesuai dan valid.');
        $('#formKodePerusahaan').show();
        $('#kode_perusahaan').val('');
        $('#errorKodePerusahaan').hide();
        $('#btnVerifikasi').attr('disabled', true);
    } else {
        $('#modalVerifikasiTitle').text('Verifikasi Pelanggan Lama');
        $('#modalVerifikasiDesc').text('Apakah surat kuasa yang dilampirkan sudah sesuai dengan perusahaan terkait?');
        $('#formKodePerusahaan').hide();
        $('#btnVerifikasi').attr('disabled', false);
    }
    
    $('#modalVerifikasi').modal('show');
}

function verifikasiPelanggan(obj) {
    const id = $('#id_request').val();
    const type = $('#jenis_pelanggan').val();

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
        if (result.meta.code == 200) {
            Swal.fire({ icon: 'success', text: 'Pelanggan berhasil diverifikasi' });
            $('#modalVerifikasi').modal('hide');
            loadData();
        }
    }, error => { spinner('hide', $(obj)); });
}

function tolakPelanggan(obj) {
    const id = $('#id_request').val();
    $('#modalVerifikasi').modal('hide');

    showNoteAlertSwal((reason) => {
        let params = new FormData();
        params.append('id_request', id);
        params.append('catatan', reason);

        showLoadingSwal('show');
        ajaxPost(`api/v1/pelanggan/approval/tolak`, params, result => {
            if (result.meta.code == 200) {
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

function reload() {
    loadData();
}