class UploadComponent {
    constructor(idElement, options = {}) {
        this.options = {
            modal: options.modal ?? true,
            camera: options.camera ?? true,
            allowedFileExtensions: options.allowedFileExtensions ?? [],
            type: options.type ?? 'image',
            urlUpload: options.urlUpload ?? false,
            multiple: options.multiple ?? true
        }
        
        this.listFile = [];

        // random id number
        // timestamp tanggal sekarang
        this.timestamp = new Date().getTime();
        this.id = Math.random().toString(36).substring(2, 15) + this.timestamp;

        this._initializeProperties();
        this._createCustomEvents();

        if(this.options.modal){
            $(`#${idElement}`).append(this.modalCreate());
            
            if ($('#modal-preview').length === 0) {
                $('body').append(this.modalPreview());
            }
        }
        
        this._bindEventListeners();
        this.loadListFile();
    }

    _initializeProperties() {
        this.dataPengiriman = null;
    }

    _createCustomEvents() {
        this.eventSimpan = new CustomEvent('simpan', {});
        this.eventUpload = new CustomEvent('upload', {});
    }

    _bindEventListeners() {
        // $('#btnSimpanDetail').on('click', this.simpanDetail.bind(this));
    }

    getId(){
        return this.id;
    }

    getData(){
        return this.listFile;
    }

    addData(data) {
        this.listFile = data;
        this.loadListFile();
    }

    show() {
        // $('#offcanvasDelivery').offcanvas('show');
    }

    // Fungsi untuk menentukan allowedFileExtensions
    allowedFileExtensions(){
        let accept = '';
        if(this.options.allowedFileExtensions.length > 0){
            accept = this.options.allowedFileExtensions.map(ext => `.${ext}`).join(',');
        }
        return accept;
    }

    // buatkan preview untuk pdf
    loadListFile(){
        $(`#listPreview_${this.id}`).html('');

        // cek apakah multiple atau tidak, jika multiple = false button tambah disable dan tambah class cursordisable
        if (!this.options.multiple) {
            $(`#btnTambahFile_${this.id}`).attr('disabled', this.listFile.length > 0);
        }
        
        if(this.listFile.length === 0){
            $(`#listPreview_${this.id}`).html(`<div class="text-center text-muted mt-3 w-100">Tidak ada file yang diupload</div>`);
            return;
        }
        
        this.listFile.forEach((file, index) => {
            if(file.file){
                const reader = new FileReader();
                const main = this;
                reader.onload = function (e) {
                    let htmlPreview = '';
                    if (file.file_type == 'image/jpeg' || file.file_type == 'image/png' || file.file_type == 'image/gif') {
                        htmlPreview = main.previewImage(e.target.result, index);
                    }
                    document.getElementById(`listPreview_${main.id}`).appendChild(htmlPreview);
                };
                reader.readAsDataURL(file.file);


            }else{
                let htmlPreview = '';
                if (file.file_type == 'image/jpeg' || file.file_type == 'image/png' || file.file_type == 'image/gif') {
                    htmlPreview = this.previewImage(file, index);
                } else {
                    htmlPreview = this.previewDocument(file, index);
                }

                document.getElementById(`listPreview_${this.id}`).appendChild(htmlPreview);
            }

        });

    }
    

    tambah() {
        // ambil gambar dari inputFile
        const inputFile = $(`#uploadFile_${this.id}`)[0].files[0];
        if(inputFile){
            spinner('show', $(`#btnTambahFile_${this.id}`));
            
            if(this.options.urlUpload){
                const params = new FormData();
                params.append('idHash', this.options.urlUpload.idHash);
                params.append('file', inputFile);
                ajaxPost(this.options.urlUpload.url, params, result => {
                    console.log(result);
                    this.listFile.push(result.data);
                    spinner('hide', $(`#btnTambahFile_${this.id}`));
                    $(`#uploadFile_${this.id}`).val('');
                    this.loadListFile();
                }, error => {
                    spinner('hide', $(`#btnTambahFile_${this.id}`));
                    console.error(error);
                }, () => {
                    var xhr = new window.XMLHttpRequest();
                    $(`#progress_${this.id}`).show();
                    let main = this;
                    xhr.upload.addEventListener("progress", function(evt){
                        if (evt.lengthComputable) {
                            let percentComplete = evt.loaded / evt.total;
                            percentComplete = parseInt(percentComplete * 100);
                            
                            document.getElementById(`progress_${main.id}`).children[0].style.width = percentComplete + "%";
                            document.getElementById(`progress_${main.id}`).children[0].innerHTML = percentComplete + "%";
                            
                            if(percentComplete === 100){
                                setTimeout(()=> {
                                    document.getElementById(`progress_${main.id}`).children[0].style.width = "0%";
                                    document.getElementById(`progress_${main.id}`).children[0].innerHTML = "0%";
                                    $(`#progress_${main.id}`).hide();
                                }, 2000);

                            }
                        }
                    }, false);
                    return xhr;
                })
            }else{
                let media = {
                    file: inputFile,
                    file_type: inputFile.type
                };
                this.listFile.push(media);
                spinner('hide', $(`#btnTambahFile_${this.id}`));
                $(`#uploadFile_${this.id}`).val('');
                this.loadListFile();
            }
        }
    }

