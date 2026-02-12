const tmpArrTld = [];
let inventoryTld = false;
let JL = '';
let htmlDisabled = true;

$(function () {
    inventoryTld = new Inventory_tld({
        preview: true,
        no_kontrak: dataKontrak.no_kontrak
    });
    inventoryTld.on('inventory.selected', (e) => {
        const detail = e.detail;
        $(`#${detail.selected}`).val(detail.data_tld.no_seri_tld);
        $(`#${detail.selected}_view`).html(detail.data_tld.no_seri_tld);

        // reset tmpArrTld
        let index = tmpArrTld.findIndex(d => d.index == detail.selected);

        if(index > -1){
            tmpArrTld[index].tld = detail.data_tld.tld_hash;
        }
    });
    let htmlAlamat = '<option value="">Pilih alamat</option>';
    for (const [i,value] of dataKontrak.pelanggan.perusahaan.alamat.entries()) {
        if(value.status){
            htmlAlamat += `<option value='${i}'>Alamat ${value.jenis}</option>`;
        }
    }

    $('#selectAlamat').html(htmlAlamat);

    $('#selectAlamat').on('change', obj => {
        if(dataKontrak){
            const perusahaan = dataKontrak.pelanggan.perusahaan;

            if(perusahaan.alamat[obj.target.value] && perusahaan.alamat[obj.target.value].alamat){
                $('#txt_alamat').val(perusahaan.alamat[obj.target.value].alamat + ", "+ perusahaan.alamat[obj.target.value].kode_pos);
            }else{
                $('#txt_alamat').val('');
            }
        }
    });

    JL = jenislayanan(dataKontrak.jenis_layanan_parent, dataKontrak.jenis_layanan);

    if(tmpArrEvaluasi.includes(JL)){
        htmlDisabled = false;
    }

    if(StringZerocek == JL){
        if(dataKontrak.is_have_tld == 1) {
            htmlDisabled = false;
        }
    }

    loadTld();

    $('#checkAllTldPengguna').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('input[name="checkTldPengguna"]').prop('checked', isChecked);
    });
})

function loadTld() {
    let tldPengguna = dataKontrak.kontrak_detail.filter(tld => tld.jenis == 'pengguna');
    let tldKontrol = dataKontrak.kontrak_detail.filter(tld => tld.jenis == 'kontrol');

    loadTldKontrol(tldKontrol);
    loadPengguna(tldPengguna);
}

function loadTldKontrol(tldKontrol) {
    let htmlTldKontrol = '';
    let isPeriodOne = dataPeriodeNow.count_tld == 1;
    ajaxGet(`api/v1/tld/searchTldNotUsed`, {jenis: 'kontrol'}, result => {
       let tldNotUsed = result.data;
       let noTld = 0;
       for (const [i, list] of tldKontrol.entries()) {
           let dataTld = null;
           let type = 'baru';
           if(isPeriodOne){
               if(list.tld_1){
                   dataTld = list.tld_1;
                   type = 'lama';
               } else {
                   dataTld = tldNotUsed[noTld];
                   noTld++;
               }
           } else {
               if(list.tld_2){
                   dataTld = list.tld_2;
                   type = 'lama';
               } else {
                   dataTld = tldNotUsed[noTld];
                   noTld++;
               }
           }
          tmpArrTld.push({
              id: list.kontrak_detail_hash,
              tld: dataTld.tld_hash,
              index: `tldNoSeri_${i}_kontrol`,
              type
          });

          let kodeLencana = i == 0 ? 'C' : `C${i}`;

          const dataCard = {
            index: i,
            idHash: list.kontrak_detail_hash,
            name: `Kontrol ${list.entitas?.name ?? ''} ${kodeLencana}`,
            kode: kodeLencana,
            isCheckedEvaluasi: true,
            tldHash: dataTld.tld_hash,
            no_seri_tld: dataTld.no_seri_tld,
            htmlDisabled: htmlDisabled
          }

          htmlTldKontrol += cardKontrolComponent(dataCard, {
            label_tld: true
          });
       }

       $('#tld-kontrol-content').html(htmlTldKontrol);
    });
}

