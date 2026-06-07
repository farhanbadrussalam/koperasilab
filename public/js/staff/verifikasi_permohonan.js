const listDocumenLHU = [];
let signaturePad = false;
let periodeJs = false;
let periodeNextJs = false;
let jenisLayanan = false;
let checkedTldValues = [];
let listTldKontrol = [];
let uploadDocLhu = false;
let modalDoc = false;
let inventoryTld = false;
let tmpArrTld = [];
let JL = '';
let jmlTldCount = 0;
let isCheckedEvaluasi = false;
let isAdendumZerocek = true;

$(function () {
    // is adendum tidak pake zerocek
    if (dataPermohonan.tipe_kontrak == 'adendum') {
        if (dataPermohonan.is_zerocek == 0) {
            $('#tambah-tandaterima').hide();
            isAdendumZerocek = false;
        }
    }
    inventoryTld = new Inventory_tld({ preview: true });
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

    if (dataPermohonan.tipe_kontrak == 'kontrak lama') {
        $('#total-harga').hide();
    }

    JL = jenislayanan(dataPermohonan.jenis_layanan_parent, dataPermohonan.jenis_layanan);

    const arrPeriode = dataPermohonan.periode_pemakaian;
    jenisLayanan = dataPermohonan.jenis_layanan;

    let txtPeriode = '';
    if (!dataPermohonan.periode_pemakaian) {
        txtPeriode = 'Periode ' + dataPermohonan.periode;
        $('#btn-periode').hide();
    } else {
        if (arrPeriode.length == 1) {
            txtPeriode = `${dateFormat(arrPeriode[0].start_date, 4)} - ${dateFormat(arrPeriode[0].end_date, 4)}`;
            // $('#btn-periode').hide();
        } else {
            txtPeriode = arrPeriode.length + ' Periode';
        }
    }
    $('#periode-pemakaian').html(txtPeriode);

    // periode pemakaian selanjutnya
    let txtPeriodeNext = '';
    if (dataPermohonan.periode_next) {
        let arrPeriodeNext = dataPermohonan.periode_next;
        if (arrPeriodeNext.length == 1) {
            txtPeriodeNext = `${dateFormat(arrPeriodeNext[0].start_date, 4)} - ${dateFormat(arrPeriodeNext[0].end_date, 4)}`;
        } else {
            txtPeriodeNext = arrPeriodeNext.length + ' Periode';
        }

        periodeNextJs = new Periode(arrPeriodeNext, {
            preview: false,
            max: arrPeriodeNext.length,
            id_element: 2
        });

        $('#btn-periode-next').on('click', () => {
            periodeNextJs.show();
        });

        periodeNextJs.on('periode.simpan.2', () => {
            const dataPeriode = periodeNextJs.getData();
            const params = new FormData();
            params.append('idPermohonan', dataPermohonan.permohonan_hash);
            params.append('periodeNext', JSON.stringify(dataPeriode));
            ajaxPost(`api/v1/permohonan/tambahPengajuan`, params, result => {
                Swal.fire({
                    icon: 'success',
                    text: 'Update periode successfully',
                    timer: 1200,
                    timerProgressBar: true,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            });
        });
    }

    $('#periode-pemakaian-next').html(txtPeriodeNext);

    loadTld();
    $('#btn-tandaterima').on('click', () => {
        if (tandaterima) {
            loadPertanyaan();
        }
        $('#modal-tandaterima').modal('show');
    })

    if (arrPeriode) {
        periodeJs = new Periode(arrPeriode, {
            preview: false,
            max: arrPeriode.length,
            id_element: 1
        });

        $('#btn-periode').on('click', () => {
            periodeJs.show();
        });

        periodeJs.on('periode.simpan.1', () => {
            const dataPeriode = periodeJs.getData();
            const params = new FormData();
            params.append('idPermohonan', dataPermohonan.permohonan_hash);
            params.append('periodePemakaian', JSON.stringify(dataPeriode));

            ajaxPost(`api/v1/permohonan/tambahPengajuan`, params, result => {
                Swal.fire({
                    icon: 'success',
                    text: 'Update periode successfully',
                    timer: 1200,
                    timerProgressBar: true,
                    showConfirmButton: false
                });
            });
        });
    }

    $('#btnSelectAllTld').on('click', function () {
        const isChecked = $('#selectAllTld').is(':checked');
        $('#selectAllTld').prop('checked', !isChecked);
        $('input[name="selectTld"]').prop('checked', !isChecked);
    });

    uploadDocLhu = new UploadComponent('uploadDocLHU', {
        camera: false,
        allowedFileExtensions: ['pdf'],
        urlUpload: {
            url: `api/v1/permohonan/uploadLhuZeroCek`,
            urlDestroy: `api/v1/permohonan/destroyLhuZero`,
            idHash: dataPermohonan.permohonan_hash
        },
        multiple: false
    });

    if (dataPermohonan.file_lhu) {
        uploadDocLhu.addData([dataPermohonan.file_lhu]);
    }

    modalDoc = new ModalDocument({
        title: 'Tanda Terima Pengujian',
    });

    $('#btn-show-tandaterima').on('click', () => {
        modalDoc.show(`laporan/tandaterima/${dataPermohonan.permohonan_hash}`);
    });

    $('#btn-delete-tandaterima').on('click', () => {
        ajaxDelete(`api/v1/permohonan/destroyTandaterima/${dataPermohonan.permohonan_hash}`, (result) => {
            Swal.fire({
                icon: 'success',
                text: 'Delete tandaterima successfully',
                timer: 1200,
                timerProgressBar: true,
                showConfirmButton: false
            }).then(() => {
                dataPermohonan.tandaterima = [];
                loadTandaterima();
            })
        });
    });

    $('#checkAllTldPengguna').on('change', function () {
        const isChecked = $(this).is(':checked');
        $('input[name="checkTldPengguna"]').prop('checked', isChecked);
    });


    if (dataPermohonan.is_have_tld) {
        isCheckedEvaluasi = true;
    }
    // isCheckedEvaluasi = dataPermohonan.is_have_tld || dataPermohonan.is_zerocek == 0;

    signaturePad = new SignatureSelect(document.getElementById('validasi-frontdesk'), {
        inputId: 'frontdeskVal',
        label: 'Nyatakan valid & Lengkap',
        placeholder: 'Menunggu validasi petugas...',
        signerUser: userActive
    });

    $('#tanggal-selesai').flatpickr({
        altInput: true,
        locale: "id",
        minDate: new Date(),
        dateFormat: "Y-m-d",
        altFormat: "j F Y",
    });

    loadPelanggan();
    loadTandaterima();

    // Heartbeat ping to keep lock alive
    setInterval(() => {
        let formData = new FormData();
        formData.append('idPermohonan', dataPermohonan.permohonan_hash);
        ajaxPost(`api/v1/permohonan/verifikasi/ping`, formData, result => {
            // lock updated silently
        }, error => {
            console.error('Ping failed');
        }, { onErrorPopup: false });
    }, 60000); // 1 minute

    // Unlock when leaving the page or canceling
    window.addEventListener('beforeunload', function (e) {
        let formData = new FormData();
        formData.append('idPermohonan', dataPermohonan.permohonan_hash);
        formData.append('_token', csrf);

        ajaxPost(`api/v1/permohonan/verifikasi/unlock`, formData, result => {
            // lock updated silently
        }, error => {
            console.error('Unlock failed');
        }, { onErrorPopup: false });
    });

    if(dataPermohonan.is_have_tld == 1){
        $('#checkAllTldPengguna').on('change', function () {
            const isChecked = $(this).is(':checked');
            $('input[name="checkTldPengguna"]').prop('checked', isChecked);
        });
    
        $('#checkAllTldKontrol').on('change', function () {
            const isChecked = $(this).is(':checked');
            $('input[name="checkTldKontrol"]').prop('checked', isChecked);
        });
    }
});

function loadPelanggan() {
    const pelanggan = dataPermohonan.pelanggan;
    const perusahaan = pelanggan.perusahaan;

    $('#nama-instansi').html(perusahaan.nama_perusahaan);
    $('#nama-pic').html(pelanggan.name ?? '-');
    $('#jabatan-pic').html(pelanggan.jabatan ?? '-');
    $('#email-pic').html(pelanggan.email ?? '-');
    $('#telepon-pic').html(pelanggan.profile.no_hp ? maskReload(pelanggan.profile.no_hp, 'telepon') : '-');
    $('#npwp-pic').html(perusahaan.npwp_perusahaan ? maskReload(perusahaan.npwp_perusahaan, 'npwp') : '-');
    $('#kodeInstansi').html(perusahaan.kode_perusahaan ?? '-');
    $('#email-perusahaan').html(perusahaan.email ?? '-');

    if (perusahaan.kode_perusahaan) {
        $('#status-instansi').html('Terverifikasi');
        $('#status-instansi').removeClass('text-danger bg-danger-subtle border-danger-subtle');
        $('#status-instansi').addClass('text-success bg-success-subtle border-success-subtle');
    } else {
        $('#status-instansi').html('Belum terverifikasi');
        $('#status-instansi').removeClass('text-success bg-success-subtle border-success-subtle');
        $('#status-instansi').addClass('text-danger bg-danger-subtle border-danger-subtle');
    }

    // Alamat
    let alamatUtama = false;
    let kodeposUtama = false;
    for (const value of perusahaan.alamat) {
        let valAlamat = value.alamat;
        let valKodepos = value.kode_pos;

        if (value.jenis == 'Utama') {
            alamatUtama = value.alamat;
            kodeposUtama = value.kode_pos;
        } else {
            if (value.status) {
                valAlamat = value.alamat;
                valKodepos = value.kode_pos;
            } else {
                valAlamat = '';
                valKodepos = '';
            }
        }

        if (valAlamat == '') {
            $(`#alamat-${value.jenis}`).addClass('fst-italic');
            $(`#alamat-${value.jenis}`).html("Sama dengan alamat utama");
        } else {
            $(`#alamat-${value.jenis}`).removeClass('fst-italic');
            $(`#alamat-${value.jenis}`).html(valAlamat + "," + valKodepos);
        }

    }
}

function loadPertanyaan() {
    let html = '';
    $('#content-pertanyaan').html('');
    for (const [i, value] of tandaterima.entries()) {
        let htmlAnswer = ``;
        let btnSelectTld = ``;
        let htmlMandatory = value.mandatory ? '<span class="text-danger ml-2">*</span>' : '';

        if (value.type == 1) {
            let jenisTld = '';
            let readonly = '';
            if (value.pertanyaan == 'TLD') {
                jenisTld = dataPermohonan.jenis_tld?.name ?? '';
                readonly = ' readonly';
            }
            htmlAnswer = `<textarea name="answer_${i}" id="answer_${i}" cols="30" rows="3" class="form-control" ${readonly}>${jenisTld}</textarea>`;
        } else if (value.type == 2) {
            htmlAnswer = `
                <div class="my-3">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="answer_${i}" id="answer_${i}_baik" value="baik" onclick="toggleReason(${i}, false)">
                        <label class="form-check-label" for="answer_${i}_baik">Baik</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="answer_${i}" id="answer_${i}_cacat" value="cacat" onclick="toggleReason(${i}, true)">
                        <label class="form-check-label" for="answer_${i}_cacat">Cacat</label>
                    </div>
                    <div>
                        <input type="text" class="form-control w-100" id="reason_${i}" placeholder="Bila cacat, sebutkan : ....." disabled>
                    </div>
                </div>
            `;
        } else if (value.type == 3) {
            htmlAnswer = `<textarea name="answer_${i}" id="answer_${i}" cols="30" rows="3" class="form-control" readonly></textarea>`;
            btnSelectTld = `<button class="btn btn-outline-primary btn-sm" type="button" onclick="selectTLDPermohonan(${i})">Pilih TLD</button>`;
        }

        html += `
            <div class="col-sm-6 mt-2">
                <label for="" class="mb-2">${value.pertanyaan + htmlMandatory} : ${btnSelectTld}</label>
                ${htmlAnswer}
            </div>
        `;
    }

    $('#content-pertanyaan').html(html);
}

function loadTandaterima() {
    const data = dataPermohonan.tandaterima;
    if (data && data.length > 0) {
        $('#status_tandaterima').val('true');
        $('#tambah-tandaterima').addClass('d-none');
        $('#show-tandaterima').removeClass('d-none');
    } else {
        $('#status_tandaterima').val('false');
        $('#tambah-tandaterima').removeClass('d-none');
        $('#show-tandaterima').addClass('d-none');
    }
}

function toggleReason(index, enable) {
    $(`#reason_${index}`).prop('disabled', !enable);
}

function loadTld() {
    loadPengguna();
    loadTldKontrol();
}

function loadTldKontrol() {
    ajaxGet(`api/v1/permohonan/listKontrol`, { idPermohonan: dataPermohonan.permohonan_hash }, result => {
        let html = '';
        let htmlDisabled = false;

        if (dataPermohonan.is_have_tld) {
            htmlDisabled = true;
        }
        let index = 0;
        for (const key in result.data.tldPermohonan) {
            if (!Object.hasOwn(result.data.tldPermohonan, key)) continue;
            const kontrol = result.data.tldPermohonan[key];

            for (const [i, item] of kontrol.entries()) {
                let idHash = item.permohonan_detail_hash ? item.permohonan_detail_hash : item.kontrak_detail_hash;
                let tldHash = item.tld ? item.tld.tld_hash : (item.tld_pengguna?.tld_hash || '');
                let no_seri_tld = item.tld ? item.tld.no_seri_tld : (item.tld_pengguna?.no_seri_tld || '');
                tmpArrTld.push({
                    id: idHash,
                    tld: tldHash,
                    index: `tldNoSeri_${index}_kontrol`
                });

                let kodeLencana = i == 0 ? 'C' : `C${i}`;

                let data = {
                    name: `Kontrol ${item.entitas?.name ?? ''} ${kodeLencana}`,
                    kode: kodeLencana,
                    isCheckedEvaluasi: isCheckedEvaluasi,
                    index: index,
                    tldHash: tldHash,
                    no_seri_tld: no_seri_tld,
                    htmlDisabled: htmlDisabled
                };

                html += cardKontrolComponent(data, {
                    is_btn_remove: false,
                    label_tld: true,
                    add_kontrol: false
                });

                jmlTldCount++;
                index++;
            }
        }
        $('#jumlah-info-kontrol').html(index);

        if (result.data.tldPermohonan.length == 0) {
            html += `
                <div class="col-sm-12 text-center my-3">
                    <label for="">Tidak ada TLD Kontrol</label>
                </div>
            `;
        }
        $('#tld-kontrol-content').html(html);
    });
}
function loadPengguna() {
    let params = {
        idPermohonan: dataPermohonan.permohonan_hash
    }
    $('#pengguna-placeholder').removeClass('d-none');
    $('#pengguna-table').addClass('d-none');

    ajaxGet(`api/v1/permohonan/listPengguna`, params, result => {
        let html = '';
        let htmlDisabled = false;

        if (dataPermohonan.is_have_tld || dataPermohonan.tipe_kontrak == 'adendum') {
            htmlDisabled = true;
        }

        $('#jumlah-pengguna').html(result.data.length + ' Orang')
        $('#jumlah-info-pengguna').html(result.data.length)

        for (const [i, value] of result.data.entries()) {
            const pengguna = value.entitas;
            // TLD PENGGUNA
            let idHash = value.permohonan_detail_hash ? value.permohonan_detail_hash : value.kontrak_detail_hash;
            let tldHash = value.tld ? value.tld.tld_hash : (value.tld_pengguna?.tld_hash || '');
            let no_seri_tld = value.tld ? value.tld.no_seri_tld : (value.tld_pengguna?.no_seri_tld || '');

            if (!value.tld) htmlDisabled = false;

            tmpArrTld.push({
                id: idHash,
                tld: tldHash,
                index: `tldNoSeri_${i}_pengguna`
            });

            let fileKtp = pengguna.media_ktp ? `${base_url}/storage/${pengguna.media_ktp.file_path}/${pengguna.media_ktp.file_hash}` : '';

            let data = {
                index: i,
                idHash: idHash,
                isCheckedEvaluasi: isCheckedEvaluasi,
                name: pengguna.name,
                divisi: pengguna.divisi?.name || '',
                radiasi: pengguna.radiasi?.map(r => r.nama_radiasi),
                no_seri_tld: no_seri_tld,
                htmlDisabled: htmlDisabled,
                fileKtp: fileKtp
            }

            if (value.type == 'ganti') {
                data['name'] = value.pengguna_lama?.name;
                data['pengguna_baru'] = {
                    name: pengguna.name,
                }
            }

            html += cardPenggunaComponent(data, {
                label_tld: true,
                status: value.type,
                is_adendum: false
            });

            jmlTldCount++;
        }

        if (result.data.length == 0) {
            html += `
                <div class="col-sm-12 text-center my-3">
                    <label for="">Tidak ada Pengguna</label>
                </div>
            `;
        }

        $('#pengguna-list-container').html(html);
        showPopupReload();
    })

}

function verif_kelengkapan(status, obj) {
    if (status == 'lengkap') {
        let [ttdValue, ttdBy] = signaturePad.getValue();
        if (dataPermohonan.tandaterima.length == 0 && isAdendumZerocek) {
            return Swal.fire({
                icon: "warning",
                text: "Harap tambah tandaterima terlebih dahulu.",
            });
        }

        if (!ttdValue) {
            return Swal.fire({
                icon: "warning",
                text: "Harap berikan tanda tangan terlebih dahulu.",
            });
        }

        if (isCheckedEvaluasi) {
            let checkTld = [];
            $('input[name="checkTldPengguna"]:checked, input[name="checkTldKontrol"]:checked').each(function () {
                checkTld.push($(this).val());
            });

            if (checkTld.length < jmlTldCount) {
                return Swal.fire({
                    icon: "warning",
                    text: "Data Pengguna dan Kontrol belum lengkap.",
                });
            }
        }

        if ($('#tanggal-selesai').val() == '') {
            return Swal.fire({
                icon: "warning",
                text: "Harap pilih tanggal selesai terlebih dahulu.",
            });
        }

        Swal.fire({
            icon: 'warning',
            title: 'Apakah data sudah lengkap?',
            showCancelButton: true,
            confirmButtonText: 'Iya',
            cancelButtonText: 'Tidak',
            customClass: {
                confirmButton: 'btn btn-outline-success mx-1',
                cancelButton: 'btn btn-outline-danger mx-1'
            },
            buttonsStyling: false,
            reverseButtons: true
        }).then(result => {
            if (result.isConfirmed) {
                let formData = new FormData();
                formData.append('ttd', ttdValue);
                formData.append('ttd_by', ttdBy);
                formData.append('status', status);
                formData.append('idPermohonan', dataPermohonan.permohonan_hash);
                formData.append('tanggal_selesai', $('#tanggal-selesai').val());
                formData.append('listTld', JSON.stringify(tmpArrTld));

                spinner('show', obj);
                if (dataPermohonan.tipe_kontrak == 'adendum') {
                    ajaxPost(`api/v1/permohonan/verifikasi/adendum`, formData, result => {
                        Swal.fire({
                            icon: 'success',
                            text: 'Permohonan terverifikasi',
                            timer: 1200,
                            timerProgressBar: true,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = base_url + "/staff/permohonan";
                        });
                    }, error => {
                        spinner('hide', obj);
                    });
                } else {
                    ajaxPost(`api/v1/permohonan/verifikasi/cek`, formData, result => {
                        Swal.fire({
                            icon: 'success',
                            text: 'Permohonan terverifikasi',
                            timer: 1200,
                            timerProgressBar: true,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = base_url + "/staff/permohonan";
                        });
                    }, error => {
                        spinner('hide', obj);
                    });
                }
            }
        })
    } else if (status == 'tidak_lengkap') {
        $('#modal-verif-invalid').modal('show');
    }
}

function createInvoice(idPermohonan) {
    const formData = new FormData();
    formData.append('idPermohonan', idPermohonan);
    formData.append('status', 1);
    ajaxPost(`api/v1/keuangan/action`, formData, result => { })
}

function createPenyelia(idPermohonan) {
    const formData = new FormData();
    formData.append('idPermohonan', idPermohonan);
    formData.append('status', 1);
    ajaxPost(`api/v1/penyelia/action`, formData, result => {

    })
}

function simpanTandaTerimaPermohonan(obj) {
    // Get all form elements within #content-pertanyaan
    const answerTandaterima = [];
    if (tandaterima) {
        for (const [i, value] of tandaterima.entries()) {
            let elementAnswer = false;
            if (value.type == 1 || value.type == 3) {
                elementAnswer = $(`#answer_${i}`).val();
                answerTandaterima.push({
                    id: value.pertanyaan_hash,
                    answer: elementAnswer,
                    note: ''
                });
            } else if (value.type == 2) {
                elementAnswer = $(`[name="answer_${i}"]:checked`).val();
                let note = '';
                if (elementAnswer == 'cacat') {
                    note = $(`#reason_${i}`).val();
                }
                answerTandaterima.push({
                    id: value.pertanyaan_hash,
                    answer: elementAnswer,
                    note: note
                });
            }

            if (value.mandatory && elementAnswer == '') {
                return Swal.fire({
                    icon: "warning",
                    text: `Harap lengkapi pertanyaan yang wajib diisi.`
                });
            }
        }

        let formData = new FormData();
        formData.append('tandaterima', JSON.stringify(answerTandaterima));
        formData.append('idPermohonan', dataPermohonan.permohonan_hash);
        spinner('show', $(obj));
        ajaxPost(`api/v1/permohonan/verifikasi/tambahTandaterima`, formData, result => {
            spinner('hide', $(obj));
            if (result.meta.code == 200) {
                Swal.fire({
                    icon: "success",
                    text: result.data.msg,
                }).then(() => {
                    dataPermohonan.tandaterima = result.data.information;
                    $('#status_tandaterima').val('true');
                    $('#modal-tandaterima').modal('hide');

                    loadTandaterima();
                });
            } else {
                Swal.fire({
                    icon: "error",
                    text: result.data.msg,
                });
            }
            spinner('hide', $(obj));
        }, error => {
            spinner('hide', $(obj));
        });
    }
}

function return_permohonan(obj) {
    let note = $('#txt_note').val();
    spinner('show', obj);

    let formData = new FormData();
    formData.append('_token', csrf);
    formData.append('status', 'tidak_lengkap');
    formData.append('note', note);
    formData.append('idPermohonan', dataPermohonan.permohonan_hash);
    ajaxPost(`api/v1/permohonan/verifikasi/cek`, formData, result => {
        Swal.fire({
            icon: 'success',
            text: 'Permohonan dikembalikan',
            timer: 1200,
            timerProgressBar: true,
            showConfirmButton: false
        }).then(() => {
            window.location.href = base_url + "/staff/permohonan";
        });
    })
}

function selectTLDPermohonan(index) {
    let jsonTld = [];
    if (dataPermohonan.list_tld) {
        jsonTld = dataPermohonan.list_tld;
    } else {
        let jumTld = dataPermohonan.jumlah_pengguna + dataPermohonan.jumlah_kontrol;
        for (let i = 0; i < jumTld; i++) {
            jsonTld.push('TLD ' + (i + 1));
        }
    }

    if (checkedTldValues.length != 0) {
        jsonTld = checkedTldValues;
    }

    let htmlList = '';
    for (const [i, tld] of jsonTld.entries()) {
        htmlList += `
            <li class="list-group-item">
                <input class="form-check-input me-1" type="checkbox" value="${tld}" data-index="${i}" name="selectTld" checked>
                <label class="form-check-label">
                    <input type="text" class="form-control form-control-sm" name="listTld[]" id="tld_${i}" placeholder="${tld}" value="${tld}" autocomplete="off">
                </label>
            </li>
        `;
    }
    $('#btnPilihTld').data('index', index);
    $('#listTldSelect').html(htmlList);
    $('#modal-select-tld').modal('show');
}

function simpanTldPermohonan(obj) {
    checkedTldValues = [];
    $('input[name="selectTld"]:checked').each(function () {
        let indexTld = $(this).data('index');
        let value = $(`#tld_${indexTld}`).val();
        checkedTldValues.push(value);
    });

    let index = $(obj).data('index');

    // tambahkan ke textarea answer
    $(`#answer_${index}`).html(checkedTldValues.map(tld => tld).join(', '));

    $('#listTldSelect').html('');
    $('#modal-select-tld').modal('hide');
}

function areThereEmptyFields(formElements) {
    let isEmpty = false; // Assume no empty fields initially

    // Iterate through each form element
    formElements.each(function () {
        const element = $(this); // Get the jQuery object for the element

        // Check for empty values based on element type
        if (element.is('input[type="text"], input[type="email"], input[type="number"], textarea') && element.val().trim() === "") {
            isEmpty = true; // Found an empty field
            return false; // Exit the .each() loop early
        } else if (element.is('input[type="radio"], input[type="checkbox"]') && !element.is(':checked')) {
            // Check if at least one radio button in a group is selected
            const name = element.attr('name');
            if ($(`input[name="${name}"]:checked`).length === 0) {
                isEmpty = true;
                return false;
            }
        } else if (element.is('select') && element.val() === null) {
            isEmpty = true;
            return false;
        }
    });

    return isEmpty;
}

function templateTld(state) {
    if (!state.id) {
        return state.text;
    }

    let content = $(`
        <div class="d-flex justify-content-between">
            <div>${state.text}</div>
            <div>${state.status == 1 ? '<span class="badge rounded-pill text-bg-success">Digunakan</span>' : ''}</div>
        </div>
    `);
    return content;
}

function openInventory(obj, jenis) {
    let id = $(obj).data('id');
    inventoryTld.show(id, tmpArrTld, jenis);
}
