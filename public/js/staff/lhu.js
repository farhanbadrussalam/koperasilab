let dataPenyelia = [];
let nowSelect = false;
let detail = false;
let documentLhu = false;
$(function () {
    loadData();

    detail = new Detail({
        jenis: 'penyelia',
        tab: {
            dokumen: true,
            log: true
        }
    });

    $('#updateProgressModal').on('hide.bs.modal', () => {
        nowSelect = false;
    });

    $(`[name="statusProgress"]`).on('click', obj => {
        if(obj.target.value == 'return') {
            $('#divUploadDocLhu').hide();
            $('#prosesNext').val(nowSelect.prosesPrev.jobs.name);
        } else {
            $('#divUploadDocLhu').show();
            $('#prosesNext').val(nowSelect.prosesNext.jobs.name);
        }
    });
});

function loadData(page = 1) {
    let params = {
        limit: 10,
        page: page,
        status: listJobs
    };

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
            
            if(listJobs.includes(lhu.status_hash)) {
                btnAction = `<button class="btn btn-outline-primary btn-sm" title="Verifikasi" onclick="openProgressModal(this)"><i class="bi bi-check2-circle"></i> update progress</button>`;
            } else {
                btnAction = `<button class="btn btn-sm btn-outline-secondary" title="Show detail" onclick="showDetail(this)"><i class="bi bi-info-circle"></i> Detail</button>`;
            }

            let divInfoTugas = `
                <div class="col-md-12">
                    <div class="rounded bg-secondary-subtle p-2 text-body-secondary d-flex justify-content-between">
                        <span class="fs-7">Durasi pelaksanaan layanan ${dateFormat(lhu.start_date, 4)} s/d ${dateFormat(lhu.end_date, 4)}</span>
                        <span>Status : ${statusFormat('penyelia', lhu.status)}</span>
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

            html += `
                <div class="card mb-2">
                    <div class="card-body row align-items-center">
                        <div class="col-12 col-md-4">
                            <div class="title">Layanan ${permohonan.layanan_jasa.nama_layanan}</div>
                            <small class="subdesc text-body-secondary fw-light lh-sm">
                                <div>${permohonan.jenis_tld.name}</div>
                                ${htmlPeriode}
                                <div>Created : ${dateFormat(permohonan.created_at, 4)}</div>
                            </small>
                        </div>
                        <div class="col-6 col-md-6 my-3 text-end text-md-start">
                            <div>${permohonan.tipe_kontrak}</div>
                            <small class="subdesc text-body-secondary fw-light lh-sm">${permohonan.kontrak?.no_kontrak ?? ''}</small>
                        </div>
                        <div class="col-6 col-md-2 text-center" data-id='${lhu.penyelia_hash}' data-index='${i}' data-surattugas='${lhu.no_surat_tugas}'>
                            ${btnAction}
                        </div>
                        ${timeline.elementCreate()}
                        ${divInfoTugas}
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
        
        $('#statusDone').prop('checked', true);
        // Mengambil proses jobs 
        const prosesNow = nowSelect.penyelia_map.find(d => d.jobs.status == nowSelect.status);
        const prosesPrev = nowSelect.penyelia_map.find(d => d.order == (prosesNow.order - 1));
        const prosesNext = nowSelect.penyelia_map.find(d => d.order == (prosesNow.order + 1));
    
        !prosesPrev ? $('#divReturnProgress').hide() : null;
    
        $('#dateProgress').flatpickr({
            altInput: true,
            locale: "id",
            dateFormat: "Y-m-d",
            altFormat: "j F Y",
            minDate: nowSelect.start_date,
            maxDate: nowSelect.end_date,
            defaultDate: 'today'
        });
        prosesNow.jobs.upload_doc ? $('#divUploadDocLhu').show() : $('#divUploadDocLhu').hide();
    
        nowSelect.prosesNow = prosesNow;
        nowSelect.prosesPrev = prosesPrev;
        nowSelect.prosesNext = prosesNext;
    
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
    
        $('#prosesNow').val(prosesNow.jobs.name);
        $('#prosesNext').val(prosesNext?.jobs?.name ?? "Finish");
        $('#inputNote').val('');
        
        $('#updateProgressModal').modal('show');
    })
}

function simpanProgress(obj){
    let note = $('#inputNote').val();
    let sProgress = $(`[name="statusProgress"]:checked`).val();
    let status = sProgress == 'done' ? (nowSelect?.prosesNext?.jobs?.status ?? 3) : nowSelect?.prosesPrev?.jobs?.status;

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
    form.append('status', status);
    form.append('note', note);
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