function loadPengguna(tldPengguna){
    let htmlPengguna = '';
    let isPeriodOne = dataPeriodeNow.count_tld == 1;
    ajaxGet(`api/v1/tld/searchTldNotUsed`, {jenis: 'pengguna'}, result => {
        let tldNotUsed = result.data;
        let noTld = 0;
        for (const [i, value] of tldPengguna.entries()) {
            let pengguna = value;
            let fileKtp = pengguna.entitas?.media_ktp ? `${base_url}/storage/${pengguna.entitas.media_ktp.file_path}/${pengguna.entitas.media_ktp.file_hash}` : '';

            let dataTld = null;
            let type = 'baru';
            if(isPeriodOne){
               if(value.tld_1){
                   dataTld = value.tld_1;
                   type = 'lama';
               } else {
                   dataTld = tldNotUsed[noTld];
                   noTld++;
               }
           } else {
               if(value.tld_2){
                   dataTld = value.tld_2;
                   type = 'lama';
               } else {
                   dataTld = tldNotUsed[noTld];
                   noTld++;
               }
           }

            tmpArrTld.push({
                id: value.kontrak_detail_hash,
                tld: dataTld.tld_hash,
                index: `tldNoSeri_${i}_pengguna`,
                type
            });

            const dataCard = {
                index: i,
                idHash: value.kontrak_detail_hash,
                tldHash: dataTld.tld_hash,
                name: pengguna.entitas.name,
                divisi: pengguna.entitas.divisi?.name || '',
                isCheckedEvaluasi: true,
                radiasi: pengguna.entitas.radiasi?.map(r => r.nama_radiasi),
                fileKtp: fileKtp,
                no_seri_tld: dataTld.no_seri_tld || '',
                htmlDisabled: htmlDisabled
            }

            htmlPengguna += cardPenggunaComponent(dataCard, {
                label_tld: true
            })
        }

        $('#pengguna-list-container').html(htmlPengguna);
        showPopupReload();
    });
}

function buatPermohonan(obj){
    let jenisLayanan = dataJenisLayanan.jenis_layanan_hash;
    let jenisLayananParent = dataJenisLayanan.parent_hash;
    let idKontrak = dataKontrak.kontrak_hash;
    let periode = dataPeriodeNow;
    let alamatIndex = $('#selectAlamat').val();

    let checkTld = [];
    $('input[name="checkTldPengguna"]:checked, input[name="checkTldKontrol"]:checked').each(function() {
        checkTld.push($(this).val());
    });

    if(!alamatIndex){
        Swal.fire({
            icon: 'warning',
            text: 'Alamat belum dipilih. Silakan pilih alamat terlebih dahulu.'
        });
        return;
    }

    Swal.fire({
        title: 'Buat Permohonan?',
        text: "Apakah Anda yakin ingin membuat permohonan ini?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',  // Green
        cancelButtonColor: '#d33',     // Red
        confirmButtonText: 'Ya, Buat!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {

            let alamatData = dataKontrak.pelanggan.perusahaan.alamat[alamatIndex]; // Get address data

            const params = new FormData();
            params.append('jenisLayanan2', jenisLayanan);
            params.append('jenisLayanan1', jenisLayananParent);
            params.append('idKontrak', idKontrak);
            params.append('periode', periode?.periode);
            params.append('alamat', alamatData.alamat_hash); // Send the address hash
            params.append('listTld', JSON.stringify(checkTld));
            params.append('createBy', userActive.user_hash);
            params.append('is_zerocek', 0);
            params.append('haveTld', 1);
            params.append('tipeKontrak', 'kontrak lama');
            params.append('dataTld', JSON.stringify(tmpArrTld));
            // dataPermohonan ? params.append('idPermohonan', dataPermohonan.permohonan_hash) : false;

            params.append('status', 1);

            spinner('show', $(obj)); // Show the spinner on the clicked button

            ajaxPost('api/v1/permohonan/tambahPengajuan', params, result => {
                // Show success message and handle any further actions (like redirecting):
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Permohonan berhasil dibuat.',
                    timer: 1500, // Adjust as needed
                    showConfirmButton: false
                }).then(() => {
                    // e.g., redirect to a different page
                    window.location.href = base_url+"/permohonan/pengajuan";
                });


            }, error => {
                spinner('hide', $(obj));  // Important: hide the spinner on error too!
            });
        } // End of if(result.isConfirmed)
    });

}

function openInventory(obj, jenis){
    let id = $(obj).data('id');
    inventoryTld.show(id, tmpArrTld, jenis);
}
