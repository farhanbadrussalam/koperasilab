function formatRupiah(angka) {
    // Mengubah angka menjadi format mata uang Rupiah
    var format = new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(angka);

    // Mengganti nilai input dengan format Rupiah
    return format;
}
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

    $('.maskNPWP').inputmask('99.999.999.9-999.999', { "placeholder": "_", "removeMaskOnSubmit": true });
    $('.maskNIK').inputmask('9999999999999999', { "placeholder": "_", "removeMaskOnSubmit": true });
    $('.maskTelepon').inputmask('9999-9999-9999', { "placeholder": " ", "removeMaskOnSubmit": true });
}
maskReload();

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
 * @param {integer} type 1,2,false
 * @returns
 */
function dateFormat(tanggal, type = false) {
    let d = new Date(tanggal);

    let options = {};

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
                    <div class="d-flex align-items-center">
                        <div><div class="me-1 dot bg-secondary"></div></div>
                        <span class="subbody-medium text-submain text-truncate">Pengajuan</span>
                    </div>
                    `;
                break;
            case 2:
                htmlStatus = `
                    <div class="d-flex align-items-center">
                        <div><div class="me-1 dot bg-info"></div></div>
                        <span class="subbody-medium text-submain text-truncate">Terverifikasi</span>
                    </div>
                    `;
                break;
            case 3:
                htmlStatus = `
                    <div class="d-flex align-items-center">
                        <div><div class="me-1 dot bg-success"></div></div>
                        <span class="subbody-medium text-submain text-truncate">Selesai</span>
                    </div>
                    `;
                break;
            case 9:
                htmlStatus = `
                    <div class="d-flex align-items-center">
                        <div><div class="me-1 dot bg-danger"></div></div>
                        <span class="subbody-medium text-submain text-truncate">Ditolak</span>
                    </div>
                    `;
                break;
        }
    }

    return htmlStatus;
}

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
