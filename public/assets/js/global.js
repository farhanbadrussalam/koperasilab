const bearer = $('#bearer-token')?.val();
const csrf = $('#csrf-token')?.val();
const userActive = $('#userActive').val() ? JSON.parse($('#userActive').val()) : false;
const base_url = $('#base_url')?.val();
const role = $('#role')?.val();
const permission = $('#permission')?.val() ? JSON.parse($('#permission').val()) : false;
const permissionInRole = $('#permissionInRole').val() ? JSON.parse($('#permissionInRole').val()) : false;

/**
 * Formats a number into Indonesian Rupiah currency format.
 *
 * @param {number} angka - The number to be formatted.
 * @returns {string} The formatted currency string in Rupiah.
 */
function formatRupiah(angka) {
    // Mengubah angka menjadi format mata uang Rupiah
    var format = new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(angka);

    // Mengganti nilai input dengan format Rupiah
    return format;
}

/**
 * Initializes input masks for various input fields.
 * 
 * This function applies different input masks to elements with specific classes:
 * - `.rupiah`: Applies a numeric input mask formatted as currency with no prefix, 
 *   comma as the radix point, dot as the group separator, no digits after the decimal, 
 *   auto grouping enabled, right alignment disabled, and mask removed on form submit.
 * - `.maskNumber`: Applies a numeric input mask with a minimum value of 0, maximum value of 100, 
 *   no minus or plus signs allowed, integer values only, and right alignment disabled.
 * - `.maskNPWP`: Applies an input mask for NPWP (Indonesian Tax Identification Number) 
 *   with a specific pattern and placeholder.
 * - `.maskNIK`: Applies an input mask for NIK (Indonesian National Identification Number) 
 *   with a specific pattern and placeholder.
 * - `.maskTelepon`: Applies an input mask for telephone numbers with a specific pattern and placeholder.
 */
function maskReload() {
    $('.rupiah').inputmask('numeric', {
        alias: 'currency',
        prefix: '',
        radixPoint: ',',
        groupSeparator: '.',
        digits: 0,
        autoGroup: true,
        rightAlign: false,
        removeMaskOnSubmit: true
    });
    
    $('.maskNumber').inputmask('numeric', {
        min: 0,
        max: 100,
        allowMinus: false,
        allowPlus: false,
        integer: true,
        rightAlign: false
    });

    $('.maskNPWP').inputmask('99.999.999.9-999.999', { "placeholder": "_", "removeMaskOnSubmit": true });
    $('.maskNIK').inputmask('9999999999999999', { "placeholder": "_", "removeMaskOnSubmit": true });
    $('.maskTelepon').inputmask('9999-9999-9999', { "placeholder": " ", "removeMaskOnSubmit": true });
}
maskReload();

function showPopupReload(){
    $('.show-popup-image').magnificPopup({
        type: 'image',
        closeBtnInside: false,
        callbacks: {
            open: function() {
                $('body').addClass('mfp-open');
            },
            close: function() {
                $('body').removeClass('mfp-open');
            }
        },
        modal: false
    });
}
showPopupReload();

/**
 * Displays a confirmation dialog using SweetAlert2 and executes a callback function if confirmed.
 *
 * @param {Function} [callback=() => {}] - The callback function to execute if the user confirms the action.
 */
function deleteGlobal(callback = () => { }) {
    Swal.fire({
        title: 'Are you sure?',
        icon: false,
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'No, cancel!',
        customClass: {
            confirmButton: 'btn btn-outline-success mx-1',
            cancelButton: 'btn btn-outline-danger mx-1'
        },
        buttonsStyling: false,
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            callback();
        }
    })
}

/**
 *
 * @param {date} tanggal
 * @param {integer} type
 * * 1 = 14 Okt 2023, 18:40
 * * 2 = 14 Okt 2023
 * * 3 = 2023-10-14 18:40
 * * 4 = 14 August 2024
 * * default = sabtu, 14 Okt 2023, 18:40
 * @returns
 */
