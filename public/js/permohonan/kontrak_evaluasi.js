const tmpArrTld = [];
let inventoryTld = false;
let JL = '';
let htmlDisabled = true;
let source = 'map';

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

        if (index > -1) {
            tmpArrTld[index].tld = detail.data_tld.tld_hash;
        }
    });
    let htmlAlamat = '<option value="">Pilih alamat</option>';
    for (const [i, value] of dataKontrak.pelanggan.perusahaan.alamat.entries()) {
        if (value.status) {
            htmlAlamat += `<option value='${i}'>Alamat ${value.jenis}</option>`;
        }
    }

    $('#selectAlamat').html(htmlAlamat);

    $('#selectAlamat').on('change', obj => {
        if (dataKontrak) {
            const perusahaan = dataKontrak.pelanggan.perusahaan;

            if (perusahaan.alamat[obj.target.value] && perusahaan.alamat[obj.target.value].alamat) {
                $('#txt_alamat').val(perusahaan.alamat[obj.target.value].alamat + ", " + perusahaan.alamat[obj.target.value].kode_pos);
            } else {
                $('#txt_alamat').val('');
            }
        }
    });

    JL = jenislayanan(dataKontrak.jenis_layanan_parent, dataKontrak.jenis_layanan);

    if (tmpArrEvaluasi.includes(JL)) {
        htmlDisabled = false;
    }

    if (StringZerocek == JL) {
        if (dataKontrak.is_have_tld == 1) {
            htmlDisabled = false;
        }
    }

    loadTld();

    $('#checkAllTldPengguna').on('change', function () {
        const isChecked = $(this).is(':checked');
        $('input[name="checkTldPengguna"]').prop('checked', isChecked);
    });

    $('#checkAllTldKontrol').on('change', function () {
        const isChecked = $(this).is(':checked');
        $('input[name="checkTldKontrol"]').prop('checked', isChecked);
    });
})

function loadTld() {
    let tldPengguna = false;
    let tldKontrol = false;
    if (dataKontrak.kontrak_map.length == 0 || (dataKontrak.is_have_tld == 1)) {
        tldPengguna = dataKontrak.kontrak_detail.filter(tld => {
            return tld.jenis == 'pengguna' && tld.status == 1;
        });
        tldKontrol = dataKontrak.kontrak_detail.filter(tld => tld.jenis == 'kontrol' && tld.status == 1);
        source = `detail`;
    } else {
        tldPengguna = dataKontrak.kontrak_map.filter(tld => tld.jenis == 'pengguna');
        tldKontrol = dataKontrak.kontrak_map.filter(tld => tld.jenis == 'kontrol');
        source = `map`;
    }

    loadTldKontrol(tldKontrol, source);
    loadPengguna(tldPengguna, source);
}

function loadTldKontrol(tldKontrol, source) {
    let htmlTldKontrol = '';
    let isPeriodOne = dataPeriodeNow.count_tld == 1;
    ajaxGet(`api/v1/tld/searchTldNotUsed`, { jenis: 'kontrol' }, result => {
        let tldNotUsed = result.data;
        let noTld = 0;
        for (const [i, list] of tldKontrol.entries()) {
            let dataTld = null;
            let type = 'baru';
            let id = false;
            let _htmlDisabled = htmlDisabled;
            let _isCheckedEvaluasi = true;

            if (source == 'map') {
                if (list.tld) {
                    dataTld = list.tld;
                    type = 'lama';
                } else {
                    dataTld = tldNotUsed[noTld];
                    noTld++;
                }
                id = list.kontrak_map_hash;
            } else {
                if (isPeriodOne) {
                    if (list.tld_1) {
                        dataTld = list.tld_1;
                        type = 'lama';
                        if (list.status_tld_1 != 2) {
                            _htmlDisabled = true;
                            _isCheckedEvaluasi = false;
                        }
                    } else {
                        dataTld = tldNotUsed[noTld];
                        noTld++;
                    }
                } else {
                    if (list.tld_2) {
                        dataTld = list.tld_2;
                        type = 'lama';
                        if (list.status_tld_2 != 2) {
                            _htmlDisabled = true;
                            _isCheckedEvaluasi = false;
                        }
                    } else {
                        dataTld = tldNotUsed[noTld];
                        noTld++;
                    }
                }
                id = list.kontrak_detail_hash;
            }

            tmpArrTld.push({
                id: id,
                tld: dataTld.tld_hash,
                index: `tldNoSeri_${i}_kontrol`,
                type,
                source
            });

            let kodeLencana = i == 0 ? 'C' : `C${i}`;

            const dataCard = {
                index: i,
                idHash: id,
                name: `Kontrol ${list.entitas?.name ?? ''} ${kodeLencana}`,
                kode: kodeLencana,
                isCheckedEvaluasi: _isCheckedEvaluasi,
                tldHash: dataTld.tld_hash,
                no_seri_tld: dataTld.no_seri_tld,
                htmlDisabled: _htmlDisabled
            }

            htmlTldKontrol += cardKontrolComponent(dataCard, {
                label_tld: true
            });
        }

        $('#tld-kontrol-content').html(htmlTldKontrol);
    });
}

