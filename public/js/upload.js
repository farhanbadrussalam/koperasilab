class UploadComponent {
    /**
     * Constructor untuk membuat komponen upload
     * @param {String} idElement - id element yang akan digunakan sebagai wrapper
     * @param {Object} options - object yang berisi konfigurasi
     * @property {String} mode - mode upload atau preview, default 'upload'
     * @property {Boolean} modal - boolean untuk menentukan apakah akan menampilkan modal atau tidak, default true
     * @property {Boolean} camera - boolean untuk menentukan apakah akan menampilkan tombol camera atau tidak, default true
     * @property {Array} allowedFileExtensions - array yang berisi ekstensi file yang diizinkan, default []
     * @property {String} type - type file yang diizinkan, default 'image'
     * @property {String} urlUpload - url untuk mengupload file, default false
     * @property {Boolean} multiple - boolean untuk menentukan apakah akan mengizinkan upload file multiple atau tidak, default true
     */
    constructor(idElement, options = {}) {
        this.options = {
            mode: options.mode ?? 'upload', // upload atau preview
            modal: options.modal ?? true,
            camera: options.camera ?? true,
            allowedFileExtensions: options.allowedFileExtensions ?? [],
            type: options.type ?? 'image',
            urlUpload: options.urlUpload ?? false,
            multiple: options.multiple ?? true,
            maxSize: options.maxSize ?? (window.appSettings && window.appSettings.max_upload_size ? (parseFloat(window.appSettings.max_upload_size) / 1024) : 1),
            resolution: options.resolution ?? false,
            preview: {
                width: options.preview?.width ?? 100,
                height: options.preview?.height ?? 100,
                fullwidth: options.preview?.fullwidth ?? false
            },
            form: options.form ?? false,
            template: {
                url: options.template?.url ?? null,
                name: options.template?.name ?? null
            }
        }
        this.idElement = idElement;
        this.listFile = options.data ?? [];

        // random id number
        // timestamp tanggal sekarang
        this.timestamp = new Date().getTime();
        this.id = Math.random().toString(36).substring(2, 15) + this.timestamp;

        this._initializeProperties();
        this._createCustomEvents();

        if (this.options.modal) {
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
        $('#uploadFile_' + this.id).on('change', (e) => {
            if (this.options.multiple === false) {
                const file = e.target.files[0];
                if (this.checkMaxSize(file) === false) {
                    $(`#uploadFile_${this.id}`).val('');
                    Swal.fire({
                        icon: 'warning',
                        text: 'Ukuran file tidak boleh melebihi ' + this.options.maxSize + 'MB'
                    })
                    return;
                }
            }
        });
    }

    getId() {
        return this.id;
    }

    getData() {
        if (this.options.form) {
            return $(`#uploadFile_${this.id}`)[0].files[0] ?? null;
        } else {
            return this.listFile;
        }
    }

    addData(data) {
        this.listFile = data;
        this.loadListFile();
    }

    setData(data) {
        if (this.options.multiple) {
            this.listFile = data;
        } else {
            this.listFile.push(data);
        }
        this.loadListFile();
    }

    show() {
        // $('#offcanvasDelivery').offcanvas('show');
    }

    // Fungsi untuk menentukan allowedFileExtensions
    allowedFileExtensions() {
        let accept = '';
        if (this.options.allowedFileExtensions.length > 0) {
            accept = this.options.allowedFileExtensions.map(ext => `.${ext}`).join(',');
        }
        return accept;
    }

    // buatkan preview untuk pdf
    loadListFile() {
        $(`#listPreview_${this.id}`).html('');

        // cek apakah multiple atau tidak, jika multiple = false button tambah disable dan tambah class cursordisable
        if (!this.options.multiple) {
            $(`#btnTambahFile_${this.id}`).attr('disabled', this.listFile.length > 0);
        }

        if (this.listFile.length === 0) {
            $(`#listPreview_${this.id}`).html(`<div class="text-center text-muted mt-3 w-100">Tidak ada file yang diupload</div>`);
            return;
        }

        // sort agar file gambar tampil di paling terakhir
        this.listFile.sort((a, b) => {
            if (a.file_type == 'image/jpeg' || a.file_type == 'image/png' || a.file_type == 'image/gif') {
                return 1;
            } else {
                return -1;
            }
        });

        this.listFile.forEach((file, index) => {
            if (file.file) {
                const reader = new FileReader();
                const main = this;
                reader.onload = function (e) {
                    let htmlPreview = '';
                    if (file.file_type == 'image/jpeg' || file.file_type == 'image/png' || file.file_type == 'image/gif') {
                        htmlPreview = main.previewImage(e.target.result, index);
                    } else {
                        let objFile = {
                            file_size: file.file.size,
                            file_type: file.file.type,
                            file_ori: file.file.name,
                            file_result: e.target.result
                        }
                        htmlPreview = main.previewDocument(objFile, index);
                    }
                    document.getElementById(`listPreview_${main.id}`).appendChild(htmlPreview);
                };
                reader.readAsDataURL(file.file);
            } else {
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
        if (inputFile) {
            spinner('show', $(`#btnTambahFile_${this.id}`));

            // cek size file
            if (this.checkMaxSize(inputFile) === false) {
                spinner('hide', $(`#btnTambahFile_${this.id}`));
                let displaySize = this.options.maxSize ?? (window.appSettingsCache['max_upload_size'] ? (parseFloat(window.appSettingsCache['max_upload_size']) / 1024) : 1);
                Swal.fire({
                    icon: 'warning',
                    text: 'Ukuran file tidak boleh melebihi ' + displaySize + 'MB'
                })
                return;
            }

            if (this.options.urlUpload) {
                const params = new FormData();
                params.append('idHash', this.options.urlUpload.idHash);
                params.append('file', inputFile);
                ajaxPost(this.options.urlUpload.url, params, result => {
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
                    xhr.upload.addEventListener("progress", function (evt) {
                        if (evt.lengthComputable) {
                            let percentComplete = evt.loaded / evt.total;
                            percentComplete = parseInt(percentComplete * 100);

                            document.getElementById(`progress_${main.id}`).children[0].style.width = percentComplete + "%";
                            document.getElementById(`progress_${main.id}`).children[0].innerHTML = percentComplete + "%";

                            if (percentComplete === 100) {
                                setTimeout(() => {
                                    document.getElementById(`progress_${main.id}`).children[0].style.width = "0%";
                                    document.getElementById(`progress_${main.id}`).children[0].innerHTML = "0%";
                                    $(`#progress_${main.id}`).hide();
                                }, 2000);

                            }
                        }
                    }, false);
                    return xhr;
                })
            } else {
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


    checkMaxSize(file) {
        let maxSize = this.options.maxSize;
        let fileSize = file.size / (1024 * 1024);
        if (fileSize > maxSize) {
            return false;
        }
        return true;
    }

    previewImage(file, index) {
        let src = false;
        if (file.media_hash) {
            src = `${base_url}/storage/${file.file_path}/${file.file_hash}`;
        } else {
            src = file;
        }
        // ambil gambar dari inputFile
        const divMain = document.createElement('div');
        divMain.className = 'position-relative';
        if (this.options.preview.fullwidth) {
            divMain.style.width = '100%';
            divMain.style.height = `${this.options.preview.height}px`;
        } else {
            divMain.style.width = `${this.options.preview.width}px`;
            divMain.style.height = `${this.options.preview.height}px`;
        }

        const preview = document.createElement('img');
        preview.src = src;
        preview.className = 'img-thumbnail';
        if (this.options.preview.fullwidth) {
            preview.style.height = '100%';
        } else {
            preview.style.width = `${this.options.preview.width}px`;
            preview.style.height = `${this.options.preview.height}px`;
        }
        preview.style.cursor = 'pointer';
        preview.onclick = () => {
            const showImage = document.createElement('a');
            showImage.href = src;
            showImage.className = 'show-popup-image d-none';
            document.body.appendChild(showImage);
            showPopupReload();
            $(showImage).trigger('click');
            setTimeout(() => showImage.remove(), 500);
        }

        const btnRemove = document.createElement('button');
        btnRemove.className = 'btn btn-danger btn-sm position-absolute mt-2 ms-2';
        btnRemove.innerHTML = '<i class="bi bi-trash"></i>';
        btnRemove.onclick = this.removeFile.bind(this, index);

        this.options.mode == 'upload' && divMain.append(btnRemove);
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
        linkMedia.className = 'd-flex align-items-center w-100 text-decoration-none';
        if (file.file_result) {
            const url = window.URL.createObjectURL(new Blob([file.file_result], { type: 'application/pdf' }));
            linkMedia.href = url;
            linkMedia.setAttribute('download', file.file_ori);
        } else {
            linkMedia.href = `${base_url}/storage/${file.file_path}/${file.file_hash}`;
        }
        linkMedia.target = '_blank';

        const divImg = document.createElement('div');
        const img = document.createElement('img');
        img.className = 'my-3';
        img.src = `${base_url}/icons/${iconDocument(file.file_type)}`;
        img.style = 'width: 24px; height: 24px;';
        divImg.append(img);

        const divDesc = document.createElement('div');
        divDesc.className = 'flex-grow-1 ms-2 d-flex flex-column pe-3 text-break';
        divDesc.innerHTML = `
            <span class="caption text-main">${file.file_ori}</span>
        `;

        const divSize = document.createElement('div');
        divSize.className = 'col-md-3';
        divSize.innerHTML = sizeContent;

        const divAction = document.createElement('div');
        divAction.className = 'p-1';

        // Action
        this.options.mode == 'upload' && divAction.append(removeContent);

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

        if (this.options.mode === 'upload') {
            // Elemen input file
            const inputGroup = document.createElement('div');
            inputGroup.classList.add('input-group');

            const inputFile = document.createElement('input');
            inputFile.type = 'file';
            inputFile.name = 'uploadFile';
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
            btnTambah.type = 'button';
            btnTambah.onclick = this.tambah.bind(this);
            if (this.options.form == false) {
                inputGroup.appendChild(btnTambah);
            }

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

            // menampilkan extension file yang diizinkan
            const allowedExtensions = this.options.allowedFileExtensions;
            const textAllowedExtensions = document.createElement('div');
            if (allowedExtensions.length > 0) {
                const allowedExtension = document.createElement('span');
                allowedExtension.classList.add('text-submain', 'caption');
                allowedExtension.innerHTML = `Extension file yang diizinkan: ${allowedExtensions.join(', ')}`;
                textAllowedExtensions.appendChild(allowedExtension);
            }

            // menampilkan max size file
            const maxSize = this.options.maxSize;
            const resolution = this.options.resolution;
            const textMaxSize = document.createElement('span');
            textMaxSize.classList.add('text-submain', 'caption');
            textMaxSize.innerHTML = `<div>Max size file: ${maxSize} MB ${resolution ? `(Resolution: ${resolution})` : ''}</div>`;
            textAllowedExtensions.appendChild(textMaxSize);

            // menampilkan template jika ada
            const template = document.createElement('div');
            if (this.options.template.url) {
                template.classList.add('mt-2', 'text-end');
                const linkTemplate = document.createElement('a');
                linkTemplate.href = this.options.template.url;
                linkTemplate.classList.add('btn', 'btn-outline-light', 'btn-sm', 'border', 'text-primary');
                linkTemplate.innerHTML = `<i class="bi bi-download me-2"></i>${this.options.template.name}`;

                // Menangani klik untuk memaksa download via Fetch/Blob
                linkTemplate.onclick = (e) => {
                    e.preventDefault();
                    fetch(this.options.template.url)
                        .then(response => response.blob())
                        .then(blob => {
                            const url = window.URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = url;
                            a.download = this.options.template.name || 'template';
                            document.body.appendChild(a);
                            a.click();
                            a.remove();
                            window.URL.revokeObjectURL(url);
                        })
                        .catch(() => window.location.href = this.options.template.url);
                };

                template.appendChild(linkTemplate);
            }

            container.appendChild(textAllowedExtensions);
            container.appendChild(inputGroup);
            container.appendChild(progressBar);
            container.appendChild(template);
        }

        // Elemen untuk daftar preview
        const listPreview = document.createElement('div');
        listPreview.id = `listPreview_${this.id}`;
        listPreview.classList.add('mt-2', 'd-flex', 'column-gap-2', 'flex-wrap');
        if (this.options.form == false) {
            container.appendChild(listPreview);
        }

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

    on(eventName, callback = () => { }) {
        return document.addEventListener(eventName, callback);
    }

    removeFile(index) {
        // ambil file sekarang
        let file = this.listFile[index];
        if (this.options.urlUpload) {
            ajaxDelete(this.options.urlUpload.urlDestroy + `/${this.options.urlUpload.idHash}/${file.media_hash}`, result => {
                this.listFile.splice(index, 1);
                this.loadListFile();
            }, error => {
                console.error(error);
            })
        } else {
            this.listFile.splice(index, 1);
            this.loadListFile();
        }
    }

    clearFile() {
        this.listFile = [];
        this.loadListFile();
    }

    destroy() {
        if (this.options.modal) {
            $(`#${this.idElement}`).children().remove();
        }
    }
}