function dateFormat(tanggal, type = false) {
    let d = new Date(tanggal);

    let options = {};

    let year = d.getFullYear();
    let month = String(d.getMonth() + 1).padStart(2, '0'); // Menambahkan leading zero jika perlu
    let day = String(d.getDate()).padStart(2, '0'); // Menambahkan leading zero jika perlu
    let hour = String(d.getHours()).padStart(2, '0'); // Menambahkan leading zero jika perlu
    let minute = String(d.getMinutes()).padStart(2, '0'); // Menambahkan leading zero jika perlu

    switch (type) {
        case 1:
            // 14 Okt 2023, 18:40
            options = {
                day: 'numeric',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            break;
        case 2:
            // 14 Okt 2023
            options = {
                day: 'numeric',
                month: 'short',
                year: 'numeric'
            };
            break;
        case 3:
            // 2023-10-14 18:40
            return `${year}-${month}-${day} ${hour}:${minute}`;
            break;
        case 4:
            // 14 August 2024
            options = {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            };
            break;
        case 5:
            // Okt 2023
            options = {
            month: 'long',
            year: 'numeric'
            };
            break;
        default:
            // sabtu, 14 Okt 2023, 18:40
            options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            break;
    }

    return `${d.toLocaleString('id-ID', options)}`;
}

/**
 * Converts a date string into a formatted date string.
 *
 * @param {string} tanggal - The date string to be converted.
 * @returns {string} The formatted date string in 'en-US' locale.
 */
function convertDate(tanggal) {
    const options = {
        weekday: 'long',
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: 'numeric',
        minute: 'numeric',
    };

    const date = new Date(tanggal);
    return date.toLocaleString('en-US', options);
}

function statusFormat(feature, status) {
    let htmlStatus = '';
    status = Number(status);
    if (feature == 'jadwal') {
        switch (status) {
            case 0:
                htmlStatus = `
                    <div class="d-flex align-items-center">
                        <div><div class="me-1 dot bg-secondary"></div></div>
                        <span class="subbody-medium text-submain text-truncate">Belum ditugaskan</span>
                    </div>
                    `;
                break;
            case 1:
                htmlStatus = `
                    <div class="d-flex align-items-center">
                        <div><div class="me-1 dot bg-info"></div></div>
                        <span class="subbody-medium text-submain text-truncate">Diajukan</span>
                    </div>
                    `;
                break;
            case 2:
                htmlStatus = `
                    <div class="d-flex align-items-center">
                        <div><div class="me-1 dot bg-success"></div></div>
                        <span class="subbody-medium text-submain text-truncate">Bersedia</span>
                    </div>
                    `;
                break;
            case 9:
                htmlStatus = `
                    <div class="d-flex align-items-center">
                        <div><div class="me-1 dot bg-danger"></div></div>
                        <span class="subbody-medium text-submain text-truncate">Menolak</span>
                    </div>
                    `;
                break;
            default:
                htmlStatus = `
                    <div class="d-flex align-items-center">
                        <div><div class="me-1 dot bg-danger"></div></div>
                        <span class="subbody-medium text-submain text-truncate">Dibatalkan</span>
                    </div>
                    `;
                break;
        }
    } else if (feature == 'permohonan') {
        switch (status) {
            case 1:
                htmlStatus = `
                    <span class="text-secondary ms-2"><i class="bi bi-file-earmark-plus"></i> Pengajuan</span>
                    `;
                break;
            case 2:
                htmlStatus = `
                    <span class="text-primary ms-2"><i class="bi bi-shield-check"></i> Terverifikasi</span>
                    `;
                break;
            case 3:
                htmlStatus = `
                    <span class="text-info ms-2"><i class="bi bi-hourglass-split"></i> Proses pelaksana LAB</span>
                `;
                break;
            case 4:
                htmlStatus = `
                    <span class="text-info ms-2"><i class="bi bi-hourglass-split"></i> Proses Pengiriman</span>
                `;
            case 5:
                htmlStatus = `
                    <span class="text-success ms-2"><i class="bi bi-check-circle-fill"></i> Selesai</span>
                `;
                break;
            case 80:
                htmlStatus = `
                    <div class="d-flex align-items-center">
                        <div><div class="me-1 dot bg-secondary"></div></div>
                        <span class="subbody-medium text-submain text-truncate">Draft</span>
                    </div>
                    `;
                break;
            case 90:
                htmlStatus = `
                    <div class="d-flex align-items-center">
                        <div><div class="me-1 dot bg-danger"></div></div>
                        <span class="subbody-medium text-submain text-truncate">Ditolak</span>
                    </div>
                    `;
                break;
        }
    } else if (feature == 'keuangan') {
        switch (status) {
            case 1:
                htmlStatus = `
                    <span class="badge bg-secondary-subtle text-dark">Pengajuan</span>
                    `;
                break;
            case 2:
                htmlStatus = `
                    <span class="badge bg-secondary-subtle text-dark">TTD manager</span>
                    `;
                break;
            case 3:
                htmlStatus = `
                    <span class="badge bg-warning-subtle text-dark">Perlu dibayar</span>
                    `;
                break;
            case 4:
                htmlStatus = `
                    <span class="badge bg-primary-subtle text-dark">Menunggu konfirmasi</span>
                    `;
                break;
            case 5:
                htmlStatus = `
                    <span class="badge bg-success-subtle text-dark">Pembayaran diterima</span>
                    `;
                break;
                
            default:
                htmlStatus = `
                    <span class="badge bg-danger-subtle text-dark">Pembayaran ditolak</span>
                    `;
                break;
        }
    } else if (feature == 'pengiriman') {
        switch (status) {
            case 1:
                htmlStatus = `
                    <span class="text-info ms-2"><i class="bi bi-truck"></i> Sedang dikirim</span>`;
                break;

            case 2:
                htmlStatus = `
                    <span class="text-success ms-2"><i class="bi bi-check-circle-fill"></i> Sudah diterima</span>`;
                break;
                
            case 3:
                htmlStatus = `
                    <span class="text-primary ms-2"><i class="bi bi-arrow-repeat"></i> Proses Pengiriman</span>`;
                break;

            default:
                htmlStatus = `
                    <span class="text-secondary ms-2"><i class="bi bi-dash-circle"></i> Belum dikirim</span>`;
                break;
        }
    } else if (feature == 'penyelia') {
        switch (status){
            case 1:
                htmlStatus = `
                    <span class="badge bg-secondary-subtle text-dark border">Pengajuan</span>
                `;
                break;
            case 2:
                htmlStatus = `
                    <span class="badge bg-primary-subtle text-dark border">TTD manager</span>
                `;
                break;
            case 3:
                htmlStatus = `
                    <span class="badge bg-success-subtle text-dark border">LHU Selesai</span>
                `;
                break;
            case 11:
                htmlStatus = `
                    <span class="badge bg-primary-subtle text-dark border">Proses Pendataan TLD</span>
                `;
                break;
            case 12:
                htmlStatus = `
                    <span class="badge bg-primary-subtle text-dark border">Proses Pembacaan TLD</span>
                `;
                break;
            case 13:
                htmlStatus = `
                    <span class="badge bg-primary-subtle text-dark border">Proses Penerbitan LHU</span>
                `;
                break;
            case 14:
                htmlStatus = `
                    <span class="badge bg-primary-subtle text-dark border">Proses Penyeliaan LHU</span>
                `;
                break;
            case 15:
                htmlStatus = `
                    <span class="badge bg-primary-subtle text-dark border">Proses Pendatanganan LHU</span>
                `;
                break;
            case 16:
                htmlStatus = `
                    <span class="badge bg-primary-subtle text-dark border">Proses Anealing</span>
                `;
                break;
            case 17:
                htmlStatus = `
                    <span class="badge bg-primary-subtle text-dark border">Proses Penyimpanan</span>
                `;
                break;
            case 18:
                htmlStatus = `
                    <span class="badge bg-primary-subtle text-dark border">Proses Pembuatan Label</span>
                `;
                break;
            case 19:
                htmlStatus = `
                    <span class="badge bg-primary-subtle text-dark border">Proses Scan LHU</span>
                `;
                break;
            case 20:
                htmlStatus = `
                    <span class="badge bg-primary-subtle text-dark border">Proses Pelabelan</span>
                `;
                break;
        }
    } else if (feature == 'invoice') {
        if(status == '5'){
            htmlStatus = `<span class="badge bg-success-subtle text-dark border border-success">Sudah dibayar</span>`;
        } else if(status == 4) {
            htmlStatus = `<span class="badge bg-info-subtle text-dark border border-info">Sedang dikonfirmasi</span>`;
        } else if(status == 3) {
            htmlStatus = `<span class="badge bg-warning-subtle text-dark border border-warning">Perlu dibayar</span>`;
        } else {
            htmlStatus = `<span class="badge bg-danger-subtle text-dark border border-danger">Belum dibayar</span>`;
        }
    } else if (feature == 'kontrak') {
        switch (status) {
            case 1:
                htmlStatus = `<span class="badge bg-primary-subtle text-dark border border-primary">Kontrak sedang berjalan</span>`;
                break;
            case 2:
                htmlStatus = `<span class="badge bg-success-subtle text-dark border border-success">Kontrak selesai</span>`;
                break;
        }
    }

    return htmlStatus;
}

/**
 * Returns the icon filename based on the document MIME type.
 *
 * @param {string} type - The MIME type of the document.
 * @returns {string} The filename of the corresponding icon.
 */
function iconDocument(type) {
    let icon = '';
    switch (type) {
        case 'application/pdf':
            icon = 'pdf-icon.svg';
            break;
        case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
            icon = 'word-icon.svg';
            break;
        default:
            icon = 'other-icon.svg';
            break;
    }
    return icon;
}

/**
 * Formats a given size in bytes into a more readable string with appropriate units.
 *
 * @param {number} size - The size in bytes to format.
 * @param {number} [precision=2] - The number of decimal places to include in the formatted string.
 * @returns {string} The formatted size string with appropriate units (B, KB, MB, GB, TB).
 */
function formatBytes(size, precision = 2) {
    const base = Math.log(size) / Math.log(1024);
    const suffixes = ['B', 'KB', 'MB', 'GB', 'TB'];

    return (Math.pow(1024, base - Math.floor(base))).toFixed(precision) + ' ' + suffixes[Math.floor(base)];
}

/**
 * Untuk mengatur dropify
 * @param type masukan "init/reload/reset"
 * @param idElement Id Element ajax
 * @param options Options sesuai dengan setting dokumentasi dropify
 */
function setDropify(type = 'init', idElement, options = {}) {
    const dropifyFile = $(idElement).dropify();
    const dataDropify = dropifyFile.data('dropify');
    $(idElement).attr('data-status-file', '');
    switch (type) {
        case 'init':
            dataDropify.resetPreview();
            dataDropify.clearElement();
            for (const key in options) {
                if (Object.hasOwnProperty.call(options, key)) {
                    const value = options[key];
                    dataDropify.settings[key] = value;
                }
            }
            dataDropify.destroy();
            dataDropify.init();
            break;
        case 'reload':
            dataDropify.resetFile();
            dataDropify.resetPreview();
            dataDropify.clearElement();

            for (const key in options) {
                if (Object.hasOwnProperty.call(options, key)) {
                    const value = options[key];
                    dataDropify.settings[key] = value;
                    if (key == 'defaultFile') {
                        dataDropify.destroy();
                        dataDropify.init();
                        $(idElement).attr('data-default-file', value);
                    }
                }
            }

            const afterClear = (event, element) => {
                $(element.element).attr('data-default-file', '');
                dropifyFile.off('dropify.afterClear', afterClear);
            };
            dropifyFile.on('dropify.afterClear', afterClear);
            break;
        case 'reset':
            dataDropify.settings['defaultFile'] = false;
            dataDropify.destroy();
            dataDropify.init();
            break;
    }

    dropifyFile.off('dropify.beforeClear', removeDropify);
    dropifyFile.on('dropify.beforeClear', removeDropify);

    const onError = (evt) => {
        $(idElement).attr('data-status-file', 'error');
    };
    dropifyFile.off('dropify.errors', onError);
    dropifyFile.on('dropify.errors', onError);
}

function removeDropify(event, element) {
    if (element.file.name) {
        element.resetFile();
        element.resetPreview();
        element.clearElement();
        return false;
    } else {
        return true;
    }
}

function stringSplit(str, prefix) {
    if (str.startsWith(prefix)) {
        str = str.substring(prefix.length);
    }
    return str;
}

function formatSelect2Staff(state) {
    if(!state.id){
        return state.text;
    }

    let $content = $(
        `
            <div class="d-flex justify-content-between">
                <div class="row">
                    <div>${state.text}</div>
                </div>
                <div class="text-body-secondary fs-6">${state.title != '' ? state.title : ''}</div>
            </div>
        `
    )

    return $content;
}


/**
 * Generates HTML string for pagination controls.
 *
 * @param {Object} pagination - The pagination data.
 * @param {number} pagination.current_page - The current page number.
 * @param {number} pagination.last_page - The last page number.
 * @returns {string} The HTML string for pagination controls.
 */
function createPaginationHTML(pagination) {
    // Periksa apakah data pagination ada
    if (pagination.last_page == 1) {
        return '';
    }

    // Buat string HTML untuk pagination
    let html = '<nav aria-label="Page navigation example"><ul class="pagination pagination-sm mb-0 d-flex align-items-center justify-content-end mt-4">';

    // Tambahkan tombol First dan Previous
    html += `<li class="page-item ${pagination.current_page == 1 ? 'disabled' : ''}"><a class="page-link" href="javascript:void(0)" data-page="1">First</a></li>`;
    html += `<li class="page-item ${pagination.current_page == 1 ? 'disabled' : ''}"><a class="page-link" href="javascript:void(0)" data-page="${pagination.current_page - 1}" aria-label="Previous">&laquo;</a></li>`;

    // Tambahkan tombol-tombol halaman
    for (let i = 1; i <= pagination.last_page; i++) {
        html += `<li class="page-item ${i === pagination.current_page ? 'active' : ''}"><a class="page-link" href="javascript:void(0)" data-page="${i}">${i}</a></li>`;
    }

    // Tambahkan tombol Next dan Last
    html += `<li class="page-item ${pagination.current_page == pagination.last_page ? 'disabled' : ''}"><a class="page-link" href="javascript:void(0)" data-page="${pagination.current_page + 1}" aria-label="Next">&raquo;</a></li>`;
    html += `<li class="page-item ${pagination.current_page == pagination.last_page ? 'disabled' : ''}"><a class="page-link" href="javascript:void(0)" data-page="${pagination.last_page}">Last</a></li>`;

    // Tutup tag HTML
    html += '</ul></nav>';

    return html;
}

function unmask(data) {
    const regMask = ['.', ',', '-'];
    let unmaskedAmount = data;

    // Loop through each character in the mask array and remove it from the data
    regMask.forEach(maskChar => {
        unmaskedAmount = unmaskedAmount.split(maskChar).join('');
    });

    return unmaskedAmount;
}


/**
 * Sends an AJAX POST request to the specified URL with the given parameters.
 *
 * @param {string} url - The endpoint URL to send the request to.
 * @param {FormData} params - The parameters to include in the request body.
 * @param {Function} [callback=() => {}] - The function to call if the request is successful.
 * @param {Function} [onError=() => {}] - The function to call if the request fails.
 */
function ajaxPost(url, params, callback = () => {}, onError = () => {}, onProgress = false) {
    params.append('_token', csrf);
    let xhr = onProgress ? {xhr: onProgress} : false;
    $.ajax({
        url: `${base_url}/${url}`,
        method: 'POST',
        dataType: 'json',
        processData: false,
        contentType: false,
        headers: {
            'Authorization': `Bearer ${bearer}`
        },
        data: params,
        ...xhr
    }).done(callback).fail(error => {
        const result = error.responseJSON;
        switch (result?.meta?.code) {
            case 500:
                Swal.fire({
                    icon: "error",
                    text: 'Terjadi kesalahan. Silakan coba lagi.',
                });
                console.error(result.data.msg);
                break;
            case 422:
            case 400:
                Swal.fire({
                    icon: "warning",
                    text: result.data.msg,
                });
                console.info(result.data.msg);
                break;
            default:
                Swal.fire({
                    icon: "error",
                    text: 'Terjadi kesalahan. Silakan coba lagi.',
                });
                console.error(error.responseText);
                break;
        }

        onError(error);
    })
}

/**
 * Makes an AJAX GET request.
 *
 * @param {string} url - The endpoint URL to send the request to.
 * @param {Object} params - The parameters to include in the request.
 * @param {Function} [callback=() => {}] - The function to call if the request is successful.
 * @param {Function} [onError=() => {}] - The function to call if the request fails.
 */
function ajaxGet(url, params, callback = () => {}, onError = () => {}) {
    $.ajax({
        method: 'GET',
        url: `${base_url}/${url}`,
        dataType: 'json',
        processData: true,
        headers: {
            'Authorization': `Bearer ${bearer}`,
            'Content-Type': 'application/json'
        },
        data: params
    }).done(callback).fail(error => {
        const result = error.responseJSON;
        if(result?.meta?.code && result.meta.code == 500){
            Swal.fire({
                icon: "error",
                text: 'Terjadi kesalahan. Silakan coba lagi.',
            });
            console.error(result.data.msg);
        }else{
            Swal.fire({
                icon: "error",
                text: 'Terjadi kesalahan. Silakan coba lagi.',
            });
            console.error(error);
        }

        onError(error);
    })
}

/**
 * Sends an AJAX DELETE request to the specified URL after user confirmation.
 *
 * @param {string} url - The URL to send the DELETE request to.
 * @param {Function} [callback=() => {}] - The callback function to execute if the request is successful.
 * @param {Function} [onError=() => {}] - The callback function to execute if the request fails.
 */
function ajaxDelete(url, callback = () => {}, onError = () => {}){
    Swal.fire({
        icon: 'warning',
        title: 'Are you sure?',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'No, cancel!',
        customClass: {
            confirmButton: 'btn btn-outline-success mx-1',
            cancelButton: 'btn btn-outline-danger mx-1'
        },
        buttonsStyling: false,
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `${base_url}/${url}`,
                method: 'DELETE',
                dataType: 'json',
                processData: true,
                headers: {
                    'Authorization': `Bearer ${bearer}`,
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf
                }
            }).done(callback).fail(error => {
                const result = error.responseJSON;
                if(result?.meta?.code && result.meta.code == 500){
                    Swal.fire({
                        icon: "error",
                        text: 'Terjadi kesalahan. Silakan coba lagi.',
                    });
                    console.error(result.data.msg);
                }else{
                    Swal.fire({
                        icon: "error",
                        text: 'Terjadi kesalahan. Silakan coba lagi.',
                    });
                    console.error(error);
                }
        
                onError(error);
            });
        }
    })
}

