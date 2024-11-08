let dataPermohonan = false;
const arrImgBukti = [];
$(function (){
    $('#jenis_pengiriman').select2({
        theme: "bootstrap-5",
        placeholder: "Pilih jenis pengiriman"
    });

    $('#btn-modal-search').on('click', () => {
        const tipe = $('#tipe-search').val();
        const jenisPengiriman = $('#jenis_pengiriman').val();

        if(jenisPengiriman.length == 0){
            return Swal.fire({
                icon: 'warning',
                text: `Silahkan pilih jenis pengiriman terlebih dahulu`
            });
        }

        $('#label-tipe-search').text(tipe == 'no_kontrak' ? 'No Kontrak' : 'Permohonan');

        loadPermohonan();

        $('#modal-search').modal('show');
    });

    $('#btnSearch').on('click', () => {

    });

    $('#periode').on('change', obj => {
        if(dataPermohonan){
            let arrPeriode = JSON.parse(dataPermohonan.periode_pemakaian);
            let val = arrPeriode[obj.target.value];
            
            if(val){
                let text = `${dateFormat(val.start_date, 4)} s/d ${dateFormat(val.end_date, 4)}`;
                $('#text-periode').val(text);
            }else{
                $('#text-periode').val('');
            }
        }
    });

    $('#btnTambahBukti').on('click', obj => {
        
        let imgtmp = $('#uploadBuktiPengiriman')[0].files[0];

        if(imgtmp && arrImgBukti.length < 5){
            spinner('show', $(obj.target));
            
            arrImgBukti.push(imgtmp);
            loadPreviewBukti();
            spinner('hide', $(obj.target));
            $('#uploadBuktiPengiriman').val('');
        }
    });

    $('#btnSendDocument').on('click', obj => {
        spinner('show', $(obj.target));
        if(validateForm()){
            let jenisPengiriman = $('#jenis_pengiriman').val();
            let noResi = $('#no_resi').val();
            let idPermohonan = dataPermohonan.permohonan_hash;
            let noKontrak = dataPermohonan.no_kontrak;
            let alamat = $('#alamat').val();
            let periode = $('#periode').val();
            let arrPeriode = JSON.parse(dataPermohonan.periode_pemakaian);

            const formData = new FormData();
            formData.append('jenisPengiriman', jenisPengiriman);
            formData.append('noResi', noResi);
            formData.append('idPermohonan', idPermohonan);
            formData.append('noKontrak', noKontrak);
            formData.append('alamat', alamat);
            formData.append('tujuan', dataPermohonan.pelanggan.id);
            formData.append('periode', JSON.stringify(arrPeriode[periode]));
            formData.append('status', 1);
            arrImgBukti.forEach((file, index) => {
                formData.append('buktiPengiriman[]', file);
            });

            ajaxPost('api/v1/pengiriman/action', formData, result => {
                spinner('hide', $(obj.target));
                if(result.meta.code == 200) {
                    Swal.fire({
                        icon: "success",
                        text: "Document sedang dikirim"
                    }).then(() => {
                        window.location.href = `${base_url}/staff/pengiriman`;
                    });
                }
            }, error => {
                Swal.fire({
                    icon: "error",
                    text: 'Server error',
                });
                spinner('hide', $(obj));
                console.error(error.responseJSON.data.msg);
            });

        }else{
            spinner('hide', $(obj.target));
        }
    });

    function loadPermohonan(){
        const tipe = $('#tipe-search').val();
        $('#loading-content').empty();
        spinner('show', $('#loading-content'), {
            width: '50px',
            height: '50px'
        });

        $('#loading-content').show();
        $('#list-content').hide();
        let search = $('#inputSearch').val();
        ajaxGet(`api/v1/pengiriman/getPermohonan`, {
            tipe: tipe,
            search: search
        }, result => {
            let html = '';
            console.log(result);

            for (const value of result.data) {
                let periode = JSON.parse(value.periode_pemakaian);
                html = `
                    <div class="card mb-2">
                        <div class="card-body p-2 d-flex align-items-center">
                            <div class="flex-fill">
                                <div class="title fw-bolder" id="txt-title">${value.no_kontrak}</div>
                                <small class="subdesc text-body-secondary fw-light lh-sm">
                                    <div>Pelanggan: ${value.pelanggan.name}</div>
                                    <div>${value.layanan_jasa.nama_layanan} - ${value.jenis_layanan_parent.name}</div>
                                </small>
                            </div>
                            <div class="flex-fill text-center">
                                <div id="txt-periode">${periode.length} Periode</div>
                                <div id="txt-pengguna">${value.jumlah_pengguna} Pengguna</div>
                            </div>
                            <div class="flex-fill text-center" data-id="${value.permohonan_hash}">
                                <button class="btn btn-outline-primary btn-sm" onclick="pilihPermohonan(this)">Pilih</button>
                            </div>
                        </div>
                    </div>
                `;
            }

            $('#list-content').html(html);
            $('#loading-content').hide();
            $('#list-content').show();
        });
    }
    
});

