let tmpPetugas = [];
let nowSelect = false;
let detail = false;
let filterComp = false;
let thisTab = 1;
$(function () {
    switchLoadTab(1);

    detail = new Detail({
        jenis: 'penyelia',
        tab: {
            dokumen: true,
            log: true,
            dokumen_lhu: true,
            tld: true
        }
    });

    setDropify("init", "#upload_document", {
        allowedFileExtensions: ["pdf"]
    })

    $(`[name="statusProgress"]`).on('click', obj => {
        if(obj.target.value == 'return') {
            $('#prosesNext').val(nowSelect.prosesPrev.jobs.name);
        } else {
            $('#prosesNext').val('Finish');
        }
    });

    filterComp = new FilterComponent('list-filter', {
        jenis: 'penyelia',
        filter : {
            jenis_tld : true,
            jenis_layanan : true,
            date_range: true,
            no_kontrak : true,
            perusahaan: true,
            status : true,
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
            menu = 'penerbitanlhu';
            break;
    }

    loadData(1, menu);
}

function loadData(page = 1, menu) {
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
    (filterValue.date_range && filterValue.date_range.length == 2) && (params.filter.date_range = filterValue.date_range);

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
        for (const [i, penyelia] of result.data.entries()) {
            const permohonan = penyelia.permohonan;
            let arrPeriode = permohonan.kontrak?.periode ?? permohonan.periode_pemakaian.map((d, i) => ({...d, periode: i + 1}));
            let tgl_periode = arrPeriode.find(d => d.periode == penyelia.periode);
            let badgeClass = 'bg-primary-subtle';
            if(permohonan.tipe_kontrak == 'kontrak lama') {
                badgeClass = 'bg-success-subtle';
            }

            let btnAction = '';
            switch (menu) {
                case 'surattugas':
                    btnAction += '<button class="btn btn-sm btn-outline-secondary me-1" title="Show detail" onclick="showDetail(this)"><i class="bi bi-info-circle"></i></button>';
                    if(penyelia.status == 1) {
                        btnAction += `<a class="btn btn-outline-primary btn-sm" title="Buat Surat Tugas" href="${base_url}/staff/penyelia/surat_tugas/c/${penyelia.penyelia_hash}"><i class="bi bi-plus"></i> Surat Tugas</a>`;
                    }else if(penyelia.status == 2) {
                        btnAction += `
                            <a class="btn btn-outline-info btn-sm" href="${base_url}/staff/penyelia/surat_tugas/s/${penyelia.penyelia_hash}"><i class="bi bi-eye"></i> Lihat</a>
                            <a class="btn btn-outline-warning btn-sm" href="${base_url}/staff/penyelia/surat_tugas/e/${penyelia.penyelia_hash}"><i class="bi bi-pencil-square"></i> Edit</a>
                            <button class="btn btn-outline-danger btn-sm mt-1" onclick="btnDelete(this)"><i class="bi bi-trash"></i> Hapus</button>
                        `;
                    }else{
                        btnAction += `
                            <a class="btn btn-outline-info btn-sm" href="${base_url}/staff/penyelia/surat_tugas/s/${penyelia.penyelia_hash}"><i class="bi bi-eye"></i> Lihat</a>
                        `;
                    }

                    let divInfoTugas = '';
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
                    // status jobs yang aktif
                    let htmlStatus = statusFormat('penyelia', penyelia.status);
                    const aktifJobs = penyelia.penyelia_map.filter(d => d.status == 1);
                    aktifJobs.map(d => {
                        htmlStatus += statusFormat('penyelia', d.jobs.status);
                    });

                    html += `
                        <div class="card mb-2">
                            <div class="card-body row align-items-center py-2 position-relative">
                                <div class="position-absolute top-0 end-0 w-auto"></div>
                                <div class="col-auto">
                                    <div class="">
                                        <span class="badge ${badgeClass} fw-normal rounded-pill text-secondary-emphasis">${permohonan.tipe_kontrak}</span>
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
                                        <div><i class="bi bi-person-check-fill"></i> ${permohonan.pelanggan.name}</div>
                                        <span><i class="bi bi-calendar-range"></i> Periode ${permohonan.periode}${permohonan.periode == 1 ? '/Zero cek' : ''}</span>
                                        <div><i class="bi bi-calendar-fill"></i> ${dateFormat(permohonan.created_at, 4)}</div>
                                        ${permohonan.kontrak ? `<div><i class="bi bi-file-text"></i> ${permohonan.kontrak.no_kontrak}</div>` : ''}
                                    </div>
                                </div>
                                <div class="col-6 col-md-3 text-end ms-auto" data-idpenyelia='${penyelia.penyelia_hash}'>
                                    ${btnAction}
                                </div>
                                ${divInfoTugas}
                                <div class="col-md-12 collapse" id="timeline-progress-${penyelia.penyelia_hash}">
                                    ${timeLine ? timeLine.elementCreate() : ''}
                                </div>
                            </div>
                        </div>
                    `;
                    break;
                case 'penerbitanlhu':
                    html += `
                        <div class="card mb-2">
                            <div class="card-body row align-items-center">
                                <div class="col-12 col-md-3">
                                    <div class="title">Layanan ${permohonan.layanan_jasa.nama_layanan}</div>
                                    <small class="subdesc text-body-secondary fw-light lh-sm">
                                        <div>${permohonan.jenis_tld.name}</div>
                                        <div>Periode ${penyelia.periode} : </div>
                                        <div>${tgl_periode ? dateFormat(tgl_periode.start_date, 5)+' - '+dateFormat(tgl_periode.end_date, 5) : ''}</div>
                                        <div>Created : ${dateFormat(permohonan.created_at, 4)}</div>
                                    </small>
                                </div>
                                <div class="col-6 col-md-3 my-3 text-end text-md-start">
                                    <div>${permohonan.tipe_kontrak}</div>
                                    <small class="subdesc text-body-secondary fw-light lh-sm">${permohonan.kontrak?.no_kontrak ?? ''}</small>
                                </div>
                                <div class="col-6 col-md-4 text-center">
                                    <div class="fw-bolder">Start date</div>
                                    <div>${dateFormat(penyelia.start_date, 4)}</div>
                                    <div class="fw-bolder">End date</div>
                                    <div>${dateFormat(penyelia.end_date, 4)}</div>
                                </div>
                                <div class="col-6 col-md-2 text-end" data-penyelia='${JSON.stringify(penyelia)}' data-surattugas='${penyelia.no_surat_tugas}'>
                                    <button class="btn btn-outline-primary btn-sm" title="Verifikasi" onclick="openProgressModal(this)"><i class="bi bi-check2-circle"></i> Update progress</button>
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

function openProgressModal(obj) {
    const penyelia = $(obj).parent().data("penyelia");
    $('#statusDone').prop('checked', true);
    nowSelect = penyelia;

    // Mengambil proses jobs
    const prosesNow = nowSelect.penyelia_map.find(d => d.jobs.status == nowSelect.status);
    const prosesPrev = nowSelect.penyelia_map.find(d => d.order == (prosesNow.order - 1));
    const prosesNext = nowSelect.penyelia_map.find(d => d.order == (prosesNow.order + 1));

    $('#dateProgress').flatpickr({
        altInput: true,
        locale: "id",
        dateFormat: "Y-m-d",
        altFormat: "j F Y",
        defaultDate: 'today'
    });

    prosesNow.jobs.upload_doc ? $('#divUploadDocLhu').show() : $('#divUploadDocLhu').hide();

    nowSelect.prosesNow = prosesNow;
    nowSelect.prosesPrev = prosesPrev;
    nowSelect.prosesNext = prosesNext;

    $('#prosesNow').val(prosesNow.jobs.name);
    $('#prosesNext').val(prosesNext?.jobs?.name ?? "Finish");

    $('#updateProgressModal').modal('show');
}

function simpanProgress(obj){
    let sProgress = $(`[name="statusProgress"]:checked`).val();
    let note = $('#inputNote').val();
    let status = sProgress == 'done' ? (nowSelect?.prosesNext?.jobs?.status ?? 3) : nowSelect?.prosesPrev?.jobs?.status;
    const document = $('#upload_document')[0].files[0];

    const form = new FormData();
    form.append('idPenyelia', nowSelect?.penyelia_hash);
    form.append('status', status);
    form.append('note', note);
    form.append('document', document);
    form.append('sProgress', sProgress);
    !nowSelect?.prosesNext && form.append('statusPermohonan', 4); // status permohonan untuk proses pengiriman

    spinner('show', $(obj));
    ajaxPost(`api/v1/penyelia/action`, form, result => {
        spinner('hide', $(obj));
        if(result.meta.code == 200){
            Swal.fire({
                icon: "success",
                text: 'Progress berhasil diupdate',
            });
            $('#updateProgressModal').modal('hide');
            switchLoadTab(2);
        }else{
            Swal.fire({
                icon: "error",
                text: result.data.msg,
            });
        }
    }, error => {
        spinner('hide', $(obj));
    })
}

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
    const idPenyelia = $(obj).parent().data("idpenyelia");
    detail.show(`api/v1/penyelia/getById/${idPenyelia}`);
}

function reload(tab){
    switchLoadTab(tab);
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