function printMedia(media, folder=false, option = {}){
    const options = {
        download: option.download == undefined ? true : option.download,
        date: option.date == undefined ? true : option.date,
        size:  option.size == undefined ? true : option.size,
        onRemove: option.onRemove == undefined ? false : option.onRemove,
        isHtml: option.isHtml == undefined ? false : option.isHtml
    }

    const dateContent = options.date ? `<span class="text-submain caption text-secondary">${dateFormat(media.created_at, 1)}</span>` : '';
    const sizeContent = options.size ? `<small class="text-submain caption" style="margin-top: -3px;">${formatBytes(media.file_size)}</small>` : '';
    
    if(options.isHtml){
        return `
            <div
                class="d-flex align-items-center justify-content-between px-3 shadow-sm cursoron document border">
                    <a class="d-flex align-items-center w-100" href="${base_url}/storage/${folder ? folder : media.file_path}/${media.file_hash}" target="_blank">
                        <div>
                            <img class="my-3" src="${base_url}/icons/${iconDocument(media.file_type)}" alt=""
                                style="width: 24px; height: 24px;">
                        </div>
                        <div class="flex-grow-1 ms-2">
                            <div class="d-flex flex-column">
                                <span class="caption text-main">${media.file_ori}</span>
                                ${dateContent}
                            </div>
                        </div>
                        <div class="col-md-3">
                            ${sizeContent}
                        </div>
                    </a>
                <div class="d-flex align-items-center"></div>
            </div>
        `;
    }

    const downloadContent = document.createElement('button');
    downloadContent.className = 'btn btn-sm btn-link';
    downloadContent.title = 'Download file';
    downloadContent.innerHTML = '<i class="bi bi-download"></i>';
    downloadContent.onclick = () => {
        console.log("click download");
    }

    const removeContent = document.createElement('button');
    removeContent.className = 'btn btn-sm btn-outline-danger';
    removeContent.title = 'Remove';
    removeContent.innerHTML = '<i class="bi bi-trash"></i>';
    removeContent.onclick = options.onRemove;

    const div1 = document.createElement('div');
    div1.className = `d-flex align-items-center justify-content-between px-3 shadow-sm cursoron document border mb-2`;

    const linkMedia = document.createElement('a');
    linkMedia.className = 'd-flex align-items-center w-100';
    linkMedia.href = `${base_url}/storage/${folder ? folder : media.file_path}/${media.file_hash}`;
    linkMedia.target = '_blank';

    const divImg = document.createElement('div');
    const img = document.createElement('img');
    img.className = 'my-3';
    img.src = `${base_url}/icons/${iconDocument(media.file_type)}`;
    img.style = 'width: 24px; height: 24px;';
    divImg.append(img);

    const divDesc = document.createElement('div');
    divDesc.className = 'flex-grow-1 ms-2 d-flex flex-column pe-3 text-start';
    divDesc.innerHTML = `
        <span class="caption text-main">${media.file_ori}</span>
        ${dateContent}
    `;

    const divSize = document.createElement('div');
    divSize.className = 'col-md-3';
    divSize.innerHTML = sizeContent;

    const divAction = document.createElement('div');
    divAction.className = 'p-1';

    // Action
    options.download && divAction.append(downloadContent);
    options.onRemove && divAction.append(removeContent);

    linkMedia.append(divImg);
    linkMedia.append(divDesc);
    linkMedia.append(divSize);
    
    div1.append(linkMedia);
    div1.append(divAction);

    return div1;

    
}

