function modalConfirm(id) {
    $.ajax({
        url: "{{ url('api/permohonan/show') }}/" + id,
        method: 'GET',
        dataType: 'json',
        processing: true,
        serverSide: true,
        headers: {
            'Authorization': `Bearer {{ generateToken() }}`,
            'Content-Type': 'application/json'
        }
    }).done(result => {
        const data = result.data;

        $('#txtNamaPelanggan').html(data.user.name);
        $('#txtNamaLayanan').html(data.layananjasa.nama_layanan);
        $('#txtJenisLayanan').html(data.jenis_layanan);
        $('#txtHarga').html(data.tarif);
        $('#txtStart').html(data.jadwal.date_mulai);
        $('#txtEnd').html(data.jadwal.date_end);
        $('#txtStatus').html(statusFormat('permohonan', data.status));
        $('#txtNoBapeten').html(data.no_bapeten);
        $('#txtAntrian').html(data.nomor_antrian);
        $('#txtJeniLimbah').html(data.jenis_limbah);
        $('#txtRadioaktif').html(data.sumber_radioaktif);
        $('#txtJumlah').html(data.jumlah);

        let allDocument = '';
        // ambil dokumen petugas
        if(data.detailPermohonan?.media){
            allDocument += `<label>Petugas</label>`;
            allDocument += printMedia(data.detailPermohonan.media);
        }

        // ambil dokumen pelanggan
        allDocument += `<label class="mt-3">Pelanggan</label>`;
        for (const media of data.media) {
            allDocument += printMedia(media, "dokumen/permohonan");
        }

        $('#tmpDokumenPendukung').html(allDocument);

        $('#divConfirmBtn').show();
        if(role.includes('Pelanggan')){
            $('#divConfirmBtn').hide();
        }else{
            if(data.status == 2 && permission.find(d => d.name == 'Otorisasi-Front desk')){
                $('#divConfirmBtn').hide();
            }else if(data.status == 2 && data.flag == 2){
                $('#btnNo').html('Tidak setuju');
                $('#btnYes').html('Setuju');
                idPermohonan = id;
            }else if(data.status == 3 && permission.find(d => d.name == 'Otorisasi-Penyelia LAB')){
                $('#divConfirmBtn').hide();
            }else if(data.status == 3 && permission.find(d => d.name == 'Otorisasi-Pelaksana LAB')){
                $('#divConfirmBtn').hide();
            }else if(data.status == 3 && permissionInRole.find(d => d.name == 'Keuangan')){
                $('#divConfirmBtn').hide();
            }else{
                idPermohonan = id;
            }
        }
        maskReload();
        $('#confirmModal').modal('show');
    })
}

function printMedia(media, folder=false){
    return `
        <div
            class="mt-2 d-flex align-items-center justify-content-between px-3 mx-1 shadow-sm cursoron document border">
                <div class="d-flex align-items-center w-100">
                    <div>
                        <img class="my-3" src="{{ asset('icons') }}/${iconDocument(media.file_type)}" alt=""
                            style="width: 24px; height: 24px;">
                    </div>
                    <div class="flex-grow-1 ms-2">
                        <div class="d-flex flex-column">
                            <a class="caption text-main" href="{{ asset('storage') }}/${folder ? folder : media.file_path}/${media.file_hash}" target="_blank">${media.file_ori}</a>
                            <span class="text-submain caption text-secondary">${dateFormat(media.created_at, 1)}</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <small class="text-submain caption" style="margin-top: -3px;">${formatBytes(media.file_size)}</small>
                    </div>
                    <div class="p-1">
                        <button class="btn btn-sm btn-link" title="Download file"><i class="bi bi-download"></i></button>
                    </div>
                </div>
            <div class="d-flex align-items-center"></div>
        </div>
        `;
}

export {
    modalConfirm, printMedia
}
