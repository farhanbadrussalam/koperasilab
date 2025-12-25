let tmpPetugas = [];
let dataPenyelia = false;
let detail = false;
let filterComp = false;
let thisTab = 1;
let modalDoc = false;
$(function () {
    switchLoadTab(1);
    modalDoc = new ModalDocument({
        title: 'Penerbitan Persetujuan Pengujian',
    });

    detail = new Detail({
        jenis: 'penyelia',
        tab: {
            dokumen: true,
            log: true,
            tld: true
        }
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
            let arrPeriode = permohonan.kontrak?.periode ?? permohonan.periode_pemakaian.map((d, i) => ({...d, periode: i + 1}));
            let tgl_periode = arrPeriode.find(d => d.periode == penyelia.periode);
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
                        <i class="bi bi-eye me-2"></i> Detail
                    </a>
                </li>
            `;
            switch (menu) {
                case 'surattugas':
                    if(penyelia.status == 1) {
                        btnAction2 += `
                            <a class="btn btn-outline-primary btn-sm text-nowrap" title="Buat Surat Tugas" href="${base_url}/staff/penyelia/surat_tugas/c/${penyelia.penyelia_hash}">
                                <i class="bi bi-plus"></i> Surat Tugas
                            </a>
                        `;
                    }else if(penyelia.status == 2) {
                        btnAction += `
                            <li>
                                <a class="dropdown-item small cursor-pointer" href="${base_url}/staff/penyelia/surat_tugas/s/${penyelia.penyelia_hash}">
                                    <i class="bi bi-eye"></i> Lihat
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item small cursor-pointer" href="${base_url}/staff/penyelia/surat_tugas/e/${penyelia.penyelia_hash}">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item small cursor-pointer text-danger" onclick="btnDelete(this)">
                                    <i class="bi bi-trash"></i> Hapus
                                </a>
                            </li>
                        `;
                    } else if(penyelia.status == 5) {
                        btnAction += `
                            <li>
                                <a onclick="createPengujian('${penyelia.penyelia_hash}')" class="dropdown-item small cursor-pointer" title="Buat Surat Pengujian" >
                                    <i class="bi bi-plus me-2"></i> Surat Pengujian
                                </a>
                            </li>
                        `;
                    } else if(penyelia.status != 6) {
                        btnAction += `
                            <li>
                                <a class="dropdown-item small cursor-pointer" href="${base_url}/staff/penyelia/surat_tugas/s/${penyelia.penyelia_hash}">
                                    <i class="bi bi-eye me-2"></i> Lihat
                                </a>
                            </li>
                        `;
                    }

                    divInfoTugas = '';
                    let timeLine = false;
                    if(penyelia.start_date && penyelia.end_date){
                        divInfoTugas = `
                            <div class="col-md-12 mt-2 fs-7">
                                <div class="rounded bg-secondary-subtle ps-2 text-body-secondary d-flex justify-content-between align-items-center">
                                    <span>Durasi pelaksanaan layanan ${dateFormat(penyelia.start_date, 4)} s/d ${dateFormat(penyelia.end_date, 4)}</span>
                                    <a class="py-1 px-2 text-decoration-none border rounded-2" href="#timeline-progress-${penyelia.penyelia_hash}" data-bs-toggle="collapse"
                                    onclick="showHideProgress(this)">Lihat Progress LAB</a>
                                </div>
                            </div>
                        `;

                        timeLine = new Timeline({
                            timeline: penyelia.penyelia_map,
                            status: penyelia.status,
                            id: penyelia.penyelia_hash
                        });
                        divTimelineTugas.push(timeLine);
                    }

                    htmlPeriode = !permohonan.periode ? 'Zero cek' : 'Periode '+permohonan.periode;
                    if(permohonan.is_have_tld && permohonan.is_zerocek && permohonan.periode == 1) {
                        htmlPeriode += ' + Zero cek';
                    }

                    // range periode
                    let rangePeriode = '';
                    if(permohonan.periode) {
                        rangePeriode = `<span class="fs-8">(${dateFormat(penyelia.permohonan.periodenow.start_date, 4)} - ${dateFormat(penyelia.permohonan.periodenow.end_date, 4)})</span>`;
                    }

                    const dataS = {
                        tipeKontrak: permohonan.tipe_kontrak,
                        jenisLayananParent: permohonan.jenis_layanan_parent.name,
                        jenisLayanan: permohonan.jenis_layanan.name,
                        statusPenyelia: htmlStatus,
                        jenisTld: permohonan.jenis_tld?.name ?? '-',
                        namaLayanan: permohonan.layanan_jasa?.nama_layanan ?? '-',
                        periode: htmlPeriode + " " + rangePeriode,
                        created_at: permohonan.created_at,
                        kontrak: permohonan.kontrak?.no_kontrak,
                        id: penyelia.penyelia_hash,
                        is_have_tld: permohonan.is_have_tld,
                        is_zerocek: permohonan.is_zerocek,
                        pelanggan: permohonan.pelanggan.name,
                        divInfoTugas: divInfoTugas,
                        divTimelineTugas: timeLine
                    }

                    html += cardComponent(dataS, {btnMenuAction : btnAction, btnAction: btnAction2});

                    // html += `
                    //     <div class="card mb-2">
                    //         <div class="card-body row align-items-center py-2 position-relative">
                    //             <div class="position-absolute top-0 end-0 w-auto"></div>
                    //             <div class="col-auto">
                    //                 <div class="">
                    //                     <span class="badge ${badgeClass} fw-normal rounded-pill text-secondary-emphasis">${permohonan.tipe_kontrak}</span>
                    //                     <span class="badge bg-secondary-subtle fw-normal rounded-pill text-secondary-emphasis">${permohonan.jenis_layanan_parent.name} - ${permohonan.jenis_layanan.name}</span>
                    //                     <span> | ${htmlStatus}</span>
                    //                 </div>
                    //                 <div class="fs-5 my-2">
                    //                     <span class="fw-bold">${permohonan.jenis_tld?.name ?? '-'} - Layanan ${permohonan.layanan_jasa?.nama_layanan}</span>
                    //                     <div class="text-body-tertiary fs-7">
                    //                         <div><i class="bi bi-building-fill"></i> ${permohonan.pelanggan.perusahaan.nama_perusahaan}</div>
                    //                         <div><i class="bi bi-calendar-fill"></i> ${dateFormat(permohonan.created_at, 4)}</div>
                    //                     </div>
                    //                 </div>
                    //                 <div class="d-flex gap-3 text-body-tertiary fs-7">
                    //                     <div><i class="bi bi-person-check-fill"></i> ${permohonan.pelanggan.name}</div>
                    //                     <span><i class="bi bi-calendar-range"></i> ${htmlPeriode} ${rangePeriode}</span>
                    //                     ${permohonan.kontrak ? `<div><i class="bi bi-file-text"></i> ${permohonan.kontrak.no_kontrak}</div>` : ''}
                    //                 </div>
                    //             </div>
                    //             <div class="col-6 col-md-3 text-end ms-auto" data-idpenyelia='${penyelia.penyelia_hash}'>
                    //                 ${btnAction}
                    //             </div>
                    //             ${divInfoTugas}
                    //             <div class="col-md-12 collapse" id="timeline-progress-${penyelia.penyelia_hash}">
                    //                 ${timeLine ? timeLine.elementCreate() : ''}
                    //             </div>
                    //         </div>
                    //     </div>
                    // `;
                    break;
                case 'penyelialhu':
                    divInfoTugas = `
                        <div class="col-md-12 mt-2 fs-7">
                            <div class="rounded bg-secondary-subtle ps-2 text-body-secondary d-flex justify-content-between align-items-center">
                                <span>Durasi pelaksanaan layanan ${dateFormat(penyelia.start_date, 4)} s/d ${dateFormat(penyelia.end_date, 4)}</span>
                                <a class="py-1 px-2 text-decoration-none border rounded-2" href="#timeline-progress-${penyelia.penyelia_hash}" data-bs-toggle="collapse"
                                onclick="showHideProgress(this)">Lihat Progress LAB</a>
                            </div>
                        </div>
                    `;

                    const timeline = new Timeline({
                        timeline: penyelia.penyelia_map,
                        status: penyelia.status,
                        id: penyelia.penyelia_hash
                    });

                    divTimelineTugas.push(timeline);

                    htmlPeriode = permohonan.periode == 0 ? `Zero cek` : `Periode ${permohonan.periode}`;
                    if(permohonan.is_have_tld && permohonan.is_zerocek && permohonan.periode == 1) {
                        htmlPeriode += ' + Zero cek';
                    }
                    btnAction += `<button class="btn btn-outline-primary btn-sm" title="Verifikasi" onclick="openProgressModal(this)"><i class="bi bi-check2-circle"></i> update progress</button>`;

                    html += `
                        <div class="card mb-2">
                            <div class="card-body row align-items-center py-2">
                                <div class="col-auto">
                                    <div class="">
                                        <span class="badge bg-primary-subtle fw-normal rounded-pill text-secondary-emphasis">${permohonan.tipe_kontrak}</span>
                                        <span class="badge bg-secondary-subtle fw-normal rounded-pill text-secondary-emphasis">${permohonan.jenis_layanan_parent.name} - ${permohonan.jenis_layanan.name}</span>
                                        <span> | ${htmlStatus}</span>
                                    </div>
                                    <div class="fs-5 my-2">
                                        <span class="fw-bold">${permohonan.jenis_tld?.name ?? '-'} - Layanan ${permohonan.layanan_jasa?.nama_layanan}</span>
                                        <div class="text-body-tertiary fs-7">
                                            <div><i class="bi bi-building-fill"></i> ${permohonan.pelanggan.perusahaan.nama_perusahaan}</div>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-3 text-body-tertiary fs-7">
                                        <span><i class="bi bi-calendar-range"></i> ${htmlPeriode}</span>
                                        <div><i class="bi bi-calendar-fill"></i> ${dateFormat(permohonan.created_at, 4)}</div>
                                        ${permohonan.kontrak ? `<div><i class="bi bi-file-text"></i> ${permohonan.kontrak.no_kontrak}</div>` : ''}
                                    </div>
                                </div>
                                <div class="ms-auto col-auto text-center gap-1 d-flex" data-id='${penyelia.penyelia_hash}' data-index='${i}'>
                                    ${btnAction}
                                </div>
                                ${divInfoTugas}
                                <div class="col-md-12 collapse" id="timeline-progress-${penyelia.penyelia_hash}">
                                    ${timeline.elementCreate()}
                                </div>
                            </div>
                        </div>
                    `;
                    break;
                default:
                    break;
            }
        }

        if(result.data.length == 0){
            html = `
                <div class="d-flex flex-column align-items-center py-3">
                    <img src="${base_url}/images/no_data2_color.svg" style="width:220px" alt="">
                    <span class="fw-bold mt-3 text-muted">No Data Available</span>
                </div>
            `;
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
    const id = $(obj).parent().data('idpenyelia');
    ajaxDelete(`api/v1/penyelia/remove/${id}`, result => {
        Swal.fire({
            icon: 'success',
            text: result.data.msg,
            timer: 1200,
            timerProgressBar: true,
            showConfirmButton: false
        }).then(() => {
            switchLoadTab(1);
        });
    }, error => {
        const result = error.responseJSON;
        if(result?.meta?.code && result?.meta?.code == 500){
            Swal.fire({
                icon: "error",
                text: 'Server error',
            });
            console.error(result.data.msg);
        }else{
            Swal.fire({
                icon: "error",
                text: 'Server error',
            });
            console.error(error);
        }
        spinner(`hide`, $(obj.target));
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

function showHideProgress(obj){
    const collapse = obj;
    if(!collapse.classList.contains('show')) {
        collapse.innerText = 'Lebih sedikit';
    } else {
        collapse.innerText = 'Lihat Progress LAB';
    }
    collapse.classList.toggle('show');
}

function createPengujian(id){
    let find = dataPenyelia.find(d => d.penyelia_hash == id);
    // jenis pengujian
    let zrcek = find.permohonan.is_zerocek ? 'Zero Cek' : '';
    let lJasa = find.permohonan.layanan_jasa.nama_layanan;
    let jTld = find.permohonan.jenis_tld.name;
    let jenisPengujian = zrcek + ' ' + lJasa + ' ' + jTld;
    $('#list-sample').empty();

    // sample
    let htmlSample = `<div>${lJasa} ${jTld}</div>`;

    $('#list-sample').append(htmlSample);
    let kontrak = find.permohonan.kontrak;

    // template surat pengujian
    let template = find.template_surat.find(d => d.name == 'SuratPengujian');

    // periode
    for (const periode of kontrak.periode) {
        if(periode.periode != 0) {
            let startDate = dateFormat(periode.start_date, 6);
            let endDate = dateFormat(periode.end_date, 6);

            $('#list-sample').append(`
                <div>${kontrak.jumlah_kontrol} + ${kontrak.jumlah_pengguna} ${startDate} - ${endDate}</div>
            `);
        }
    }

    // load pertanyaan
    let htmlPertanyaan = '';
    for (const [i,pertanyaan] of template.data_pertanyaan.entries()) {
        let htmlAnswer = ``;
        let htmlMandatory = pertanyaan.mandatory ? '<span class="text-danger ml-2">*</span>' : '';
        if(pertanyaan.type == 2) {
            htmlAnswer = `
            <div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="answer_${i}" id="answer_${i}_siap" value="siap">
                    <label class="form-check-label" for="answer_${i}_siap">Siap</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="answer_${i}" id="answer_${i}_tidak_siap" value="tidak siap">
                    <label class="form-check-label" for="answer_${i}_tidak_siap">Tidak Siap</label>
                </div>
            </div>
            `;
        }
        htmlPertanyaan += `
            <div class="mb-3">
                <label for="" class="mb-2">${pertanyaan.pertanyaan+htmlMandatory}</label>
                ${htmlAnswer}
            </div>
        `;
    }

    $('#content-pertanyaan').html(htmlPertanyaan);

    $('#inputJenisPengujian').text(jenisPengujian);
    $('#inputPemilik').text(find.permohonan.pelanggan.perusahaan.nama_perusahaan);
    $('#inputAlamat').text(find.permohonan.pelanggan.perusahaan.alamat[0].alamat);
    $('#txt_id_penyelia').val(id);
    $('#create_modal_surat_pengujian').modal('show');
}



function btnCreatePengujian(obj){
    let id = $('#txt_id_penyelia').val();
    spinner('show', $(obj));

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
        spinner('hide', $(obj));
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
            const form = new FormData();
            form.append('idPenyelia', id);
            form.append('status', 6);
            form.append('answers', JSON.stringify(answers));
            ajaxPost(`api/v1/penyelia/createPengujian`, form, result => {
                spinner('hide', $(obj));
                if(result.meta.code == 200){
                    spinner('hide', $(obj));
                    $('#create_modal_surat_pengujian').modal('hide');
                    reload();
                }else{
                    Swal.fire({
                        icon: "error",
                        text: result.data.msg,
                    });
                }
            }, error => {
                spinner('hide', $(obj));
            });
        } else {
            spinner('hide', $(obj));
        }
    })
}
