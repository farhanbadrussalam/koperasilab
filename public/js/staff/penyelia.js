let tmpPetugas = [];
let dataPenyelia = false;
let detail = false;
let detailNote = false;
let filterComp = false;
let thisTab = 1;
let modalDoc = false;
$(function () {
    // mengambil params url
    let urlParams = new URLSearchParams(window.location.search);
    if(urlParams.has('md') && urlParams.has('tab')) {
        let md = urlParams.get('md');
        let tab = urlParams.get('tab');

        if(tab == 'jobs') {
            $('#penerbitanlhu-tab').click();
        }
        openProgressModal(false, md);
    } else {
        switchLoadTab(1);
    }

    modalDoc = new ModalDocument({
        title: 'Penerbitan Persetujuan Pengujian',
    });

    detail = new Detail({
        jenis: 'penyelia',
        id: 'modalDetailPenyelia',
        tab: {
            pengguna: true,
            tld: true,
            dokumen: true,
            log: true
        }
    });

    detailNote = new Detail({
        jenis: 'history_note',
        id: 'modalHistoryNote'
    });

    filterComp = new FilterComponent('list-filter', {
        jenis: 'penyelia',
        filter : {
            jenis_tld : true,
            jenis_layanan : true,
            // date_range: true,
            no_kontrak : true,
            perusahaan: true,
            status : true,
            periode: true
        }
    })

    // SETUP FILTER
    filterComp.on('filter.change', () => switchLoadTab(thisTab));
});

function switchLoadTab(menu){
    thisTab = menu;
    switch (menu) {
        case 1:
            menu = 'surattugas';
            break;

        case 2:
            menu = 'penyelialhu';
            break;
    }

    loadData(1, menu);
}

