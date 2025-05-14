let datatable_ = false;
const optionsUploadKTP = {
    allowedFileExtensions: ['jpg', 'jpeg', 'png']
}
$(function () {
    datatable_ = $('#pengguna-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: `${base_url}/management/getDataPengguna`,
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
            { data: 'name', name: 'name' },
            { data: 'radiasi', name: 'radiasi', className: 'text-center' },
            { data: 'divisi', name: 'divisi' },
            { data: 'status', name: 'status', className: 'text-center' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
        ]
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

    // Event 
    $('#btn-tambah-pengguna').on('click', obj => {
        spinner('show', obj.target);
        const namaPengguna = $('#nama_pengguna').val();
        const divisiPengguna = $('#divisi_pengguna').val();
        const jenisRadiasi = $('#jenis_radiasi').val();
        const imageKtp = $('#uploadKtpPengguna')[0].files[0];

        const formData = new FormData();
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
        })
    });

    $('#modal-add-pengguna').on('hidden.bs.modal', event => {
        resetForm();
        setDropify('reset', '#uploadKtpPengguna', optionsUploadKTP);
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
}