function pilihPermohonan(obj) {
    let id = $(obj).parent().data('id');
    
    spinner('show', $(obj));
    ajaxGet(`api/v1/pengiriman/getPermohonan`, {
        idPermohonan: id
    }, result => {
        console.log(result);
        dataPermohonan = result.data;
        loadForm();
        $('#modal-search').modal('hide');
        spinner('hide', $(obj));
    })
}

function loadForm(){
    let periode = JSON.parse(dataPermohonan.periode_pemakaian);
    let jenisPengiriman = $('#jenis_pengiriman').val();

    $('#no_permohonan').val(dataPermohonan.no_kontrak);
    $('#pelanggan').val(dataPermohonan.pelanggan.name);

    let htmlSelect = '<option value="">Pilih periode</option>';
    for (const [i,value] of periode.entries()) {
        htmlSelect += `<option value='${i}'>Periode ${i+1}</option>`;
    }

    $('#periode').html(htmlSelect);

    let htmlJenis = ``;
    for (const jenis of jenisPengiriman) {
        switch (jenis) {
            case 'invoice':
                let badgeStatus = '';
                if(dataPermohonan.invoice.status == 5){
                    badgeStatus = `<span class="badge text-bg-primary rounded-pill">Status : Sudah bayar</span>`
                }else{
                    badgeStatus = `<span class="badge text-bg-danger rounded-pill">Status : Belum bayar</span>`
                }
                htmlJenis += `
                    <li class="list-group-item d-flex justify-content-between align-items-center p-2">
                        <div class="ms-2 me-auto">
                            <div class="fw-bold">Invoice</div>
                            ${dataPermohonan.invoice.no_invoice}
                        </div>
                        ${badgeStatus}
                    </li>
                `;
                break;
        
            default:
                break;
        }
    }

    $('#list-jenis').html(htmlJenis);
}

function loadPreviewBukti(){
    $('#list-preview-bukti').html('');
    for (const [i,img] of arrImgBukti.entries()) {
        const reader = new FileReader();
        reader.onload = function(e) {
            let divMain = document.createElement('div');
            divMain.className = '';
            divMain.style.width = '100px';
            divMain.style.height = '100px';

            const preview = document.createElement('img');
            preview.src = e.target.result;
            preview.className = 'img-thumbnail';
            preview.style.width = '100px';
            preview.style.height = '100px';
            preview.style.cursor = 'pointer';
            preview.onclick = () => {
                $('#modal-preview-image').attr('src', e.target.result);

                $('#modal-preview').modal('show');
            }

            const btnRemove = document.createElement('button');
            btnRemove.className = 'btn btn-danger btn-sm position-absolute mt-2 ms-2';
            btnRemove.innerHTML = '<i class="bi bi-trash"></i>';
            btnRemove.onclick = () => {
                arrImgBukti.splice(i, 1);
                loadPreviewBukti();
            }

            divMain.append(btnRemove);
            divMain.append(preview);

            document.getElementById('list-preview-bukti').appendChild(divMain);
        };
        reader.readAsDataURL(img);
    }

}

function validateForm(){
    let jenisPengiriman = $('#jenis_pengiriman').val();
    let noResi = $('#no_resi').val();
    let periode = $('#periode').val();
    let bukti = arrImgBukti.length;

    if(jenisPengiriman == '' || noResi == '' || periode == '' || bukti == 0){
        Swal.fire({
            icon: 'warning',
            text: 'Harap pastikan semua form sudah diisi dengan benar'
        });

        return false;
    }

    return true;
}