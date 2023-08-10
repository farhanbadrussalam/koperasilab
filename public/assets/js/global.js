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
function maskReload(){
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

    $('.maskNPWP').inputmask('99.999.999.9-999.999', { "placeholder": "_", "removeMaskOnSubmit" : true });
    $('.maskNIK').inputmask('9999999999999999', { "placeholder": "_", "removeMaskOnSubmit" : true });
    $('.maskTelepon').inputmask('9999-9999-9999', { "placeholder": " ", "removeMaskOnSubmit" : true });
}
maskReload();

function deleteGlobal(callback = ()=>{}) {
    Swal.fire({
        title: 'Are you sure?',
        icon: false,
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'No, cancel!',
        customClass: {
            confirmButton: 'btn btn-success mx-1',
            cancelButton: 'btn btn-danger mx-1'
        },
        buttonsStyling: false,
        reverseButtons: true
    }).then((result) => {
        if(result.isConfirmed){
            callback();
        }
    })
}

function dateFormat(tanggal, time = false){
    let d = new Date(tanggal);

    const options = {
        year: 'numeric',
        month : 'long',
        day : 'numeric'
    };

    let hour = d.getHours() < 10 ? `0${d.getHours()}` : d.getHours();
    let minute = d.getMinutes() < 10 ? `0${d.getMinutes()}` : d.getMinutes();

    return `${hour}:${minute}, ${d.toLocaleString('id-ID', options)}`;
}

function statusFormat(feature, status) {
    let htmlStatus = '';
    status = Number(status);
    if(feature == 'jadwal'){
        switch (status) {
            case 0:
                htmlStatus = `<span class="badge text-bg-secondary">Belum ditugaskan</span>`;
                break;
            case 1:
                htmlStatus = `<span class="badge text-bg-info">Diajukan</span>`;
                break;
            case 2:
                htmlStatus = `<span class="badge text-bg-success">Bersedia</span>`;
                break;
            case 3:
                htmlStatus = `<span class="badge text-bg-danger">Tidak bersedia</span>`;
                break;
            default:
                htmlStatus = `<span class="badge text-bg-danger">dibatalkan</span>`;
                break;
        }
    }else if(feature == 'permohonan'){
        switch (status) {
            case 1:
                htmlStatus = `<span class="badge text-bg-secondary">Pengajuan</span>`;
                break;
            case 2:
                htmlStatus = `<span class="badge text-bg-info">Terverifikasi</span>`;
                break;
            case 3:
                htmlStatus = `<span class="badge text-bg-success">Selesai</span>`;
                break;
            case 9:
                htmlStatus = `<span class="badge text-bg-danger">Di tolak</span>`;
                break;
        }
    }

    return htmlStatus;
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
    // const validateMime = (evt) => {
    //   if (options.validateMime) {
    //     const form_data = new FormData();
    //     form_data.append('files', dataDropify.file.object);
    //     form_data.append('mime', JSON.stringify(options.validateMime));
    //     form_data.append('token', '<?= $this->security->get_csrf_hash() ?>');
    //     form_data.append('<?= $this -> security -> get_csrf_token_name() ?>', '<?= $this->security->get_csrf_hash() ?>');

    //     $.ajax({
    //       url: '<?= base_url() ?>management/validateMime',
    //       method: 'POST',
    //       data: form_data,
    //       contentType: false,
    //       cache: false,
    //       processData: false,
    //       success: function (data) {
    //         if (data == 'notok') {
    //           dataDropify.onFileReady(false);
    //           dataDropify.resetFile();
    //           dataDropify.resetPreview();
    //           dataDropify.clearElement();
    //           $.alert({
    //             title: false,
    //             content: 'The file does not meet the allowed type.',
    //             draggable: false,
    //             animation: 'zoom',
    //             closeAnimation: 'zoom',
    //             animateFromElement: false,
    //             buttons: {
    //               close: {
    //                 text: 'close', // With spaces and symbols,
    //                 action: function () {},
    //               },
    //             },
    //           });
    //         } else if (data == 'ok') {
    //           $(idElement).attr('data-status-file', '');
    //         }
    //       },
    //     });
    //   }
    // };
    // $(idElement).off('change', validateMime);
    // $(idElement).on('change', validateMime);

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
