let signaturePad;
$(function() {
    loadForm(user);

    $('#btn-upload-ttd').click(function() {
        if(signaturePad.isEmpty()){
            return Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Tanda tangan tidak boleh kosong' 
            });
        }
        spinner('show', $(this));
        
        let ttd = signaturePad.toDataURL();
        const formData = new FormData();
        formData.append('ttd', ttd);
        formData.append('idProfile', user.user_hash);
        ajaxPost(`api/v1/profile/action`, formData, result => {
            if(result.meta.code == 200){
                document.getElementById('show-ttd').innerHTML = '';
                user.ttd = ttd;
                loadForm(user);
                spinner('hide', $(this));
            }
        })
    });
    $(`#btn-hapus-ttd`).click(function() {
        spinner('show', $(this));
        const formData = new FormData();
        formData.append('idProfile', user.user_hash);
        formData.append('ttd', '');
        ajaxPost(`api/v1/profile/action`, formData, result => {
            if(result.meta.code == 200){
                document.getElementById('show-ttd').innerHTML = '';
                user.ttd = '';
                loadForm(user);
                spinner('hide', $(this));
            }
        })
    });
})

function loadForm(data) {
    $('#nama_instansi').val(data.perusahaan?.nama_perusahaan ? data.perusahaan.nama_perusahaan : '-');
    $('#nama_pic').val(data.name ? data.name : '-');
    $('#jabatan_pic').val(data.jabatan ? data.jabatan : '-');
    $('#email').val(data.email ? data.email : '-');
    $('#telepon').val(data.telepon ? data.telepon : '-');
    $('#npwp').val(data.perusahaan?.npwp_perusahaan ? data.perusahaan.npwp_perusahaan : '-');

    signaturePad = signature(document.getElementById('show-ttd'), {
        width: 300,
        height: 220,
        defaultSig: data.ttd ? data.ttd : false
    });

    if(data.ttd){
        $('#btn-upload-ttd').addClass('d-none');
        $('#btn-hapus-ttd').removeClass('d-none');
    }else{
        $('#btn-upload-ttd').removeClass('d-none');
        $('#btn-hapus-ttd').addClass('d-none');
    }

    let html = '';
    if(data.perusahaan){
        for (const alamat of data.perusahaan.alamat) {
            let jenis = '';
            let checkbox = `
                <div class="form-check form-switch">
                    <input class="form-check-input" onclick="changeAlamat(this)" data-jenis="${alamat.jenis}" type="checkbox" role="switch" id="switch-alamat-${alamat.jenis}" ${alamat.status == 1 ? 'checked' : ''}>
                </div>
            `;
            switch (alamat.jenis) {
                case 'utama':
                    jenis = 'Utama';
                    checkbox = '';
                    break;
                case 'tld':
                    jenis = 'TLD';
                    break;
                case 'lhu':
                    jenis = 'LHU';
                    break;
                case 'invoice':
                    jenis = 'Invoice'
                    break;
            }

            html += `
                <div class="mb-3" data-idalamat="${alamat.alamat_hash}">
                    <div class="d-flex" id="divLabel-${alamat.jenis}">
                        <label class="form-label me-3">Alamat ${jenis}</label>
                        ${checkbox}
                    </div>
                    <div id="alamat-${alamat.jenis}-inactive" class="${alamat.status == 1 ? 'd-none' : 'd-block'}">
                        <p>Alamat sesuai dengan Alamat Utama</p>
                    </div>
                    <div id="alamat-${alamat.jenis}-active" class="d-flex align-items-center ${alamat.status == 1 ? 'd-block' : 'd-none'}">
                        <div class="flex-fill me-2" id="formAlamat-${alamat.jenis}">
                            <textarea name="txt-alamat-${alamat.jenis}" data-field="alamat" id="txt-alamat-${alamat.jenis}" cols="30" rows="3" class="form-control mb-2" disabled>${alamat.alamat}</textarea>
                            <input type="text" class="form-control me-2" data-field="kode_pos" placeholder="Kode pos" id="txt-kode-pos-${alamat.jenis}" value="${alamat.kode_pos}" disabled>
                        </div>
                        <div id="btnEditDiv-${alamat.jenis}" class="d-block" data-field="${alamat.jenis}">
                            <button class="btn btn-outline-secondary btn-sm rounded-circle shadow-sm me-2" title="edit" type="button" onclick="enableEdit(this, 'alamat')"><i class="bi bi-pencil"></i></button>
                        </div>
                        <div id="btnActionDiv-${alamat.jenis}" class="d-none d-flex" data-field="${alamat.jenis}">
                            <button class="btn btn-outline-danger btn-sm rounded-circle shadow-sm me-2" title="Batal" type="button" onclick="batalEdit(this, 'alamat')"><i class="bi bi-x"></i></button>
                            <button class="btn btn-outline-primary btn-sm rounded-circle shadow-sm me-2" title="Simpan" type="button" onclick="simpanEdit(this, 'alamat')" data-idalamat="${alamat.alamat_hash}"><i class="bi bi-check"></i></button>
                        </div>
                    </div>
                </div>
            `;
        }
    } else {
        html += `
            <div class="mb-3">
                <div class="d-flex">
                    <label for="alamat_utama" class="form-label me-3">Alamat Utama</label>
                </div>
                <div class="d-flex align-items-center">
                    <div class="flex-fill me-2">
                        <textarea name="alamat_utama" id="alamat_utama" cols="30" rows="3" class="form-control mb-2" disabled></textarea>
                        <input type="text" class="form-control me-2" placeholder="Kode pos" disabled>
                    </div>
                    <a href="#">Edit</a>
                </div>
            </div>
        `;
    }

    $('#list-alamat').html(html);
}