function loadData(page = 1, menu = 'penyelialhu') {
    let params = {
        limit: 5,
        page: page,
        menu: menu,
        filter: {}
    };

    let filterValue = filterComp && filterComp.getAllValue();

    filterValue.status && (params.filter.status = filterValue.status);
    filterValue.jenis_tld && (params.filter.jenis_tld = filterValue.jenis_tld);
    filterValue.jenis_layanan && (params.filter.jenis_layanan_1 = filterValue.jenis_layanan);
    filterValue.jenis_layanan_child && (params.filter.jenis_layanan_2 = filterValue.jenis_layanan_child);
    filterValue.no_kontrak && (params.filter.id_kontrak = filterValue.no_kontrak);
    filterValue.perusahaan && (params.filter.id_perusahaan = filterValue.perusahaan);
    // (filterValue.date_range && filterValue.date_range.length == 2) && (params.filter.date_range = filterValue.date_range);
    filterValue.periode && (params.filter.periode = filterValue.periode);

    if(Object.keys(params.filter).length > 0) {
        $('#countFilter').html(Object.keys(params.filter).length);
        $('#countFilter').removeClass('d-none');
    } else {
        $('#countFilter').addClass('d-none');
    }

    $(`#list-placeholder`).show();
    $(`#list-container`).hide();
    ajaxGet(`api/v1/penyelia/list`, params, result => {
        let html = '';
        let divTimelineTugas = [];
        dataPenyelia = result.data;
        for (const [i, penyelia] of result.data.entries()) {
            const permohonan = penyelia.permohonan;
            let badgeClass = 'bg-primary-subtle';
            if(permohonan.tipe_kontrak == 'kontrak lama') {
                badgeClass = 'bg-success-subtle';
            }

            let btnAction = '';
            let btnAction2 = '';
            let divInfoTugas = '';
            let htmlStatus = '';
            let aktifJobs = '';
            let htmlPeriode = '';

            // status jobs yang aktif
            htmlStatus = statusFormat('penyelia', penyelia.status);
            aktifJobs = penyelia.penyelia_map.filter(d => d.status == 1);
            aktifJobs.map(d => {
                htmlStatus += statusFormat('penyelia', d.jobs.status);
            });
            btnAction += `
                <li>
                    <a class="dropdown-item small cursor-pointer" title="Show detail" onclick="showDetail(this)">
                        <i class="bi bi-info-circle me-2"></i> Detail
                    </a>
                </li>
            `;
            switch (menu) {
                case 'surattugas':
                    // if(penyelia.status == 1 || penyelia.status == 2) {
                        const isTugasSigned = penyelia.is_surat_tugas_signed;
                        const isPengajuanSigned = penyelia.is_pengajuan_signed;
                        const hasTugas = penyelia.penyelia_map.length > 0;
                        const docPengujian = permohonan.dokumen.find(d => d.jenis === 'SuratPengujian');
                        const docTugas = permohonan.dokumen.find(d => d.jenis === 'surattugas');

                        let btnDocTugas = ``;
                        let btnDocPengujian = ``;

                        // Konfigurasi Tombol Surat Tugas
                        let tugasBtn = {
                            icon: 'bi-plus',
                            class: 'btn-outline-secondary',
                            attr: `href="${base_url}/staff/penyelia/surat_tugas/c/${penyelia.penyelia_hash}"`,
                            title: 'Buat Surat Tugas'
                        };
                        let btnRemoveTugas = '';
                        let btnNoteTugas = '';
                        let btnRemovePengajuan = '';
                        let btnNotePengajuan = '';

                        if (hasTugas) {
                            if (!isTugasSigned) {
                                tugasBtn.icon = 'bi-check2-circle';
                                tugasBtn.class = 'btn-light text-warning-emphasis';
                                tugasBtn.attr = `href="${base_url}/staff/penyelia/surat_tugas/e/${penyelia.penyelia_hash}"`;
                                tugasBtn.title = 'Lanjutkan Surat Tugas';

                                btnRemoveTugas = `
                                    <a class="btn btn-outline-danger btn-sm text-nowrap rounded-pill" title="Hapus Surat Tugas" data-type="st" onclick="btnDelete(this)">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                `;
                            } else if (isTugasSigned === 1) {
                                // Jika sudah signed, hanya sebagai informasi
                                tugasBtn.icon = 'bi-check2-all';
                                tugasBtn.class = 'btn-light text-success';
                                tugasBtn.attr = 'href="javascript:void(0)" style="cursor: default; pointer-events: none;"';
                                tugasBtn.title = 'Surat Tugas Selesai (Signed)';

                                btnDocTugas = `
                                    <a class="btn btn-outline-primary btn-sm text-nowrap rounded-pill" target="_blank" href="${base_url}/laporan/${docTugas.jenis}/${docTugas.permohonan_hash}" title="Download Surat Tugas">
                                        <i class="bi bi-file-earmark-text"></i>
                                    </a>
                                `;
                            } else {
                                // Jika surat tugas ditolak atau ada kondisi lain, sesuaikan dengan kebutuhan
                                tugasBtn.icon = 'bi-x-circle';
                                tugasBtn.class = 'btn-light text-danger';
                                tugasBtn.attr = `href="${base_url}/staff/penyelia/surat_tugas/e/${penyelia.penyelia_hash}"`;
                                tugasBtn.title = 'Surat Tugas Ditolak';

                                btnNoteTugas = `
                                    <a class="btn btn-outline-warning btn-sm text-nowrap rounded-pill" title="Catatan Surat Tugas" data-type="st" onclick="btnNote(this)">
                                        <i class="bi bi-chat-left-text"></i>
                                    </a>
                                `;
                                btnRemoveTugas = `
                                    <a class="btn btn-outline-danger btn-sm text-nowrap rounded-pill" title="Hapus Surat Tugas" data-type="st" onclick="btnDelete(this)">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                `;
                            }
                        }

                        btnAction2 = `
                            <div class="d-flex justify-content-between gap-1">
                                <a class="btn ${tugasBtn.class} btn-sm text-nowrap rounded-pill w-100" title="${tugasBtn.title}" ${tugasBtn.attr}>
                                    <i class="bi ${tugasBtn.icon}"></i> Surat Tugas
                                </a>
                                ${btnDocTugas}
                                ${btnNoteTugas}
                                ${btnRemoveTugas}
                            </div>
                        `;

                        // Konfigurasi Tombol Surat Pengujian
                        if (jenislayanan(permohonan.jenis_layanan_parent, permohonan.jenis_layanan) === 'EvaluasiTanpaKontrak') {
                            let pengujianBtn = {
                                icon: 'bi-plus',
                                class: 'btn-outline-secondary',
                                attr: `onclick="createPengujian('${penyelia.penyelia_hash}')"`,
                                title: 'Buat Surat Pengujian'
                            };

                            if (docPengujian) {
                                if (!isPengajuanSigned) {
                                    pengujianBtn.icon = 'bi-check2-circle';
                                    pengujianBtn.class = 'btn-light text-warning-emphasis';
                                    pengujianBtn.attr = 'disabled';
                                    pengujianBtn.title = 'Lanjutkan Surat Pengujian';

                                    btnRemovePengajuan = `
                                        <a class="btn btn-outline-danger btn-sm text-nowrap rounded-pill" title="Hapus Surat Pengujian" data-type="sp" onclick="btnDelete(this)">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    `;
                                } else if (isPengajuanSigned === 1) {
                                    // Jika sudah signed, non-aktifkan tombol
                                    pengujianBtn.icon = 'bi-check2-all';
                                    pengujianBtn.class = 'btn-light text-success';
                                    pengujianBtn.attr = 'disabled';
                                    pengujianBtn.title = 'Surat Pengujian Selesai (Signed)';

                                    btnDocPengujian = `
                                        <a class="btn btn-outline-primary btn-sm text-nowrap rounded-pill" target="_blank" href="${base_url}/laporan/${docPengujian.jenis}/${docPengujian.permohonan_hash}" title="Download Surat Pengujian">
                                            <i class="bi bi-file-earmark-text"></i>
                                        </a>
                                    `;
                                } else {
                                    // Jika surat pengujian ditolak atau ada kondisi lain, sesuaikan dengan kebutuhan
                                    pengujianBtn.icon = 'bi-x-circle';
                                    pengujianBtn.class = 'btn-light text-danger';
                                    pengujianBtn.attr = 'disabled';
                                    pengujianBtn.title = 'Surat Pengujian Ditolak';

                                    btnNotePengajuan = `
                                        <a class="btn btn-outline-warning btn-sm text-nowrap rounded-pill" title="Catatan Surat Pengujian" data-type="sp" onclick="btnNote(this)">
                                            <i class="bi bi-chat-left-text"></i>
                                        </a>
                                    `;

                                    btnRemovePengajuan = `
                                        <a class="btn btn-outline-danger btn-sm text-nowrap rounded-pill" title="Hapus Surat Pengujian" data-type="sp" onclick="btnDelete(this)">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    `;
                                }
                            }

                            btnAction2 = `
                                <div class="d-flex justify-content-center flex-column gap-2">
                                    ${btnAction2}
                                    <div class="d-flex justify-content-between gap-1">
                                        <button class="btn ${pengujianBtn.class} btn-sm text-nowrap rounded-pill w-100" title="${pengujianBtn.title}" ${pengujianBtn.attr}>
                                            <i class="bi ${pengujianBtn.icon}"></i> Surat Pengujian
                                        </button>
                                        ${btnDocPengujian}
                                        ${btnNotePengajuan}
                                        ${btnRemovePengajuan}
                                    </div>
                                </div>
                            `;
                        }
                    // }

                    let timeLine = new Timeline({
                        timeline: penyelia.penyelia_map,
                        status: penyelia.status,
                        id: penyelia.penyelia_hash,
                        startDate: penyelia.start_date,
                        endDate: penyelia.end_date,
                    });
                    divTimelineTugas.push(timeLine);

                    htmlPeriode = !permohonan.periode ? 'Zero cek' : permohonan.periode;
                    if(permohonan.is_have_tld && permohonan.is_zerocek && permohonan.periode == 1) {
                        htmlPeriode += ' + Zero cek';
                    }

                    // range periode
                    let rangePeriode = '';
                    if(permohonan.periode && permohonan.periodenow) {
                        rangePeriode = `<span class="fs-8">(${dateFormat(permohonan.periodenow.start_date, 4)} - ${dateFormat(permohonan.periodenow.end_date, 4)})</span>`;
                    }

                    const dataS = {
                        tipeKontrak: permohonan.tipe_kontrak,
                        jenisLayananParent: permohonan.jenis_layanan_parent.name,
                        jenisLayanan: permohonan.jenis_layanan.name,
                        statusPenyelia: htmlStatus,
                        jenisTld: permohonan.jenis_tld?.name ?? '-',
                        namaLayanan: permohonan.layanan_jasa?.nama_layanan ?? '-',
                        periode: permohonan.periode,
                        created_at: permohonan.created_at,
                        kontrak: permohonan.kontrak?.no_kontrak,
                        id: penyelia.penyelia_hash,
                        is_have_tld: permohonan.is_have_tld,
                        is_zerocek: permohonan.is_zerocek,
                        pelanggan: permohonan.pelanggan.name,
                        divTimelineTugas: timeLine,
                        status: penyelia.status,
                        perusahaan: permohonan.pelanggan.perusahaan.nama_perusahaan,
                    }

                    html += cardComponent(dataS, {btnMenuAction : btnAction, btnAction: btnAction2});
                    break;
                case 'penyelialhu':
                    let timeline = new Timeline({
                        timeline: penyelia.penyelia_map,
                        status: penyelia.status,
                        id: penyelia.penyelia_hash,
                        startDate: penyelia.start_date,
                        endDate: penyelia.end_date,
                    });
                    divTimelineTugas.push(timeline);

                    btnAction2 += `<button class="btn btn-outline-primary btn-sm" title="Verifikasi" onclick="openProgressModal(this)"><i class="bi bi-check2-circle"></i> update progress</button>`;

                    const dataP = {
                        tipeKontrak: permohonan.tipe_kontrak,
                        jenisLayananParent: permohonan.jenis_layanan_parent.name,
                        jenisLayanan: permohonan.jenis_layanan.name,
                        statusPenyelia: htmlStatus,
                        jenisTld: permohonan.jenis_tld?.name ?? '-',
                        namaLayanan: permohonan.layanan_jasa?.nama_layanan ?? '-',
                        periode: permohonan.periode,
                        created_at: permohonan.created_at,
                        kontrak: permohonan.kontrak?.no_kontrak,
                        id: penyelia.penyelia_hash,
                        is_have_tld: permohonan.is_have_tld,
                        is_zerocek: permohonan.is_zerocek,
                        pelanggan: permohonan.pelanggan.name,
                        divTimelineTugas: timeline,
                        status: penyelia.status,
                        index: i,
                        perusahaan: permohonan.pelanggan.perusahaan.nama_perusahaan,
                    }

                    html += cardComponent(dataP, {btnMenuAction : btnAction, btnAction: btnAction2});
                    break;
                default:
                    break;
            }
        }

        if(result.data.length == 0){
            html = htmlNoData();
        }

        $(`#list-container`).html(html);

        divTimelineTugas.map(d => d.render());

        $(`#list-pagination`).html(createPaginationHTML(result.pagination));

        $(`#list-placeholder`).hide();
        $(`#list-container`).show();
    })
}

