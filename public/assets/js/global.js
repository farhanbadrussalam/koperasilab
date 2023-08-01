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

    $('.maskNPWP').inputmask('99.999.999.9-999.999', { "placeholder": "_" });
    $('.maskNIK').inputmask('9999999999999999', { "placeholder": "_" });
    $('.maskTelepon').inputmask('9999-9999-9999', { "placeholder": " " });
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
    }

    return htmlStatus;
}