    previewImage(file, index){
        let src = false;
        if(file.media_hash){
            src = `${base_url}/storage/${file.file_path}/${file.file_hash}`;
        }else{
            src = file;
        }
        // ambil gambar dari inputFile
        const divMain = document.createElement('div');
        divMain.className = 'position-relative';
        divMain.style.width = '100px';
        divMain.style.height = '100px';

        const preview = document.createElement('img');
        preview.src = src;
        preview.className = 'img-thumbnail';
        preview.style.width = '100px';
        preview.style.height = '100px';
        preview.style.cursor = 'pointer';
        preview.onclick = () => {
            $('#modal-preview-image').attr('src', src);
            $('#modal-preview').modal('show');
        }

        const btnRemove = document.createElement('button');
        btnRemove.className = 'btn btn-danger btn-sm position-absolute mt-2 ms-2';
        btnRemove.innerHTML = '<i class="bi bi-trash"></i>';
        btnRemove.onclick = this.removeFile.bind(this, index);

        divMain.append(btnRemove);
        divMain.append(preview);

        return divMain;
    }

    previewDocument(file, index) {
        const sizeContent = `<small class="text-submain caption" style="margin-top: -3px;">${formatBytes(file.file_size)}</small>`;
        const removeContent = document.createElement('button');
        removeContent.className = 'btn btn-sm btn-outline-danger';
        removeContent.title = 'Remove';
        removeContent.innerHTML = '<i class="bi bi-trash"></i>';
        removeContent.onclick = this.removeFile.bind(this, index);

        const div1 = document.createElement('div');
        div1.className = `d-flex align-items-center justify-content-between px-3 shadow-sm cursoron document border mb-2 w-100`;

        const linkMedia = document.createElement('a');
        linkMedia.className = 'd-flex align-items-center w-100';
        linkMedia.href = `${base_url}/storage/${file.file_path}/${file.file_hash}`;
        linkMedia.target = '_blank';

        const divImg = document.createElement('div');
        const img = document.createElement('img');
        img.className = 'my-3';
        img.src = `${base_url}/icons/${iconDocument(file.file_type)}`;
        img.style = 'width: 24px; height: 24px;';
        divImg.append(img);

        const divDesc = document.createElement('div');
        divDesc.className = 'flex-grow-1 ms-2 d-flex flex-column pe-3';
        divDesc.innerHTML = `
            <span class="caption text-main">${file.file_ori}</span>
        `;

        const divSize = document.createElement('div');
        divSize.className = 'col-md-3';
        divSize.innerHTML = sizeContent;

        const divAction = document.createElement('div');
        divAction.className = 'p-1';

        // Action
        divAction.append(removeContent);

        linkMedia.append(divImg);
        linkMedia.append(divDesc);
        linkMedia.append(divSize);
        
        div1.append(linkMedia);
        div1.append(divAction);

        return div1;
    }

    modalCreate() {
        // Buat elemen div container
        const container = document.createElement('div');
    
        // Elemen input file
        const inputGroup = document.createElement('div');
        inputGroup.classList.add('input-group');
    
        const inputFile = document.createElement('input');
        inputFile.type = 'file';
        inputFile.classList.add('form-control');
        inputFile.id = `uploadFile_${this.id}`;
        inputFile.accept = this.allowedFileExtensions();
        inputFile.setAttribute('aria-label', 'Upload');
        inputGroup.appendChild(inputFile);
    
        // Tombol Tambah
        const btnTambah = document.createElement('button');
        btnTambah.classList.add('btn', 'btn-outline-primary');
        btnTambah.id = `btnTambahFile_${this.id}`;
        btnTambah.textContent = 'Tambah';
        btnTambah.onclick = this.tambah.bind(this);
        inputGroup.appendChild(btnTambah);
    
        // Tombol Kamera
        const btnKamera = document.createElement('button');
        btnKamera.classList.add('btn', 'btn-outline-secondary');
        btnKamera.type = 'button';
        btnKamera.id = `activeFoto_${this.id}`;
    
        const iconKamera = document.createElement('i');
        iconKamera.classList.add('bi', 'bi-camera');
        btnKamera.appendChild(iconKamera);
        this.options.camera && inputGroup.appendChild(btnKamera);

        // Progress bar
        /*
        <div class="progress" role="progressbar" aria-label="Example with label" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
            <div class="progress-bar" style="width: 25%">25%</div>
        </div>
        */
        const progressBar = document.createElement('div');
        progressBar.classList.add('progress', 'w-100', 'mt-2');
        progressBar.id = `progress_${this.id}`;
        progressBar.max = 100;
        progressBar.value = 0;
        progressBar.ariaValueMin = 0;
        progressBar.ariaValueMax = 100;
        progressBar.innerHTML = '<div class="progress-bar" style="width: 0%">0%</div>';
        progressBar.style.display = 'none';
        
        container.appendChild(inputGroup);
        container.appendChild(progressBar);
    
        // Elemen untuk daftar preview
        const listPreview = document.createElement('div');
        listPreview.id = `listPreview_${this.id}`;
        listPreview.classList.add('mt-2', 'd-flex', 'column-gap-2', 'flex-wrap');
        container.appendChild(listPreview);
    
        return container;
    }

    modalPreview() {
        return `
            <div class="modal fade" id="modal-preview" tabindex="-1" aria-labelledby="modal-previewLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <img id="modal-preview-image" src="" alt="" class="img-fluid">
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    on(eventName, callback = () => {}) {
        return document.addEventListener(eventName, callback);
    }

    removeFile(index){
        // ambil file sekarang
        let file = this.listFile[index];
        if(this.options.urlUpload){
            ajaxDelete(this.options.urlUpload.urlDestroy + `/${this.options.urlUpload.idHash}/${file.media_hash}`, result => {
                this.listFile.splice(index, 1);
                this.loadListFile();
            }, error => {
                console.error(error);
            })
        }else{
            this.listFile.splice(index, 1);
            this.loadListFile();
        }
    }

    destroy(){
        if(this.options.modal){
            $('#offcanvasDetail').remove();
        }
    }
}