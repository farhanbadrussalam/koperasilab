let editorInstance;
let dataList = [];
let selectedId = false;
let vars = [];
$(function() {
    CKEditorBuild
        .create( document.querySelector( '#txt_content' ), {
            toolbar: {
                items: [
                    'fullscreen','heading', 'style',
                    '|', 'bold', 'italic', 'link', 'alignment',
                    '|', 'horizontalLine', 'bulletedList', 'numberedList',
                    '|', 'fontFamily', 'fontSize', 'fontColor', 'fontBackgroundColor', 'Underline',
                    '|', 'undo', 'redo'
                ]
            },
            heading: {
                options: [
                    { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                    { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                    { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                    { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' },
                    { model: 'heading4', view: 'h4', title: 'Heading 4', class: 'ck-heading_heading4' }
                ]
            },
            mention: {
                // (CKEditor 5 mendukung custom marker & limit dropdown)
                feeds: [
                    {
                        marker: "@",
                        feed: (queryText) => {
                            const q = (queryText || "").toLowerCase();
                            return vars
                                .filter((v) => v.toLowerCase().startsWith(q))
                                .map((v) => `@${v}`);
                        },
                        minimumCharacters: 0,
                        dropdownLimit: 50,
                    },
                ],
            },
        })
        .then( editor => {
            editorInstance = editor;
        } )
        .catch( error => {
            console.error( error );
        } );

    load();

    $('#modal-tambah-pembayaran').on('hidden.bs.modal', reset);
})
const varsInput = document.getElementById('varsInput');
const varsHidden = document.getElementById('varsHidden');
varsInput.addEventListener('input', syncVars);

function syncVars() {
    const items = (varsInput.value || "")
        .split(",")
        .map((s) => s.trim())
        .filter(Boolean);
    varsHidden.setAttribute("name", "variables"); // kirim sebagai array di Laravel
    varsHidden.value = JSON.stringify(items);
    vars = items;
}

function created(){
    $('#modalLabel').html('Tambah Metode Pembayaran');
    $('#modal-tambah-pembayaran').modal('show');
}


function simpan(obj) {
    const form = new FormData();
    form.append('name', $('#txt_nama').val());
    form.append('content', editorInstance.getData());
    form.append('variables', JSON.stringify(vars));
    form.append('status', 1);

    if(selectedId){
        form.append('id_jenis_pembayaran', selectedId);
    }

    spinner('show', $(obj));
    ajaxPost(`api/v1/keuangan/actionJenisPembayaran`, form, result => {
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Pembayaran berhasil disimpan',
        }).then(() => {
            $('#modal-tambah-pembayaran').modal('hide');
            load();
            spinner('hide', $(obj));
        })
    }, err => {
        spinner('hide', $(obj));
        console.error(err);
    })
}

function hapus(obj) {
    const id = $(obj).parent().data('id');
    ajaxDelete(`api/v1/keuangan/destroyJenisPembayaran/${id}`, result => {
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Pembayaran berhasil dihapus',
        }).then(() => {
            load();
        })
    }, err => {
        console.error(err);
    })
}

function edit(obj) {
    const id = $(obj).parent().data('id');

    selectedId = id;

    let find = dataList.find(d => d.jenis_pembayaran_hash == id);
    $('#txt_nama').val(find.name);
    editorInstance.setData(find.content);
    varsInput.value = find.variables?.join(',') ?? '';

    syncVars();
    $('#modalLabel').html('Edit Metode Pembayaran');
    $('#modal-tambah-pembayaran').modal('show');
}

function reset(){
    $('#txt_nama').val('');
    editorInstance.setData('');
    selectedId = false;
}

function load() {
    ajaxGet(`api/v1/keuangan/listJenisPembayaran`, false, result => {
        let html = '';
        dataList = result.data;
        for (const value of result.data) {
            html += `
                <div class="col-md-6 mb-3">
                    <div class="card h-100 shadow">
                        <div class="card-body position-relative">
                            <div class="position-absolute top-0 end-0 mt-2 me-2" data-id="${value.jenis_pembayaran_hash}">
                                <button class="btn btn-sm btn-primary" onclick="edit(this)">Edit</button>
                                <button class="btn btn-sm btn-danger" onclick="hapus(this)">Hapus</button>
                            </div>
                            <h4 class="card-title">${value.name}</h4>
                            <div>
                                ${value.content}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        html += `
            <div class="col-md-6 mb-3">
                <div class="card border-1 bg-transparent jenis-pembayaran-created h-100" onclick="created(this)">
                    <div class="card-body d-flex flex-column">
                        <div class="text-center m-auto">
                            <h4 class="card-title">Tambah Pembayaran</h4>
                            <div class="fs-1"><i class="bi bi-plus-circle"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('#pembayaran-content').html(html);
    }, err => {
        console.error(err);
    })
}
