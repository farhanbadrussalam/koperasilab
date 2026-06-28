const arrSelectDocument = [];
const arrDocCustom = [];
let inventoryTld = false;
let mPeriode = false;
const tmpArrTld = [];
const modalDoc = new ModalDocument();
let isLabReady = true;

let dataOrderPengiriman = {};
if (informasi.sumber == 'permohonan') {
    const periode_now = informasi.kontrak.periode.find(k => k.periode == informasi.periode);
    dataOrderPengiriman = {
        sumber: 'permohonan',
        no_kontrak: informasi.kontrak.no_kontrak,
        pelanggan: informasi.pelanggan,
        detail: informasi.permohonan_detail,
        jenis_layanan: informasi.jenis_layanan,
        jenis_layanan_parent: informasi.jenis_layanan_parent,
        tipe_kontrak: informasi.tipe_kontrak,
        jumlah_pengguna: informasi.jumlah_pengguna,
        jumlah_kontrol: informasi.jumlah_kontrol,
        pengiriman: informasi.kontrak.pengiriman,
        id_hash: informasi.permohonan_hash,
        kontrak_hash: informasi.kontrak.kontrak_hash,
        created_at: informasi.created_at,
        invoice: informasi.invoice,
        lhu: informasi.lhu,
        lhu_two_periods_ago: informasi.lhu_two_periods_ago,
        is_zerocek: informasi.is_zerocek,
        is_have_tld: informasi.is_have_tld,
        is_periode_berjalan: informasi.is_periode_berjalan,
        file_lhu: informasi.file_lhu,
        dokumen: informasi.dokumen,
        periode_now: periode_now,
        periode_aktif: informasi.kontrak?.periode_active,
        adendum: false,
        periode_all: informasi.kontrak.periode_all,
        periode_next: informasi.kontrak.periode_next,
        periode: informasi.periode,
        alamat: informasi.alamat,
        kontrak_detail: informasi.kontrak.kontrak_detail
    }
} else {
    dataOrderPengiriman = {
        sumber: 'kontrak',
        no_kontrak: informasi.no_kontrak,
        pelanggan: informasi.pelanggan,
        detail: informasi.kontrak_detail,
        jenis_layanan: informasi.jenis_layanan,
        jenis_layanan_parent: informasi.jenis_layanan_parent,
        tipe_kontrak: informasi.tipe_kontrak,
        jumlah_pengguna: informasi.jumlah_pengguna,
        jumlah_kontrol: informasi.jumlah_kontrol,
        pengiriman: informasi.pengiriman,
        id_hash: false,
        kontrak_hash: informasi.kontrak_hash,
        created_at: informasi.created_at,
        invoice: false,
        lhu: false,
        lhu_two_periods_ago: informasi.lhu_two_periods_ago,
        is_zerocek: informasi.is_zerocek,
        is_have_tld: informasi.is_have_tld,
        is_periode_berjalan: false,
        file_lhu: false,
        dokumen: informasi.periode[0].permohonan?.dokumen ?? informasi.dokumen?.filter(d => d.periode == informasi.periode[0].periode),
        periode_now: informasi.periode[0],
        periode_aktif: informasi.periode_aktif,
        adendum: informasi.adendum,
        periode_all: informasi.periode_all,
        periode_next: informasi.periode_next,
        periode: false,
        alamat: false,
        kontrak_detail: informasi.kontrak_detail
    }
}

$(function () {
    inventoryTld = new Inventory_tld({
        preview: true,
        no_kontrak: dataOrderPengiriman.no_kontrak
    });

    inventoryTld.on('inventory.selected', (e) => {
        const detail = e.detail;

        $(`#tldNoSeri_${$.escapeSelector(detail.selected)}`).val(detail.data_tld.no_seri_tld);
        $(`#tldNoSeri_${$.escapeSelector(detail.selected)}_view`).html(detail.data_tld.no_seri_tld);

        // reset tmpArrTld
        let index = tmpArrTld.findIndex(d => d.id == detail.selected);

        if (index > -1) {
            tmpArrTld[index].tld = detail.data_tld.tld_hash;
        }
    })

    // Fetch unused TLDs automatically from master_tld if not assigned
    ajaxGet('api/v1/tld/searchTldNotUsed', { jenis: 'kontrol' }, resKontrol => {
        ajaxGet('api/v1/tld/searchTldNotUsed', { jenis: 'pengguna' }, resPengguna => {
            const unusedKontrol = resKontrol.data || [];
            const unusedPengguna = resPengguna.data || [];
            load_form(unusedKontrol, unusedPengguna);
        });
    });

    $('#select_alamat').on('change', obj => {
        const perusahaan = dataOrderPengiriman.pelanggan.perusahaan;

        if (perusahaan.alamat[obj.target.value].alamat) {
            $('#alamatTujuan').val(perusahaan.alamat[obj.target.value].alamat + ", " + perusahaan.alamat[obj.target.value].kode_pos);
        } else {
            const alamatUtama = perusahaan.alamat.find(a => a.jenis == 'utama');
            $('#alamatTujuan').val(alamatUtama.alamat + ", " + alamatUtama.kode_pos);
        }
    });

})