/**
 * Toggles a spinner on a given element.
 *
 * @param {string} [status='show'] - The status of the spinner, either 'show' or 'hide'.
 * @param {HTMLElement} obj - The target element to which the spinner will be added or removed.
 * @param {Object} [options={}] - Additional options for the spinner.
 * @param {string} [options.place='before'] - The position of the spinner relative to the target element, either 'before' or 'after'.
 * @param {string|boolean} [options.width=false] - The width of the spinner.
 * @param {string|boolean} [options.height=false] - The height of the spinner.
 */
function spinner(status = 'show', obj, options = {}){
    options = {
        place: options.place ? options.place : 'before', // after or before
        width: options.width ? options.width : false,
        height: options.height ? options.height : false,
    }
    if(status == 'show'){
        const spin = document.createElement('span');
        spin.role = 'status';
        options.width && (spin.style.width = options.width);
        options.height && (spin.style.height = options.height);
        if(options.place == 'after'){
            spin.className = `spinner-border spinner-border-sm ms-1`;
            $(obj).attr('disabled', true).append(spin);
        } else {
            spin.className = `spinner-border spinner-border-sm me-1`;
            $(obj).attr('disabled', true).prepend(spin);
        }
    }else if(status == 'hide'){
        $(obj).attr('disabled', false).children('.spinner-border').remove();
    }
}

