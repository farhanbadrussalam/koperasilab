let editorHeader = false;

/**
 * Membuka modal untuk membuat, mengedit, atau melihat kop surat.
 * @param {('create'|'edit'|'show')} mode - Mode modal.
 * @param {string|null} id - ID dokumen (doc_hash) untuk mode 'edit' atau 'show'.
 */
function openModalKopSurat(mode, id = null) {
    const modal = $('#modal-kop-surat');
    const modalTitle = $('#kopModalLabel');

    // Reset state dengan mentrigger event custom
    // modal.trigger('reset');

    if (mode === 'create') {
        modalTitle.text('Tambah Kop Surat');
        modal.modal('show');
    } else if (mode === 'edit' || mode === 'show') {
        spinner('show', modal.find('.modal-content'));
        ajaxGet(`management/document/${id}`, null, (result) => {
            spinner('hide', modal.find('.modal-content'));
            if (result.data) {
                const doc = result.data;
                modalTitle.text(mode === 'edit' ? 'Edit Kop Surat' : 'Detail Kop Surat');
                $('#kop_surat_id').val(doc.doc_hash);
                $('#nama_kop_surat').val(doc.name);
                if (doc.view == 1) {
                    $('#active_kop_surat_1').prop('checked', true);
                } else {
                    $('#active_kop_surat_0').prop('checked', true);
                }
                if (editorHeader) editorHeader.setData(doc.content);

                if (mode === 'show') {
                    $('#nama_kop_surat').prop('disabled', true);
                    if (editorHeader) editorHeader.enableReadOnlyMode('kopSuratShowMode');
                    $('#btnSimpanKopSurat').hide();
                }
                modal.modal('show');
            } else {
                Swal.fire({ icon: 'error', text: 'Data kop surat tidak ditemukan.' });
            }
        }, (error) => {
            spinner('hide', modal.find('.modal-content'));
            console.error("Gagal memuat data kop surat:", error);
            Swal.fire({ icon: 'error', text: 'Gagal memuat data kop surat.' });
        });
    }
}

function mkEditor(selector) {
    return CKEditorBuild.create(document.querySelector(selector), {
        toolbar: {
            items: [
                'fullscreen', 'heading', 'style',
                '|', 'bold', 'italic', 'link', 'alignment',
                '|', 'horizontalLine', 'bulletedList', 'numberedList', 'insertTable', 'insertTableLayout',
                '|', 'fontFamily', 'fontSize', 'fontColor', 'fontBackgroundColor', 'Underline',
                '|', 'undo', 'redo', 'pageBreak', 'ImageUpload'
            ]
        },
        image: {
            upload: { types: ['jpeg', 'jpg', 'png', 'gif', 'webp'] },
            toolbar: [
                'imageStyle:inline', 'imageStyle:block', 'imageStyle:side',
                '|', 'toggleImageCaption', 'imageTextAlternative',
                '|', 'resizeImage'
            ],
            resizeUnit: 'px',
            // (Opsional) tombol preset di toolbar
            resizeOptions: [
                { name: 'resizeImage:original', label: 'Original', value: null },
                { name: 'resizeImage:custom', label: 'Custom', value: 'custom' },
                { name: 'resizeImage:100', label: '100 px', value: 100 },
                { name: 'resizeImage:300', label: '300 px', value: 300 },
                { name: 'resizeImage:600', label: '600 px', value: 600 }
            ],
        },
        // Opsi font
        fontFamily: {
            options: [
                'default',
                'Arial, Helvetica, sans-serif',
                'Times New Roman, Times, serif',
                'Courier New, Courier, monospace',
                'Tahoma, Geneva, sans-serif',
                'Georgia, serif'
            ],
            supportAllValues: true
        },
        fontSize: {
            options: ['8', '10', '12', '14', '16', '18', '24', '32', '48'],
            supportAllValues: true
        },
        fontColor: { colorPicker: { format: 'hex' } },
        fontBackgroundColor: { colorPicker: { format: 'hex' } }
    }).then((editor) => {
        editorHeader = editor;
    }).catch((error) => {
        console.error(error);
    });
}

function simpanKopSurat(obj) {
    const nama = $('#nama_kop_surat').val();
    if (!nama) {
        return Swal.fire({ icon: 'warning', text: 'Nama kop surat tidak boleh kosong.' });
    }
    const content = editorHeader.getData();
    const id = $('#kop_surat_id').val();
    const isActive = $('#active_kop_surat_1').is(':checked') ? 1 : 0;
    const formParams = new FormData();

    formParams.append("title", nama);
    formParams.append("content", content);
    formParams.append("jenis", "header");
    formParams.append("isActive", isActive);

    const url = id ? `management/document/${id}` : `management/document`;

    spinner('show', $(obj));
    ajaxPost(url, formParams, result => {
        spinner('hide', $(obj));
        if (result.meta.code == 200) {
            Swal.fire({ icon: "success", text: result.data.msg });
            $('#modal-kop-surat').modal('hide');
            if (typeof loadDocumentKop === 'function') {
                loadDocumentKop();
            }
        } else {
            Swal.fire({ icon: "error", text: result.data.msg || 'Gagal menyimpan data.' });
        }
    }, err => {
        spinner('hide', $(obj));
        console.error(err);
        Swal.fire({ icon: 'error', text: 'Terjadi kesalahan saat menyimpan.' });
    })
}

Promise.all([
    mkEditor('#content_kop_surat')
])

// Event listener untuk mereset modal saat ditutup atau saat event 'reset' di-trigger
$('#modal-kop-surat').on('hidden.bs.modal reset', function () {
    $('#kopModalLabel').text('Kop Surat');
    $('#kop_surat_id').val('');
    $('#nama_kop_surat').val('').prop('disabled', false);
    $('#btnSimpanKopSurat').show();
    if (editorHeader) {
        editorHeader.setData('');
        editorHeader.disableReadOnlyMode('kopSuratShowMode');
    }
});