function openInventory(obj, jenis) {
    let id = $(obj).data('id');
    inventoryTld.show(id, tmpArrTld, jenis);
}

function load_form(unusedKontrol = [], unusedPengguna = []) {
    // Inisialisasi Alamat
    let htmlAlamat = '<option value="">Pilih alamat</option>';

    for (const [i, value] of dataOrderPengiriman.pelanggan.perusahaan.alamat.entries()) {
        if (value.status) {
            let checked = '';
            if (value.alamat_hash == dataOrderPengiriman.alamat?.alamat_hash) {
                checked = 'selected';
            }
            htmlAlamat += `<option value='${i}' ${checked}>Alamat ${value.jenis}</option>`;
        }
    }
    $('#select_alamat').html(htmlAlamat);

    if (dataOrderPengiriman.alamat) {
        $('#alamatTujuan').val(dataOrderPengiriman.alamat.alamat + ", " + dataOrderPengiriman.alamat.kode_pos);
    }

    $('#list-document').empty();

    let tldPengguna = dataOrderPengiriman.detail.filter(p => p.jenis == 'pengguna');
    let tldKontrol = dataOrderPengiriman.detail.filter(p => p.jenis == 'kontrol');

    // jika ada dendum yang baru akan di masukkan ke tldPengguna atau tldKontrol sesuai dengan jenisnya
    if (dataOrderPengiriman.adendum) {
        let adendumPengguna = dataOrderPengiriman.adendum.filter(p => p.jenis == 'pengguna');
        let adendumKontrol = dataOrderPengiriman.adendum.filter(p => p.jenis == 'kontrol');

        // di adendum ini ada type baru dan ganti type lama jadi type baru, untuk type baru akan langsung di masukkan ke list tld pengguna atau kontrol, sedangkan untuk type ganti akan mengganti data yang lama dengan yang baru berdasarkan column pengguna_lama
        for (const [i, value] of adendumPengguna.entries()) {
            if (value.type == 'ganti') {
                let index = tldPengguna.findIndex(p => p.id_pengguna_divisi == value.pengguna_lama);
                if (index > -1) {
                    tldPengguna[index] = value;
                }
            } else {
                tldPengguna.push(value);
            }
        }
        tldKontrol = tldKontrol.concat(adendumKontrol);
    }

    let periodeAwal = getPeriodeAwal(dataOrderPengiriman);
    let JL = jenislayanan(dataOrderPengiriman.jenis_layanan_parent, dataOrderPengiriman.jenis_layanan);

    // list document TLD
    // Mengecek apakah sudah last periode atau belum
    let htmlDisabled = false;
    let periodeTld = dataOrderPengiriman.periode_now.periode === 0 ? 1 : dataOrderPengiriman.periode_now.periode;
    // di ganti sebelumnya saya mengambil kontrak.periode bukan kontrak.periode_all
    // let isLastPeriode = periodeTld >= dataOrderPengiriman.periode_all.jml_periode;

    const isAdendum = dataOrderPengiriman.tipe_kontrak == 'adendum';
    const isAdendumZeroCek = isAdendum && dataOrderPengiriman.is_zerocek;
    if ((!periodeAwal.includes(periodeTld) && dataOrderPengiriman.tipe_kontrak != 'adendum') || isAdendum) {
        let checkStatusPengiriman = null;
        if (dataOrderPengiriman.tipe_kontrak == 'adendum') {
            checkStatusPengiriman = dataOrderPengiriman.pengiriman.find(d =>
                d.permohonan_hash === dataOrderPengiriman.id_hash &&
                d.detail.find(c => c.jenis == 'tld')
            );
        } else {
            checkStatusPengiriman = dataOrderPengiriman.pengiriman.find(d =>
                d.detail.find(c => c.jenis == 'tld' && c.periode == periodeTld) &&
                !d.permohonan_hash
            );
        }

        let isSurpengReady = false;
        if (dataOrderPengiriman.dokumen) {
            let surpengDoc = dataOrderPengiriman.dokumen.find(d => d.jenis === 'surpeng');
            if (surpengDoc && surpengDoc.ttd) {
                isSurpengReady = true;
            }
        }

        if (dataOrderPengiriman.tipe_kontrak !== 'adendum') {
            let checkLhu = dataOrderPengiriman.lhu_two_periods_ago;
            if (checkLhu) {
                isLabReady = false;
                if (checkLhu.status == 3 || checkLhu.status == 5) {
                    isLabReady = true;
                } else if (checkLhu.penyelia_map && checkLhu.penyelia_map.length > 0) {
                    let pelabelanJob = checkLhu.penyelia_map.find(j => j.jobs && j.jobs.name === 'Pelabelan TLD');
                    if (!pelabelanJob) {
                        pelabelanJob = checkLhu.penyelia_map.find(j => j.jobs && j.jobs.name === 'Pembuatan Label');
                    }
                    
                    if (pelabelanJob && pelabelanJob.status == 2) {
                        isLabReady = true;
                    }
                }
            } else {
                let currentPeriod = dataOrderPengiriman.periode_now?.periode || dataOrderPengiriman.periode;
                if (currentPeriod >= 3 || (currentPeriod == 2 && dataOrderPengiriman.is_zerocek == 1)) {
                    isLabReady = false;
                }
            }
        } else {
            let currentLhu = dataOrderPengiriman.lhu;
            if (dataOrderPengiriman.sumber === 'kontrak') {
                currentLhu = dataOrderPengiriman.periode_now?.permohonan?.lhu;
            }
            if (currentLhu) {
                isLabReady = false;
                if (currentLhu.status == 3 || currentLhu.status == 5) {
                    isLabReady = true;
                } else if (currentLhu.penyelia_map && currentLhu.penyelia_map.length > 0) {
                    let pelabelanJob = currentLhu.penyelia_map.find(j => j.jobs && j.jobs.name === 'Pelabelan TLD');
                    if (!pelabelanJob) {
                        pelabelanJob = currentLhu.penyelia_map.find(j => j.jobs && j.jobs.name === 'Pembuatan Label');
                    }
                    
                    if (pelabelanJob && pelabelanJob.status == 2) {
                        isLabReady = true;
                    }
                }
            } else {
                if (dataOrderPengiriman.is_zerocek == 1) {
                    isLabReady = false;
                }
            }
        }

        let checkedTld = checkStatusPengiriman ? 'disabled' : (!isLabReady ? 'disabled' : (isSurpengReady ? 'checked' : (dataOrderPengiriman.periode_now.permohonan == null ? '' : 'disabled')));

        let htmlKontrol = ``;
        let unusedKontrolIndex = 0;
        for (const [i, list] of tldKontrol.entries()) {
            let tldActive = false;
            if (dataOrderPengiriman.sumber == 'permohonan') {
                tldActive = list.tld;
            } else {
                let isPeriodOne = dataOrderPengiriman.periode_now.count_tld == 1;
                tldActive = isPeriodOne ? list.tld_1 : list.tld_2;
            }

            // Fallback automatically to master_tld if not assigned
            if (!tldActive && unusedKontrol && unusedKontrol.length > unusedKontrolIndex) {
                tldActive = unusedKontrol[unusedKontrolIndex];
                unusedKontrolIndex++;
            }

            let id = dataOrderPengiriman.sumber == 'permohonan' ? list.permohonan_detail_hash : list.kontrak_detail_hash;

            // Cek apakah TLD ini merupakan adendum (kontrak_detail.status == 2)
            const isAdendumKontrol = dataOrderPengiriman.sumber !== 'permohonan' && list.status == 2;

            tmpArrTld.push({
                id: `${id}`,
                tld: tldActive ? (tldActive.tld_hash || tldActive.id_tld) : null,
                isAdendum: isAdendumKontrol
            });
            if (!tldActive) {
                htmlDisabled = false;
            } else {
                if (dataOrderPengiriman.tipe_kontrak == 'kontrak lama' || (tmpArrEvaluasi.includes(JL) && dataOrderPengiriman.is_have_tld == 1)) {
                    htmlDisabled = true;
                }
            }
            htmlKontrol += `
                <div class="bg-white border rounded px-2 py-1 d-flex align-items-center shadow-sm">
                    <small class="text-muted me-2">${dataOrderPengiriman.pelanggan.perusahaan.kode_perusahaan}-${i > 1 ? `C${i + 1}` : 'C'}:</small>
                    <span class="fw-bold me-2" id="tldNoSeri_${id}_view">${tldActive ? tldActive.no_seri_tld : 'Tidak ada'}</span>
                    ${isAdendumKontrol ? `<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle ms-1" style="font-size:0.65rem;">Adendum</span>` : ''}
                    ${!htmlDisabled ? `<button class="btn btn-sm btn-link p-0 text-info ms-auto" data-id="${id}" onclick="openInventory(this, 'kontrol')"><i class="bi bi-arrow-repeat"></i></button>` : ``}
                    <input type="hidden" class="form-control rounded-start form-sm" name="kodeTldKontrol" value="${tldActive ? tldActive.no_seri_tld : ''}" data-id="${id}" id="tldNoSeri_${id}" placeholder="Pilih No Seri" readonly>
                </div>
            `;
        }

        // Mengambil tld Pengguna dari kontrak
        let htmlPengguna = ``;
        let unusedPenggunaIndex = 0;
        for (const list of tldPengguna) {
            let tldActive = false;
            if (dataOrderPengiriman.sumber == 'permohonan') {
                tldActive = list.tld;
            } else {
                let isPeriodOne = dataOrderPengiriman.periode_now.count_tld == 1;
                tldActive = isPeriodOne ? list.tld_1 : list.tld_2;
            }

            // Fallback automatically to master_tld if not assigned
            if (!tldActive && unusedPengguna && unusedPengguna.length > unusedPenggunaIndex) {
                tldActive = unusedPengguna[unusedPenggunaIndex];
                unusedPenggunaIndex++;
            }

            let id = dataOrderPengiriman.sumber == 'permohonan' ? list.permohonan_detail_hash : list.kontrak_detail_hash;

            // Cek apakah TLD ini merupakan adendum (kontrak_detail.status == 2)
            const isAdendumPengguna = dataOrderPengiriman.sumber !== 'permohonan' && list.status == 2;

            tmpArrTld.push({
                id: id,
                tld: tldActive ? (tldActive.tld_hash || tldActive.id_tld) : null,
                isAdendum: isAdendumPengguna,
                permohonanHash: isAdendumPengguna ? (list.permohonan_adendum_hash ?? null) : null
            })
            if (!tldActive) {
                htmlDisabled = false;
            } else {
                if (dataOrderPengiriman.tipe_kontrak == 'kontrak lama' || (tmpArrEvaluasi.includes(JL) && dataOrderPengiriman.is_have_tld == 1)) {
                    htmlDisabled = true;
                }
            }

            if (dataOrderPengiriman.is_have_tld == 1) {
                htmlDisabled = true;
            }

            htmlPengguna += `
                <div class="bg-white border rounded px-2 py-1 d-flex align-items-center shadow-sm">
                    <input type="hidden" class="form-control rounded-start form-sm" value="${tldActive ? tldActive.no_seri_tld : ''}" data-id="${id}" id="tldNoSeri_${id}" readonly>
                    <small class="text-muted me-2">${dataOrderPengiriman.pelanggan.perusahaan.kode_perusahaan}-${list.entitas.kode_lencana}:</small>
                    <span class="fw-bold me-2" id="tldNoSeri_${id}_view">${tldActive ? tldActive.no_seri_tld : 'Tidak Ada'}</span>
                    ${isAdendumPengguna ? `<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle ms-1" style="font-size:0.65rem;">Adendum</span>` : ''}
                    ${!htmlDisabled ? `<button class="btn btn-sm btn-link p-0 text-info" data-id="${id}" onclick="openInventory(this, 'pengguna')"><i class="bi bi-arrow-repeat"></i></button>` : ``}
                </div>
            `;
        }

        htmlTld = `
            <div class="card border border-primary-subtle rounded-3">
                <div class="card-body p-3 pb-0">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check">
                            <div>
                                <input class="form-check-input" type="checkbox" id="selectDocumentTld"
                                    data-jenis="tld" name="selectDocument" data-id="${dataOrderPengiriman.id_hash ?? ''}"
                                        onclick="updateSelectDocument()" ${checkedTld}>
                                <label class="form-check-label fw-bold" for="checkTLD">TLD Periode ${dataOrderPengiriman.periode_now.status === 2 ? 'Pengembalian' : periodeTld}</label>
                                <span class="badge bg-light text-muted border ms-2">${tldPengguna.length} Pengguna + ${tldKontrol.length} Kontrol</span>
                            </div>
                            <div>
                                <small><i class="bi bi-calendar-fill"></i> ${dataOrderPengiriman.periode_now && dataOrderPengiriman.periode_now.start_date ? `${dateFormat(dataOrderPengiriman.periode_now.start_date, 5)} - ${dateFormat(dataOrderPengiriman.periode_now.end_date, 5)}` : dateFormat(dataOrderPengiriman.created_at, 5)}</small>
                                <small>${statusFormat('pengiriman', checkedTld == 'disabled' ? checkStatusPengiriman?.status : false)}</small>
                                ${!isLabReady && !checkStatusPengiriman ? `<small class="text-danger d-block mt-1" style="font-size: 0.7rem;"><i class="bi bi-info-circle"></i> Menunggu TLD diproses di Lab (Labeling)</small>` : ''}
                                ${isLabReady && !isSurpengReady && !checkStatusPengiriman ? `<small class="text-danger d-block mt-1" style="font-size: 0.7rem;"><i class="bi bi-info-circle"></i> Menunggu Surat Pengantar ditandatangani</small>` : ''}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row g-2 bg-light p-2 rounded-3 m-2" id="listTld">
                    <div class="col-md-6">
                        <small class="text-muted d-block mb-2 text-uppercase fw-bold" style="font-size: 0.7rem;">TLD Pengguna</small>
                        <div class="d-flex flex-wrap gap-2">
                            ${htmlPengguna}
                        </div>
                    </div>
                    <div class="col-md-6 border-start ps-3">
                        <small class="text-muted d-block mb-2 text-uppercase fw-bold" style="font-size: 0.7rem;">TLD Kontrol</small>
                        <div class="d-flex flex-wrap gap-2">
                            ${htmlKontrol}
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('#list-document').append(htmlTld);
    }

    // list document invoice
    let htmlInvoice = '';
    if (dataOrderPengiriman.invoice) {
        let urlLaporanInvoice = dataOrderPengiriman.invoice?.status == 5 ? `<a data-url="laporan/invoice/${dataOrderPengiriman.invoice?.keuangan_hash}" data-title="Laporan Invoice" class="text-black" onclick="btnShowDoc(this)" href="javascript:void(0)" ><i class="bi bi-printer-fill"></i></a>` : '<i class="bi bi-printer-fill"></i>';
        // let checkedInvoice = informasi.invoice?.status == 5 ? (informasi.invoice?.pengiriman ? 'disabled' : 'checked') : 'disabled';
        let checkedInvoice = dataOrderPengiriman.invoice?.pengiriman ? 'disabled' : 'checked';
        dataOrderPengiriman.invoice ? htmlInvoice = `
            <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center rounded-3 border mb-2 shadow-xs">
                <div class="form-check">
                    <div class="d-flex align-items-center gap-2">
                        <input class="form-check-input" type="checkbox"
                            data-jenis="invoice" data-id="${dataOrderPengiriman.invoice.keuangan_hash}"
                            id="selectDocumentInvoice" name="selectDocument" onclick="updateSelectDocument()" ${checkedInvoice}>
                        <label class="form-check-label" for="checkInv">
                            <span class="fw-bold">Invoice + MoU</span>
                            <small class="text-muted ms-2">#${dataOrderPengiriman.invoice.no_invoice}</small>
                        </label>
                    </div>
                    <div>
                        <small><i class="bi bi-calendar-fill"></i> ${dateFormat(dataOrderPengiriman.invoice.created_at, 4)}</small>
                        <small>${statusFormat('pengiriman', dataOrderPengiriman.invoice.pengiriman?.status)}</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    ${statusFormat('invoice', dataOrderPengiriman.invoice.status)}
                    <small class="btn btn-sm btn-outline-primary border-0 bg-primary-subtle text-primary ${dataOrderPengiriman.invoice.status == 5 ? "cursoron" : "cursordisable"}">
                        ${urlLaporanInvoice}
                    </small>
                </div>
            </div>
        ` : false;
        $('#list-document').append(htmlInvoice);
    }

    // List Document LHU
    let aktifLHU = false;
    if(isAdendum){
        if(isAdendumZeroCek){
            aktifLHU = true;
        }
    } else {
        if (dataOrderPengiriman.lhu) {
            aktifLHU = true;
        }
    }
    let htmlLhu = '';
    if (aktifLHU) {
        let checkedLhu = 'disabled';
        let urlDocLhu = '<i class="bi bi-printer-fill"></i> Cetak LHU';

        if (dataOrderPengiriman.lhu?.status == 3) {
            checkedLhu = 'checked';
            urlDocLhu = `<a href="${base_url}/storage/${dataOrderPengiriman.lhu.media.file_path}/${dataOrderPengiriman.lhu.media.file_hash}" class="text-black" target="_blank" ><i class="bi bi-printer-fill"></i> Cetak LHU</a>`;
        }

        if (dataOrderPengiriman.lhu?.pengiriman) {
            checkedLhu = 'disabled';
        }

        let htmlRangeDate = `(${dataOrderPengiriman.periode_now.start_date ? dateFormat(dataOrderPengiriman.periode_now.start_date, 4) : '-'} - ${dataOrderPengiriman.periode_now.end_date ? dateFormat(dataOrderPengiriman.periode_now.end_date, 4) : '-'})`;

        let htmlPeriode = "";
        if (dataOrderPengiriman.lhu.periode == 1 && dataOrderPengiriman.is_zerocek == 1) {
            htmlPeriode += ' + Zero Check';
        }

        let aktifJobsLhu = dataOrderPengiriman.lhu?.penyelia_map.filter(d => d.status == 1);
        let htmlStatusLhu = statusFormat('penyelia', dataOrderPengiriman.lhu?.status);
        if (aktifJobsLhu && dataOrderPengiriman.lhu.status == 10) {
            aktifJobsLhu.map(d => {
                htmlStatusLhu += statusFormat('penyelia', d.jobs.status);
            });
        }

        htmlLhu = `
            <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center rounded-3 border mb-2">
                <div class="form-check">
                    <div class="d-flex align-items-center gap-2">
                        <input class="form-check-input" type="checkbox"
                            data-jenis="lhu" data-id="${dataOrderPengiriman.lhu.penyelia_hash}"
                            id="selectDocumentLHU" name="selectDocument" onclick="updateSelectDocument()" ${checkedLhu}>
                        <label class="form-check-label fw-bold" for="selectDocumentLHU">LHU ${htmlPeriode}</label>

                        <small class="text-body-tertiary"> - ${!dataOrderPengiriman.lhu.periode ? 'Zero Check' : `Periode ${dataOrderPengiriman.lhu.periode} ${htmlRangeDate}`} </small>
                    </div>
                    <div>
                        <small><i class="bi bi-calendar-fill"></i> ${dateFormat(dataOrderPengiriman.lhu.created_at, 4)}</small>
                        <small>${statusFormat('pengiriman', dataOrderPengiriman.lhu.pengiriman?.status)}</small>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 small flex-column">
                    ${htmlStatusLhu}
                    <!-- <small class="bg-body-tertiary rounded-pill ${dataOrderPengiriman.lhu.status == 3 ? "cursoron" : "cursordisable"} hover-1 border border-dark-subtle px-2">${urlDocLhu}</small> -->
                </div>
            </div>
        `;
        $('#list-document').append(htmlLhu);
    }

    // List document custom (akan mengikat ke id pengiriman yang ada di permohonannya)
    let htmlCustom = '';
    if (dataOrderPengiriman) {
        let checkedCustom = dataOrderPengiriman.pengiriman ? 'disabled' : 'checked';
        if (dataOrderPengiriman.file_lhu) {
            arrDocCustom.push({ jenis: "lhu zero check", media: dataOrderPengiriman.file_lhu });
        }

        for (const custom of arrDocCustom) {
            let urlDocCustom = custom.media ? `<a href="${base_url}/storage/${custom.media.file_path}/${custom.media.file_hash}" class="text-black" target="_blank" ><i class="bi bi-printer-fill"></i> Cetak Document</a>` : false;
            htmlCustom += `
                <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center rounded-3 border mb-2">
                    <div class="form-check">
                        <div class="d-flex align-items-center gap-2">
                            <input class="form-check-input" type="checkbox"
                                data-jenis="${custom.jenis}" data-id="${dataOrderPengiriman.id_hash}"
                                id="selectDocumentCustom" name="selectDocument" ${checkedCustom} disabled>
                            <label class="form-check-label fw-bold" for="selectDocumentLHU">${custom.jenis}</label>
                        </div>
                        <div>
                            <small>${statusFormat('pengiriman', dataOrderPengiriman.pengiriman?.status)}</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3 text-secondary">
                        ${urlDocCustom ? '<small class="bg-body-tertiary rounded-pill cursoron hover-1 border border-dark-subtle px-2">' + urlDocCustom + '</small>' : ''}
                    </div>
                </div>
            `;
        }
        $('#list-document').append(htmlCustom);
    }

    updateSelectDocument();
}

function updateSelectDocument() {
    let checkedDokumen = $('input[name="selectDocument"]');

    for (const doc of checkedDokumen) {
        let jenis = doc.dataset.jenis;
        let id = doc.dataset.id;
        let periode = false;
        let listTld = [];

        switch (jenis) {
            case 'lhu':
                periode = dataOrderPengiriman.lhu?.periode;
                break;
            case 'tld':
                if (doc.checked) {
                    $('#btnCetakSurat').html('<i class="bi bi-printer me-2"></i>Cetak Surat Pengantar');
                    $('#btnCetakSurat').attr('onclick', 'btnShowDoc(this)');
                    if (dataOrderPengiriman.tipe_kontrak == 'adendum') {
                        $('#btnCetakSurat').attr('data-url', `laporan/surpeng/${dataOrderPengiriman.id_hash}/${dataOrderPengiriman.periode}?adendum=1`);
                    } else {
                        if (dataOrderPengiriman.periode_next) {
                            $('#btnCetakSurat').attr('data-url', `laporan/surpeng/${dataOrderPengiriman.kontrak_hash}/1`);
                        } else {
                            $('#btnCetakSurat').attr('data-url', `laporan/surpeng/${dataOrderPengiriman.kontrak_hash}/${dataOrderPengiriman.periode_now.periode == 0 ? 1 : dataOrderPengiriman.periode_now.periode}`);
                        }
                    }
                    $('#btnCetakSurat').attr('data-title', `Surat Pengantar TLD`);
                    $('#btnCetakSurat').removeClass('btn-outline-primary').addClass('btn-outline-secondary');
                    $('#btnCetakSurat').addClass('d-block').removeClass('d-none');

                    // ── Cek apakah ada TLD adendum di list yang dipilih ──
                    const adendumTldItem = tmpArrTld.find(t => t.isAdendum);
                    if (adendumTldItem && dataOrderPengiriman.tipe_kontrak !== 'adendum') {
                        // Cari permohonan adendum yang terkait melalui kontrak_detail
                        // URL surpeng adendum menggunakan id_permohonan adendum
                        const adendumHash = adendumTldItem.permohonanHash;
                        if (adendumHash) {
                            const adendumPeriode = dataOrderPengiriman.periode_now.periode || 1;
                            $('#btnCetakSuratAdendum').attr('data-url', `laporan/surpeng/${adendumHash}/${adendumPeriode}?adendum=1`);
                        } else {
                            // Fallback: cari dari adendum data di kontrak
                            const adendumData = dataOrderPengiriman.adendum;
                            if (adendumData && adendumData.length > 0) {
                                const adPeriode = dataOrderPengiriman.periode_now.periode || 1;
                                $('#btnCetakSuratAdendum').attr('data-url', `laporan/surpeng/${adendumData[0].permohonan_hash ?? ''}/${adPeriode}?adendum=1`);
                            }
                        }
                        $('#btnCetakSuratAdendum').addClass('d-block').removeClass('d-none');
                    } else {
                        $('#btnCetakSuratAdendum').addClass('d-none').removeClass('d-block');
                    }
                } else {
                    $('#btnCetakSurat').addClass('d-none').removeClass('d-block');
                    $('#btnCetakSuratAdendum').addClass('d-none').removeClass('d-block');
                }

                periode = dataOrderPengiriman.periode_now.periode;

                if (doc.checked) {
                    $('#listTld').addClass('d-flex').removeClass('d-none');
                } else {
                    $('#listTld').addClass('d-none').removeClass('d-flex');
                }

                listTld = tmpArrTld;
                break;
            default:
                periode = dataOrderPengiriman.periode_now.periode;
                break;
        }

        let getIndex = arrSelectDocument.findIndex(d => d.jenis == jenis);
        let tmp = { jenis: jenis, periode: periode, id: id, listTld: listTld };
        if (doc.checked) {
            if (getIndex != -1) {
                arrSelectDocument[getIndex] = tmp;
            } else {
                arrSelectDocument.push(tmp);
            }
        } else {
            if (getIndex != -1) {
                arrSelectDocument.splice(getIndex, 1);
            }
        }
    }

}

function buatPengiriman(obj) {
    const alamat = $('#select_alamat').val();

    if (alamat == '') {
        return Swal.fire({ icon: 'warning', text: `Harap pilih alamat` });
    }

    if (arrSelectDocument.length == 0) {
        return Swal.fire({ icon: 'warning', text: `Harap tambahkan document yang akan dikirim` });
    }

    let hasTld = arrSelectDocument.find(d => d.jenis === 'tld');
    if (hasTld) {

        if (!isLabReady) {
            return Swal.fire({
                icon: 'warning',
                text: 'Pengiriman tidak dapat dilakukan karena TLD belum mulai diproses di Lab atau Labeling belum selesai.'
            });
        }

        let isSurpengReady = false;
        if (dataOrderPengiriman.dokumen) {
            let surpengDoc = dataOrderPengiriman.dokumen.find(d => d.jenis === 'surpeng');
            if (surpengDoc && surpengDoc.ttd) {
                isSurpengReady = true;
            }
        }

        if (!isSurpengReady) {
            return Swal.fire({
                icon: 'warning',
                text: 'Surat Pengantar belum ditandatangani. Harap tunggu persetujuan manager sebelum melakukan pengiriman.'
            });
        }
    }

    updateSelectDocument();
    Swal.fire({
        title: 'Konfirmasi Pengiriman',
        text: "Apakah Anda yakin ingin menjadwalkan pengiriman ini?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, jadwalkan!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            let dAlamat = dataOrderPengiriman.pelanggan.perusahaan.alamat[alamat];
            const params = new FormData();
            params.append('idPengiriman', $('#no_pengiriman').html());
            params.append('idPermohonan', dataOrderPengiriman.id_hash);
            params.append('idKontrak', dataOrderPengiriman.kontrak_hash)
            params.append('alamat', dAlamat.alamat_hash);
            params.append('tujuan', dataOrderPengiriman.pelanggan.id);
            params.append('status', 3);
            params.append('detail', JSON.stringify(arrSelectDocument));
            params.append('periode', dataOrderPengiriman.periode_now.periode);

            spinner('show', $(obj));
            ajaxPost('api/v1/pengiriman/buatPengiriman', params, result => {
                Swal.fire({
                    icon: 'success',
                    text: `Pengiriman di jadwalkan`,
                    timer: 1200,
                    timerProgressBar: true,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = `${base_url}/staff/pengiriman`;
                });
            }, error => {
                spinner('hide', $(obj));
            });
        }
    });
}
function _cekLastPeriode(periode_kontrak, periode_now) {
    // Ambil periode terakhir
    if (periode_kontrak) {
        const lastPeriode = periode_kontrak[periode_kontrak.length - 1];
        const isLast = periode_now < lastPeriode?.periode ? false : true;
        return isLast;
    }
    return false;
}

function btnShowDoc(obj) {
    const url = $(obj).data('url');
    const title = $(obj).data('title') || 'Dokumen';
    modalDoc.show(url, {
        title: title
    });
}