function validate(...data){
    console.log(data);
}

function showPreviewKtp(obj) {
    let path = $(obj).data('path');
    let file = $(obj).data('file');

    $('#img-preview-ktp').attr('src', `${base_url}/storage/${path}/${file}`);
    $('#modal-preview-ktp').modal('show');
}

// Signature
/**
 * Creates a signature pad within a specified parent element.
 *
 * @param {HTMLElement} parent - The parent element to append the signature pad to.
 * @param {Object} options - Configuration options for the signature pad.
 * @param {string} [options.text=''] - Text to display below the signature pad.
 * @param {string} [options.name=''] - Name to display below the text.
 * @param {string|boolean} [options.defaultSig=false] - URL of the default signature image. If false, no default image is used.
 * @param {number} [options.width=200] - Width of the signature pad.
 * @param {number} [options.height=120] - Height of the signature pad.
 * @returns {SignaturePad} - The created SignaturePad instance.
 */
function signature(parent, options){
    options = {
        text: options.text ? options.text : '',
        name: options.name ? options.name : '',
        defaultSig: options.defaultSig ? options.defaultSig : false,
        width: options.width ? options.width : 200,
        height: options.height ? options.height : 120
    };

    // Create Element canvas
    const canvas = document.createElement('canvas');
    canvas.className = 'p-0 signature-pad border border-success-subtle rounded border-2';
    canvas.width = options.width;
    canvas.height = options.height;
    canvas.style.width = `${options.width}px`;
    canvas.style.height = `${options.height}px`;

    // Create Element remove
    const btnRemove = document.createElement('button');
    btnRemove.className = 'btn btn-danger btn-sm position-absolute ms-1 mt-1 z-2';
    btnRemove.innerHTML = '<i class="bi bi-trash"></i>';

    // Create Element text
    const text = document.createElement('p');
    text.className = 'text-center mb-0';
    text.innerText = options.text;

    // Create Element text
    const name = document.createElement('p');
    name.className = 'text-center mb-0';
    name.innerText = `(${options.name})`;

    if(options.defaultSig){
        // Create Element img default
        const img = document.createElement('img');
        img.className = 'rounded border p-0';
        img.width = options.width;
        img.height = options.height;
        img.src = options.defaultSig;

        parent.appendChild(img);
    }else{
        parent.appendChild(btnRemove);
        parent.appendChild(canvas);
    }

    parent.appendChild(text);
    options.name != '' && parent.appendChild(name);

    const signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgb(255, 255, 255)'
    });

    btnRemove.onclick = () => {
        signaturePad.clear();
    }


    return signaturePad;
}

