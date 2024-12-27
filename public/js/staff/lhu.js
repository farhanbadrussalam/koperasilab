let dataPenyelia = [];
let nowSelect = false;
$(function () {
    loadData();

    $('#updateProgressModal').on('hide.bs.modal', () => {
        nowSelect = false;
    });

    $(`[name="statusProgress"]`).on('click', obj => {
        if(obj.target.value == 'return') {
            $('#prosesNext').val(nowSelect.prosesPrev.jobs.name);
        } else {
            $('#prosesNext').val(nowSelect.prosesNext.jobs.name);
        }
    });

    setDropify("init", "#upload_document", {
        allowedFileExtensions: ["pdf"]
    })
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
        for (const [i, lhu] of result.data.entries()) {
            const permohonan = lhu.permohonan;
            let periode = permohonan.periode_pemakaian;
            let btnAction = '<button class="btn btn-outline-primary btn-sm" title="Verifikasi" onclick="openProgressModal(this)"><i class="bi bi-check2-circle"></i> update progress</button>';

            let divInfoTugas = `
                <div class="col-md-12">
                    <div class="rounded bg-secondary-subtle p-2 text-body-secondary d-flex justify-content-between">
                        <span>Status : ${statusFormat('penyelia', lhu.status)}</span>
                    </div>
                </div>
            `;

            let htmlPeriode = `
                <div>${periode?.length ?? '0'} Periode</div>
            `;
            if(permohonan.periode){
                htmlPeriode = `<div>Periode ${permohonan.periode}</div>`;
            }

            html += `
                <div class="card mb-2">
                    <div class="card-body row align-items-center">
                        <div class="col-12 col-md-3">
                            <div class="title">Layanan ${permohonan.layanan_jasa.nama_layanan}</div>
                            <small class="subdesc text-body-secondary fw-light lh-sm">
                                <div>${permohonan.jenis_tld.name}</div>
                                ${htmlPeriode}
                                <div>Created : ${dateFormat(permohonan.created_at, 4)}</div>
                            </small>
                        </div>
                        <div class="col-6 col-md-3 my-3 text-end text-md-start">
                            <div>${permohonan.tipe_kontrak}</div>
                            <small class="subdesc text-body-secondary fw-light lh-sm">${permohonan.kontrak.no_kontrak}</small>
                        </div>
                        <div class="col-6 col-md-4 text-center">
                            <div class="fw-bolder">Start date</div>
                            <div>${dateFormat(lhu.start_date, 4)}</div>
                            <div class="fw-bolder">End date</div>
                            <div>${dateFormat(lhu.end_date, 4)}</div>
                        </div>
                        <div class="col-6 col-md-2 text-center" data-id='${lhu.penyelia_hash}' data-index='${i}' data-surattugas='${lhu.no_surat_tugas}'>
                            ${btnAction}
                        </div>
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

        $(`#list-placeholder-lhu`).hide();
        $(`#list-container-lhu`).show();
    })
}

function openProgressModal(obj){
    const index = $(obj).parent().data("index");
    nowSelect = dataPenyelia[index] ?? false;

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
        defaultDate: 'today'
    });
    prosesNow.jobs.upload_doc ? $('#divUploadDocLhu').show() : $('#divUploadDocLhu').hide();

    nowSelect.prosesNow = prosesNow;
    nowSelect.prosesPrev = prosesPrev;
    nowSelect.prosesNext = prosesNext;

    $('#prosesNow').val(prosesNow.jobs.name);
    $('#prosesNext').val(prosesNext?.jobs?.name ?? "Finish");
    $('#inputNote').val('');
    setDropify('reset', "#upload_document", {
        allowedFileExtensions: ["pdf"]
    });
    
    $('#updateProgressModal').modal('show');
}

function simpanProgress(obj){
    let note = $('#inputNote').val();
    let sProgress = $(`[name="statusProgress"]:checked`).val();
    let status = sProgress == 'done' ? (nowSelect?.prosesNext?.jobs?.status ?? 3) : nowSelect?.prosesPrev?.jobs?.status;
    const document = $('#upload_document')[0].files[0];

    if(note == ''){
        return Swal.fire({
            icon: "warning",
            text: 'Tolong masukan note!',
        });
    }
    const form = new FormData();
    form.append('idPenyelia', nowSelect?.penyelia_hash);
    form.append('status', status);
    form.append('note', note);
    nowSelect?.prosesNow.jobs.upload_doc ? form.append('document', document) : false;
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