function loadPengguna(tldPengguna, source) {
    let htmlPengguna = '';
    let isPeriodOne = dataPeriodeNow.count_tld == 1;
    ajaxGet(`api/v1/tld/searchTldNotUsed`, { jenis: 'pengguna' }, result => {
        let tldNotUsed = result.data;
        let noTld = 0;
        for (const [i, value] of tldPengguna.entries()) {
            let pengguna = value;
            let fileKtp = pengguna.entitas?.media_ktp ? `${base_url}/storage/${pengguna.entitas.media_ktp.file_path}/${pengguna.entitas.media_ktp.file_hash}` : '';

            let dataTld = null;
            let type = 'baru';
            let id = false;
            let _htmlDisabled = htmlDisabled;
            let _isCheckedEvaluasi = true;

            if (source == 'map') {
                if (value.tld) {
                    dataTld = value.tld;
                    type = 'lama';
                } else {
                    dataTld = tldNotUsed[noTld];
                    noTld++;
                }
                id = value.kontrak_map_hash;
            } else {
                if (isPeriodOne) {
                    if (value.tld_1) {
                        dataTld = value.tld_1;
                        type = 'lama';
                        if (value.status_tld_1 != 2) {
                            _htmlDisabled = true;
                            _isCheckedEvaluasi = false;
                        }
                    } else {
                        dataTld = tldNotUsed[noTld];
                        noTld++;
                    }
                } else {
                    if (value.tld_2) {
                        dataTld = value.tld_2;
                        type = 'lama';
                        if (value.status_tld_2 != 2) {
                            _htmlDisabled = true;
                            _isCheckedEvaluasi = false;
                        }
                    } else {
                        dataTld = tldNotUsed[noTld];
                        noTld++;
                    }
                }

                id = value.kontrak_detail_hash;
            }

            tmpArrTld.push({
                id: id,
                tld: dataTld?.tld_hash,
                index: `tldNoSeri_${i}_pengguna`,
                type,
                source
            });

            const dataCard = {
                index: i,
                idHash: id,
                tldHash: dataTld?.tld_hash,
                name: pengguna.entitas.name,
                divisi: pengguna.entitas.divisi?.name || '',
                isCheckedEvaluasi: _isCheckedEvaluasi,
                radiasi: pengguna.entitas.radiasi?.map(r => r.nama_radiasi),
                fileKtp: fileKtp,
                no_seri_tld: dataTld?.no_seri_tld || '',
                htmlDisabled: _htmlDisabled
            }

            htmlPengguna += cardPenggunaComponent(dataCard, {
                label_tld: true
            })
        }

        $('#pengguna-list-container').html(htmlPengguna);
        showPopupReload();
    });
}

function buatPermohonan(obj) {
    let jenisLayanan = dataJenisLayanan.jenis_layanan_hash;
    let jenisLayananParent = dataJenisLayanan.parent_hash;
    let idKontrak = dataKontrak.kontrak_hash;
    let periode = dataPeriodeNow;
    let alamatIndex = $('#selectAlamat').val();

    let checkTld = [];
    $('input[name="checkTldPengguna"]:checked, input[name="checkTldKontrol"]:checked').each(function () {
        checkTld.push($(this).val());
    });

    if (!alamatIndex) {
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
            params.append('periode', periode?.periode == 0 ? 1 : periode.periode); // If periode is 0, set to 1
            params.append('alamat', alamatData.alamat_hash); // Send the address hash
            params.append('listTld', JSON.stringify(checkTld));
            params.append('source', source);
            params.append('createBy', userActive.user_hash);
            params.append('is_zerocek', 0);
            params.append('haveTld', 1);
            params.append('tipeKontrak', 'kontrak lama');
            if (isPengembalian) {
                params.append('is_pengembalian', 1);
                params.append('pengembalian_start', pengembalianStart);
                params.append('pengembalian_end', pengembalianEnd);
            }
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
                    window.location.href = base_url + "/permohonan/pengajuan";
                });
            }, error => {
                spinner('hide', $(obj));  // Important: hide the spinner on error too!
            });
        } // End of if(result.isConfirmed)
    });

}

function openInventory(obj, jenis) {
    let id = $(obj).data('id');
    inventoryTld.show(id, tmpArrTld, jenis);
}
