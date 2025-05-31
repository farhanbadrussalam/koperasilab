let datatable_ = false;
let filterComp = false;
const optionsUploadKTP = {
    allowedFileExtensions: ['jpg', 'jpeg', 'png']
}
$(function () {
    filterComp = new FilterComponent('list-filter', {
        jenis: 'pengguna',
        filter: {
            status: true
        }
    })

    datatable_ = $('#pengguna-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: `${base_url}/management/getDataPengguna`,
            data: function(d) {
                let filterValue = filterComp && filterComp.getAllValue();
                d.filter = {};

                filterValue.status && (d.filter.status = filterValue.status);
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
            { data: 'name', name: 'name' },
            { data: 'radiasi', name: 'radiasi', className: 'text-center' },
            { data: 'divisi', name: 'divisi' },
            { data: 'status', name: 'status', className: 'text-center' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
        ]
    });

    // Setup Filter
    filterComp.on('filter.change', () => {
        datatable_.ajax.reload();
    });

    datatable_.on('draw.dt', function () {
        showPopupReload();
    });

    // set dropify
    setDropify('init', '#uploadKtpPengguna', optionsUploadKTP);

    // set Select2
    $('#jenis_radiasi').select2({
        theme: "bootstrap-5",
        tags: true,
        placeholder: "Pilih Jenis Radiasi",
        dropdownParent: $('#modal-add-pengguna'),
        createTag: (params) => {
            return {
                id: params.term,
                text: params.term,
                newTag: true
            };
        }
    });

    $('#divisi_pengguna').select2({
        theme: "bootstrap-5",
        tags: true,
        placeholder: "Pilih Divisi",
        dropdownParent: $('#modal-add-pengguna'),
        createTag: (params) => {
            return {
                id: params.term,
                text: params.term,
                newTag: true
            };
        }
    });

    $('#tanggal_lahir').flatpickr({
        enableTime: false,
        dateFormat: "Y-m-d"
    });

    // Event
    $('#btn-tambah-pengguna').on('click', obj => {
        spinner('show', obj.target);
        const namaPengguna = $('#nama_pengguna').val();
        const divisiPengguna = $('#divisi_pengguna').val();
        const jenisRadiasi = $('#jenis_radiasi').val();
        const imageKtp = $('#uploadKtpPengguna')[0].files[0];
        const nikPengguna = $('#nik_pengguna').val();
        const jenisKelamin = $('#jenis_kelamin').val();
        const tanggalLahir = $('#tanggal_lahir').val();
        const tempatLahir = $('#tempat_lahir').val();
        const kodeLencana = $('#kode_lencana').val();
        const isAktif = $('#is_aktif').is(':checked') ? 1 : 0;

        const formData = new FormData();
        formData.append('nik', nikPengguna);
        formData.append('kode_lencana', kodeLencana);
        formData.append('is_aktif', isAktif);
        formData.append('jenis_kelamin', jenisKelamin);
        formData.append('tanggal_lahir', tanggalLahir);
        formData.append('tempat_lahir', tempatLahir);
        formData.append('ktp', imageKtp);
        formData.append('name', namaPengguna);
        formData.append('divisi', divisiPengguna);
        formData.append('radiasi', JSON.stringify(jenisRadiasi));

        ajaxPost(`api/v1/pengguna/action`, formData, result => {
            if (result.meta.code == 200) {
                Swal.fire({
                    icon: "success",
                    text: result.data.msg,
                });
                reload();
                spinner('hide', obj.target);
                $('#modal-add-pengguna').modal('hide');
            } else {
                Swal.fire({
                    icon: "error",
                    text: result.data.msg,
                });
            }
        }, error => {
            spinner('hide', obj.target);
        })
    });

    $('#modal-add-pengguna').on('hidden.bs.modal', event => {
        resetForm();
        setDropify('reset', '#uploadKtpPengguna', optionsUploadKTP);
    });

    $('#is_aktif').on('change', obj => {
        if ($(obj.target).is(':checked')) {
            $('#kode_lencana').val('');
            $('#kode_lencana').attr('readonly', true);
            $('#kode_lencana').attr('placeholder', 'Auto Generate');
            $('#kode_lencana').addClass('bg-secondary-subtle');
        } else {
            $('#kode_lencana').attr('readonly', false);
            $('#kode_lencana').attr('placeholder', '');
            $('#kode_lencana').removeClass('bg-secondary-subtle');
        }
    });
})

function btnDelete(obj) {
    const id = $(obj).data('id');
    ajaxDelete(`api/v1/pengguna/destroy/${id}`, result => {
        if (result.meta.code == 200) {
            Swal.fire({
                icon: 'success',
                text: result.data.msg,
                timer: 1200,
                timerProgressBar: true,
                showConfirmButton: false
            }).then(() => {
                datatable_.ajax.reload();
            })
        }
    })
}

function reload() {
    datatable_.ajax.reload();
}

function resetForm() {
    $('#nama_pengguna').val('');
    $('#divisi_pengguna').val('').trigger('change');
    $('#jenis_radiasi').val('').trigger('change');
    $('#uploadKtpPengguna').val('');
    $('#nik_pengguna').val('');
    $('#jenis_kelamin').val('');
    $('#tanggal_lahir').val('');
    $('#tempat_lahir').val('');
    $('#is_aktif').prop('checked', false);
    $('#kode_lencana').val('');
}