$('#list-pagination').on('click', 'a', function (e) {
    e.preventDefault();
    const pageno = e.target.dataset.page;
    let menu = '';
    switch (thisTab) {
        case 1:
            menu = 'surattugas';
            break;

        case 2:
            menu = 'penyelialhu';
            break;
    }

    loadData(pageno, menu);
});

function btnDelete(obj) {
    const $btn = $(obj);
    const id = $btn.closest('[data-id]').data('id');
    const type = $btn.closest('[data-type]').data('type');

    if (!id) return;

    ajaxDelete(`api/v1/penyelia/remove/${id}/${type}`, result => {
        Swal.fire({
            icon: 'success',
            text: result.data?.msg || 'Data berhasil dihapus',
            timer: 1200,
            timerProgressBar: true,
            showConfirmButton: false
        }).then(() => reload());
    }, error => {
        console.error('Terjadi kesalahan saat menghapus penyelia:', error);
    });
}

function btnNote(obj) {
    const id = $(obj).closest('[data-id]').data('id');
    const type = $(obj).data('type');

    if (!id) return;

    NoteComponent.showLoading();
    ajaxGet(`logs/proses?mode=penyelia&log_name=HISTORY_DOCUMENT&id=${id}&key=${type == 'st' ? 'surat_tugas' : 'surat_pengujian'}`, {}, result => {
        let typeLabel = type == 'st' ? 'Surat Tugas' : 'Surat Pengujian';
        let note = result.data[0]?.properties?.catatan;
        NoteComponent.render({
            title: `${typeLabel}`,
            note: note ? `
                <div class="badge bg-danger-subtle text-danger rounded-pill">${result.data[0]?.description}</div>
                <div><b>Catatan :</b> ${note}</div>
            ` : `Belum ada catatan.`,
            created_at: dateFormat(result.data[0]?.created_at, 4),
            author: result.data[0]?.causer?.name
        });
    }, error => {
        console.error('Terjadi kesalahan saat mengambil catatan:', error);
        NoteComponent.renderError('Gagal memuat catatan. Silakan coba lagi nanti.');
    });
}
function showDetail(obj){
    const idPenyelia = $(obj).parent().parent().data("id");
    detail.show(`api/v1/penyelia/getById/${idPenyelia}`);
}