function getCurrentPeriod(periods) {
    const today = new Date(); // Tanggal hari ini
    let currentPeriod = null;

    periods.forEach(period => {
        const startDate = new Date(period.start_date);
        const endDate = new Date(period.end_date);

        if (today >= startDate && today < endDate) {
            currentPeriod = {
                name: `Periode ${period.periode}`,
                endDate: endDate
            };
        }
    });

    // Jika hari ini sebelum semua periode dimulai
    if (today < new Date(periods[0].start_date)) {
        return "notstarted";
    }

    // Jika hari ini setelah semua periode selesai
    if (today >= new Date(periods[periods.length - 1].end_date)) {
        return "ended";
    }

    return currentPeriod;
}

function getDaysRemaining(tanggal, status) {
    const today = new Date();
    const value = new Date(tanggal);
    const timeDiff = value - today;
    return Math.ceil(timeDiff / (1000 * 60 * 60 * 24)); // Konversi ms ke hari
}

function diffToday(date) {
    const now = new Date();
    const targetDate = new Date(date);
    const diff = now - targetDate;

    if (diff >= 0) {
        const diffDays = Math.floor(diff / (1000 * 60 * 60 * 24));
        const diffHours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const diffMinutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));

        if (diffDays > 7) {
            // Mengembalikan format tanggal jika sudah lebih dari 1 minggu
            return dateFormat(targetDate, 1);
        } else if (diffDays > 0) {
            return diffDays !== 1 ? `${diffDays} days ago` : `${diffDays} day ago`;
        } else {
            if (diffHours > 1) {
                return `${diffHours} hours ago`;
            } else if (diffHours > 0) {
                return `${diffHours} hour ago`;
            } else {
                return `${diffMinutes} min ago`;
            }
        }
    } else {
        return 'In the future';
    }
}


function htmlNoData(){
    return `
        <div class="d-flex flex-column align-items-center py-3">
            <img src="${base_url}/images/no_data2_color.svg" style="width:220px" alt="">
            <span class="fw-bold mt-3 text-muted">No Data Available</span>
        </div>
    `;
}