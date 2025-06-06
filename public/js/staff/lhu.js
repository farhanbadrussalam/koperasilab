let dataPenyelia = [];
let nowSelect = false;
let detail = false;
let documentLhu = false;
let filterComp = false;
$(function () {
    loadData();

    detail = new Detail({
        jenis: 'penyelia',
        tab: {
            dokumen: true,
            dokumen_lhu: true,
            log: true
        }
    });

    $('#updateProgressModal').on('hide.bs.modal', () => {
        nowSelect = false;
    });

    $(`[name="statusProgress"]`).on('click', obj => {
        const prosesNow = nowSelect.penyelia_map.find(d => listJobs.includes(d.jobs_hash) && d.status == 1);

        if(obj.target.value == 'return') {
            $('#divUploadDocLhu').hide();
            $('#prosesNext').val(nowSelect.prosesPrev.jobs.name);
        } else {
            prosesNow.jobs.upload_doc ? $('#divUploadDocLhu').show() : $('#divUploadDocLhu').hide();
            $('#prosesNext').val(nowSelect.prosesNext.jobs.name);
        }
    });
    $('#prosesNow').on('change', obj => {
        const prosesNow = nowSelect.penyelia_map.find(d => d.map_hash == obj.target.value);
        setProses(prosesNow);
    });

    filterComp = new FilterComponent('list-filter', {
        filter : {
            jenis_tld : true,
            jenis_layanan : true,
            no_kontrak : true,
            perusahaan : true
        }
    });

    // SETUP FILTER
    filterComp.on('filter.change', () => loadData());
});