function reload(){
    switchLoadTab(thisTab);
}

function clearFilter(){
    filterComp.clear();
    switchLoadTab(thisTab);
}

function createPengujian(id){
    let find = dataPenyelia.find(d => d.penyelia_hash == id);

    // jenis pengujian
    let zrcek = find.permohonan.is_zerocek ? 'Zero Cek' : '';
    let lJasa = find.permohonan.layanan_jasa.nama_layanan;
    let jTld = find.permohonan.jenis_tld.name;
    let jenisPengujian = zrcek + ' ' + lJasa + ' ' + jTld;

    // sample
    let samplesArr = [`${lJasa} ${jTld}`];
    let kontrak = find.permohonan.kontrak;

    // template surat pengujian
    let template = find.template_surat.find(d => d.name == 'SuratPengujian');

    // periode
    for (const periode of kontrak.periode) {
        if(periode.periode != 0) {
            let startDate = dateFormat(periode.start_date, 6);
            let endDate = dateFormat(periode.end_date, 6);

            samplesArr.push(`${kontrak.jumlah_kontrol} + ${kontrak.jumlah_pengguna} (${startDate} - ${endDate})`);
        }
    }

    // load pertanyaan
    let htmlPertanyaan = '<h6 class="fw-bold mb-3 text-primary"><i class="bi bi-question-circle me-2"></i>Daftar Pertanyaan</h6>';
    for (const [i,pertanyaan] of template.data_pertanyaan.entries()) {
        let htmlAnswer = ``;
        let htmlMandatory = pertanyaan.mandatory ? '<span class="text-danger ms-1">*</span>' : '';
        if(pertanyaan.type == 2) {
            htmlAnswer = `
            <div class="d-flex gap-2">
                <div class="flex-fill">
                    <input type="radio" class="btn-check" name="answer_${i}" id="answer_${i}_siap" value="siap" autocomplete="off">
                    <label class="btn btn-outline-success btn-sm w-100 rounded-3 py-2 fw-semibold" for="answer_${i}_siap">
                        <i class="bi bi-check-circle me-1"></i> Siap
                    </label>
                </div>
                <div class="flex-fill">
                    <input type="radio" class="btn-check" name="answer_${i}" id="answer_${i}_tidak_siap" value="tidak siap" autocomplete="off">
                    <label class="btn btn-outline-danger btn-sm w-100 rounded-3 py-2 fw-semibold" for="answer_${i}_tidak_siap">
                        <i class="bi bi-x-circle me-1"></i> Tidak Siap
                    </label>
                </div>
            </div>
            `;
        }
        htmlPertanyaan += `
            <div class="mb-4">
                <label class="fw-semibold text-dark mb-2 small d-block">${i + 1}. ${pertanyaan.pertanyaan}${htmlMandatory}</label>
                ${htmlAnswer}
            </div>
        `;
    }

    const dataPreview = {
        pemilik: find.permohonan.pelanggan.perusahaan.nama_perusahaan,
        alamat: find.permohonan.pelanggan.perusahaan.alamat[0].alamat,
        jenis_pengujian: jenisPengujian,
        samples: samplesArr,
        pertanyaan: htmlPertanyaan
    }
    PengujianComponent.open(dataPreview, 'create', {
        onSave: () => { simpanPengujian(id) }
    });
    return;
}