function changeAlamat(obj){
    let check = $(obj).is(":checked");
    let idAlamat = $(obj).parent().parent().parent().data('idalamat');
    let jenis = $(obj).data('jenis');
    const formParams = new FormData();

    if(check){
        formParams.append('status', 1);

        $(`#alamat-${jenis}-inactive`).addClass('d-none').removeClass('d-block');
        $(`#alamat-${jenis}-active`).addClass('d-block').removeClass('d-none');
    }else{
        formParams.append('status', 0);

        $(`#alamat-${jenis}-inactive`).addClass('d-block').removeClass('d-none');
        $(`#alamat-${jenis}-active`).addClass('d-none').removeClass('d-block');
    }

    saveUpdateForm($(obj).parent(), formParams, idAlamat);
}

function saveUpdateForm(obj, params, id){
    spinner('show', obj, {
        place: 'after'
    });

    params.append('_token', csrf);
    params.append('idAlamat', id);
    ajaxPost(`api/v1/profile/action/alamat`, params, result => {
        spinner('hide', obj);
    }, error => {
        spinner('hide', obj);
    })
}

function enableEdit(obj, tab){
    const inputId = $(obj).parent().data('field');

    // change button to action
    $(`#btnEditDiv-${inputId}`).addClass('d-none').removeClass('d-block');
    $(`#btnActionDiv-${inputId}`).addClass('d-block').removeClass('d-none');

    // change form to canedit
    if(tab == 'alamat') {
        for (const element of $(`#formAlamat-${inputId}`).children()) {
            $(element).attr('disabled', false);
            $(element).data('tmpvalue', element.value);
        }
    }else{
        $(`#${inputId}`).attr('disabled', false);
        $(`#${inputId}`).focus();
        $(`#btnActionDiv-${inputId}`).data('tmpvalue', $(`#${inputId}`).val());
    }
}

function batalEdit(obj, tab){
    const inputId = $(obj).parent().data('field');

    // change button to Edit
    $(`#btnEditDiv-${inputId}`).addClass('d-block').removeClass('d-none');
    $(`#btnActionDiv-${inputId}`).addClass('d-none').removeClass('d-block');

    // change form to canedit
    if(tab == 'alamat') {
        for (const element of $(`#formAlamat-${inputId}`).children()) {
            $(element).attr('disabled', true);
            $(element).val($(element).data('tmpvalue'));
        }
    } else {
        $(`#${inputId}`).attr('disabled', true);
        $(`#${inputId}`).val($(`#btnActionDiv-${inputId}`).data('tmpvalue'));
    }
}

function simpanEdit(obj, tab){
    const inputId = $(obj).parent().data('field');

    // change button to Edit
    $(`#btnEditDiv-${inputId}`).addClass('d-block').removeClass('d-none');
    $(`#btnActionDiv-${inputId}`).addClass('d-none').removeClass('d-block');

    // save edit
    let spinObj = false;
    if(tab == 'alamat'){
        spinObj = $(`#divLabel-${inputId}`);
        let idAlamat = $(obj).data('idalamat');
        const formParams = new FormData();
        for (const element of $(`#formAlamat-${inputId}`).children()) {
            let field = $(element).data('field');
            formParams.append(field, element.value);
        }
        
        saveUpdateForm(spinObj, formParams, idAlamat);
        for (const element of $(`#formAlamat-${inputId}`).children()) {
            $(element).attr('disabled', true);
        }
    }else{
        spinObj = $(obj).parent().parent().parent().children('label');
        const value = $(`#${inputId}`).val();
    
        const formParams = new FormData();
        formParams.append('_token', csrf);
        formParams.append('idProfile', user.user_hash);
        formParams.append(inputId, value);
    
        spinner('show', $(spinObj), {
            place: 'after'
        });
        
        ajaxPost(`api/v1/profile/action`, formParams, result => {
            spinner('hide', $(spinObj));
            // change form to canedit
            $(`#${inputId}`).attr('disabled', true);
        }, error => {
            spinner('hide', $(spinObj));
        })
    }
}