function loadData(page = 1) {
    let params = {
        limit: 10,
        page: page,
        status: listJobs,
        filter: {}
    };

    let filterValue = filterComp && filterComp.getAllValue();

    filterValue.jenis_tld && (params.filter.jenis_tld = filterValue.jenis_tld);
    filterValue.jenis_layanan && (params.filter.jenis_layanan_1 = filterValue.jenis_layanan);
    filterValue.jenis_layanan_child && (params.filter.jenis_layanan_2 = filterValue.jenis_layanan_child);
    filterValue.no_kontrak && (params.filter.id_kontrak = filterValue.no_kontrak);
    filterValue.perusahaan && (params.filter.id_perusahaan = filterValue.perusahaan);

    if(Object.keys(params.filter).length > 0) {
        $('#countFilter').html(Object.keys(params.filter).length);
        $('#countFilter').removeClass('d-none');
    } else {
        $('#countFilter').addClass('d-none');
    }

    $(`#list-placeholder-lhu`).show();
    $(`#list-container-lhu`).hide();
    ajaxGet(`api/v1/penyelia/list`, params, result => {
        let html = '';
        dataPenyelia = result.data;
        let divTimelineTugas = [];
        for (const [i, lhu] of result.data.entries()) {
            const permohonan = lhu.permohonan;
            let periode = permohonan.periode_pemakaian;
            let btnAction = '';
            // Mengecek array listJobs apakah ada di jobsAktive
            // Algoritma ini opsional jika dimunculkan semuanya
            /*
                let jobsAktive = lhu.penyelia_map.filter(x => x.status == 1);
                let jobsAktiveHash = jobsAktive.map(x => x.jobs_hash);
                const hasCommonValue = jobsAktiveHash.some(hash => listJobs.includes(hash));
            */

            let divInfoTugas = `
                <div class="col-md-12 mt-2 fs-7">
                    <div class="rounded bg-secondary-subtle ps-2 text-body-secondary d-flex justify-content-between align-items-center">
                        <span>Durasi pelaksanaan layanan ${dateFormat(lhu.start_date, 4)} s/d ${dateFormat(lhu.end_date, 4)}</span>
                        <a class="py-1 px-2 text-decoration-none border rounded-2" href="#timeline-progress-${lhu.penyelia_hash}" data-bs-toggle="collapse"
                        onclick="showHideProgress(this)">Lihat Progress LAB</a>
                    </div>
                </div>
            `;

            const timeline = new Timeline({
                timeline: lhu.penyelia_map,
                status: lhu.status,
                id: lhu.penyelia_hash
            });
            divTimelineTugas.push(timeline);

            let htmlPeriode = `
                <div>${periode?.length ?? '0'} Periode</div>
            `;
            if(permohonan.periode){
                htmlPeriode = `<div>Periode ${permohonan.periode}</div>`;
            }

            // status jobs yang aktif
            let isPelabelan = false;
            console.log(lhu.status);
            let htmlStatus = statusFormat('penyelia', lhu.status);
            const aktifJobs = lhu.penyelia_map.filter(d => listJobs.includes(d.jobs_hash) && d.status == 1);
            aktifJobs.map(d => {
                let petugasInJobs = lhu.petugas.find(y => y.map_hash == d.map_hash && y.user_hash == userActive.user_hash);
                if(petugasInJobs){
                    d.jobs.status == 20 ? isPelabelan = true : false;
                    htmlStatus += statusFormat('penyelia', d.jobs.status);
                }
            })

            // button action
            btnAction += `<button class="btn btn-sm btn-outline-secondary" title="Show detail" onclick="showDetail(this)"><i class="bi bi-info-circle"></i> Detail</button>`;
            isPelabelan ? btnAction += `<a class="btn btn-outline-info btn-sm" title="Print Label" href="${base_url}/laporan/label/${lhu.penyelia_hash}" target="_blank"><i class="bi bi-printer"></i> Label</a>` : '';
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
                                <span><i class="bi bi-calendar-range"></i> Periode ${permohonan.periode}${permohonan.periode == 1 ? `/Zero cek` : ''}</span>
                                <div><i class="bi bi-calendar-fill"></i> ${dateFormat(permohonan.created_at, 4)}</div>
                                ${permohonan.kontrak ? `<div><i class="bi bi-file-text"></i> ${permohonan.kontrak.no_kontrak}</div>` : ''}
                            </div>
                        </div>
                        <div class="ms-auto col-auto text-center gap-1 d-flex" data-id='${lhu.penyelia_hash}' data-index='${i}'>
                            ${btnAction}
                        </div>
                        ${divInfoTugas}
                        <div class="col-md-12 collapse" id="timeline-progress-${lhu.penyelia_hash}">
                            ${timeline.elementCreate()}
                        </div>
                    </div>
                </div>
            `;
        }

        if(result.data.length == 0){
            html = `
                <div class="d-flex flex-column align-items-center py-3">
                    <img src="${base_url}/images/no_data2_color.svg" style="width:220px" alt="">
                    <span class="fw-bold mt-3 text-muted">No Data Available</span>
                </div>
            `;
        }

        $(`#list-container-lhu`).html(html);

        $(`#list-pagination-lhu`).html(createPaginationHTML(result.pagination));

        divTimelineTugas.map(d => d.render());
        $(`#list-placeholder-lhu`).hide();
        $(`#list-container-lhu`).show();
    })
}

function openProgressModal(obj){
    const index = $(obj).parent().data("index");
    ajaxGet(`api/v1/penyelia/getById/${dataPenyelia[index].penyelia_hash}`, false, result => {
        nowSelect = result.data ?? false;
        // console.log(nowSelect);
        $('#statusDone').prop('checked', true);
        // Mengambil proses jobs
        const listJobsAktif = nowSelect.penyelia_map.filter(d => listJobs.includes(d.jobs_hash) && d.status == 1);

        let htmlJobs = listJobsAktif.map((d, index) => {
            let petugasInJobs = nowSelect.petugas.find(y => y.map_hash == d.map_hash && y.user_hash == userActive.user_hash);
            if(petugasInJobs){
                return `<option value="${d.map_hash}" ${index == 0 ? 'selected' : ''}>${d.jobs.name}</option>`;
            }
        });

        $('#prosesNow').html(htmlJobs.join(''));

        setProses(listJobsAktif[0]);

        $('#dateProgress').flatpickr({
            altInput: true,
            locale: "id",
            dateFormat: "Y-m-d",
            altFormat: "j F Y",
            minDate: nowSelect.start_date,
            maxDate: nowSelect.end_date,
            defaultDate: 'today'
        });

        if(documentLhu){
            documentLhu.destroy();
            documentLhu = false;
        }

        documentLhu = new UploadComponent('upload_document', {
            camera: false,
            allowedFileExtensions: ['pdf'],
            multiple: false,
            urlUpload: {
                url: `api/v1/penyelia/uploadDokumenLhu`,
                urlDestroy: `api/v1/penyelia/destroyDokumenLhu`,
                idHash: nowSelect.penyelia_hash
            }
        });

        if(nowSelect.media) {
            documentLhu.setData(nowSelect.media);
        }

        $('#inputNote').val('');

        $('#updateProgressModal').modal('show');
    })
}

function setProses(prosesNow){
    let prosesNext = false;
    let prosesPrev = false;
    if(!prosesNow.point_jobs){
        prosesPrev = nowSelect.penyelia_map.find(d => d.order == (prosesNow.order - 1));
        prosesNext = nowSelect.penyelia_map.find(d => d.order == (prosesNow.order + 1));
    } else {
        prosesPrev = nowSelect.penyelia_map.find(d => d.order == (prosesNow.order - 1) && d.point_jobs);
        prosesNext = nowSelect.penyelia_map.find(d => d.order == (prosesNow.order + 1) && d.point_jobs);
    }

    !prosesPrev ? $('#divReturnProgress').hide() : null;
    prosesNow.jobs.upload_doc ? $('#divUploadDocLhu').show() : $('#divUploadDocLhu').hide();

    nowSelect.prosesNow = prosesNow;
    nowSelect.prosesPrev = prosesPrev;
    nowSelect.prosesNext = prosesNext;

    $('#prosesNext').val(prosesNext?.jobs?.name ?? "Finish");
}

function simpanProgress(obj){
    let note = $('#inputNote').val();
    let sProgress = $(`[name="statusProgress"]:checked`).val();
    let nextJobs = sProgress == 'done' ? (nowSelect?.prosesNext?.map_hash ?? 3) : nowSelect?.prosesPrev?.map_hash;
    let nowJobs = nowSelect?.prosesNow?.map_hash;

    if(note == ''){
        return Swal.fire({
            icon: "warning",
            text: 'Tolong masukan note!',
        });
    }
    if(nowSelect?.prosesNow.jobs.upload_doc){
        const document = documentLhu.getData();
        if(document.length == 0){
            return Swal.fire({
                icon: "warning",
                text: 'Tolong upload dokumen!',
            });
        }
    }
    const form = new FormData();
    form.append('idPenyelia', nowSelect?.penyelia_hash);
    form.append('nextJobs', nextJobs);
    form.append('nowJobs', nowJobs);
    form.append('note', note);
    form.append('sProgress', sProgress);

    spinner('show', $(obj));
    ajaxPost(`api/v1/penyelia/actionJobProses`, form, result => {
        spinner('hide', $(obj));
        if(result.meta.code == 200){
            Swal.fire({
                icon: "success",
                text: 'Progress berhasil diupdate',
            });
            $('#updateProgressModal').modal('hide');
            loadData();
        }else{
            Swal.fire({
                icon: "error",
                text: result.data.msg,
            });
        }
    }, error => {
        spinner('hide', $(obj));
    });
}

function reload(){
    loadData();
}

function showDetail(obj){
    const id = $(obj).parent().data("id");
    detail.show(`api/v1/penyelia/getById/${id}`);
}

function clearFilter(){
    filterComp.clear();
    loadData();
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