function simpanPengujian(id){
    // sanity cek pertanyaan
    let find = dataPenyelia.find(d => d.penyelia_hash == id);
    let template = find.template_surat.find(d => d.name == 'SuratPengujian');
    let pertanyaan = template.data_pertanyaan;
    let answers = [];
    let status = true;
    for (const [i, value] of pertanyaan.entries()) {
        let answer = $('input[name="answer_'+i+'"]:checked').val();
        answers.push({
            id: value.pertanyaan_hash,
            answer,
        });
        if(answer == undefined){
            status = false;
        }
    }

    if(!status){
        Swal.fire({
            icon: "warning",
            text: 'Lengkapi pertanyaan terlebih dahulu',
        });
        return;
    }

    Swal.fire({
        icon: 'question',
        text: 'Apakah anda yakin ingin membuat pengujian?',
        showCancelButton: true,
        confirmButtonText: 'Ya',
        cancelButtonText: 'Tidak',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            showLoadingSwal('show');
            const form = new FormData();
            form.append('idPenyelia', id);
            form.append('status', 6);
            form.append('answers', JSON.stringify(answers));
            ajaxPost(`api/v1/penyelia/createPengujian`, form, result => {
                Swal.fire({
                    icon: 'success',
                    text: result.data?.msg || 'Data berhasil dihapus',
                    timer: 1200,
                    timerProgressBar: true,
                    showConfirmButton: false
                }).then(() => {
                    PengujianComponent.hide();
                    showLoadingSwal('hide');
                    reload();
                });
            }, error => {
                showLoadingSwal('hide');
            });
        }
    })
}
