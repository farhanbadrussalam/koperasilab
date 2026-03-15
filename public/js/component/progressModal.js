let nowSelect = false;
let documentLhu = false;
$(function () {
    $('#updateProgressModal').on('hide.bs.modal', () => {
        nowSelect = false;
    });

    $(`[name="statusProgress"]`).on('click', obj => {
        if(obj.target.value == 'return') {
            $('#divUploadDocLhu').hide();
            $('#prosesNext').val(nowSelect.prosesPrev.jobs.name);
        } else {
            // const prosesNow = nowSelect.penyelia_map.filter(d => listJobs.includes(d.jobs_hash) && d.status == 1);
            nowSelect.prosesNow.jobs.upload_doc ? $('#divUploadDocLhu').show() : $('#divUploadDocLhu').hide();
            $('#prosesNext').val(nowSelect.prosesNext?.jobs?.name ?? "Finish");
        }
    });

    $('#prosesNow').on('change', obj => {
        const prosesNow = nowSelect.penyelia_map.find(d => d.map_hash == obj.target.value);
        setProses(prosesNow);
    });
})
/**
 * Open modal for updating progress of penyelia
 * If obj is passed, it will get the idPenyelia from the closest tr element
 * If idPenyelia is passed, it will get the data from the API by the idPenyelia
 * @param {object|boolean} obj - The element that triggered the modal
 * @param {string|boolean} idPenyelia - The id of the penyelia
 */
function openProgressModal(obj = false, idPenyelia = false){
    if (obj) {
        const index = $(obj).closest('tr').data("index") ?? $(obj).parent().parent().data("index");
        idPenyelia = dataPenyelia[index].penyelia_hash;
    }

    ajaxGet(`api/v1/penyelia/getById/${idPenyelia}`, false, result => {
        nowSelect = result.data ?? false;
        if (!nowSelect) return;

        $('#statusDone').prop('checked', true);

        renderJobsSelection(nowSelect);
        initDatePicker(nowSelect);
        renderTldList(nowSelect);
        initUploadDocument(nowSelect);

        $('#inputNote').val('');
        $('#updateProgressModal').modal('show');
    })
}

/**
 * Render list of jobs selection based on the data provided
 * The jobs are filtered based on the jobs that are currently assigned to the user
 * The first job in the list is selected by default
 * @param {object} data - The penyelia data
 */
function renderJobsSelection(data) {
    let listJobsAktif = data.penyelia_map.filter(d => listJobs.includes(d.jobs_hash) && d.status == 1);

    // Urutkan pekerjaan: yang tidak memiliki point_jobs di atas, yang memiliki di bawah,
    // lalu urutkan berdasarkan 'order' di dalam masing-masing grup.
    listJobsAktif.sort((a, b) => (!!a.point_jobs - !!b.point_jobs) || (a.order - b.order));

    console.log(listJobsAktif);
    // Filter jobs assigned to current user
    const userJobs = listJobsAktif.filter(d =>
        data.petugas.some(p => p.map_hash == d.map_hash && p.user_hash == userActive.user_hash)
    );

    if (userJobs.length > 0) {
        setProses(userJobs[0]);
    }

    const htmlJobs = userJobs.map((d, index) =>
        `<option value="${d.map_hash}" ${index === 0 ? 'selected' : ''}>${d.jobs.name}</option>`
    ).join('');

    $('#prosesNow').html(htmlJobs);
}

/**
 * Initialize date picker for progress date input
 * The date picker is limited to the start date and end date of the current penyelia
 * The default date is set to today
 * @param {object} data - The penyelia data
 */
function initDatePicker(data) {
    $('#dateProgress').flatpickr({
        altInput: true,
        locale: "id",
        dateFormat: "Y-m-d",
        altFormat: "j F Y",
        minDate: data.start_date,
        maxDate: data.end_date,
        defaultDate: 'today'
    });
}

/**
 * Render list of TLDs based on the data provided
 * The TLDs are filtered based on the jobs that are currently assigned to the user
 * The first TLD in the list is selected by default
 * @param {object} data - The penyelia data
 */
function renderTldList(data) {
    let htmlRincianTld = '';
    let isPeriodOne = data.periodenow.count_tld == 1 || data.periodenow.periode == 0;
    let periodenow = data.periodenow.periode == 0 ? 1 : data.periodenow.periode;
    const details = data.permohonan.kontrak.kontrak_detail.filter(d => {
        return isPeriodOne ? d.periode_tld_1 == periodenow : d.periode_tld_2 == periodenow;
    });
    // const details = data.permohonan?.permohonan_detail.filter(d => d.type == 'baru');
    // const details = data.permohonan?.kontrak?.kontrak_detail || [];
    // const isPeriodOne = data.periodenow.count_tld == 1 || data.periodenow.periode == 0;

    details.forEach(detail => {
        const tld = isPeriodOne ? detail.tld_1 : detail.tld_2;
        if (!tld) return;
        // const tld = detail.tld;

        const status = isPeriodOne ? detail.status_tld_1 : detail.status_tld_2;
        // const status = detail.status;
        const inPenyimpanan = status == 5;

        const cardHtml = `
            <div class="card card-default mb-1">
                <div class="card-body d-flex justify-content-between py-2">
                    <span>${tld.no_seri_tld}</span>
                    <div>
                        <small class="text-${inPenyimpanan ? 'secondary' : 'success'}">
                            ${inPenyimpanan ? 'Penyimpanan' : 'Aktif'}
                        </small>
                    </div>
                </div>
            </div>
        `;

        if (!detail.pengguna) {
            htmlRincianTld = cardHtml + htmlRincianTld;
        } else {
            htmlRincianTld += cardHtml;
        }
    });

    if (!htmlRincianTld) {
        htmlRincianTld = `
            <div class="card card-default mb-1">
                <div class="card-body text-center py-2">
                    <span>Tidak ada TLD</span>
                </div>
            </div>
        `;
    }

    $('#detailTld').html(htmlRincianTld);
}

/**
 * Initialize the upload document component
 * @param {object} data - The penyelia data
 */
function initUploadDocument(data) {
    if (documentLhu) {
        documentLhu.destroy();
        documentLhu = false;
    }

    documentLhu = new UploadComponent('upload_document', {
        camera: false,
        allowedFileExtensions: ['pdf'],
        multiple: true,
        urlUpload: {
            url: `api/v1/penyelia/uploadDokumenLhu`,
            urlDestroy: `api/v1/penyelia/destroyDokumenLhu`,
            idHash: data.penyelia_hash
        }
    });

    if (data.media && data.media.length > 0) {
        documentLhu.setData(data.media);
    }
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
    form.append('periodeNow',nowSelect.periodenow?.periode_hash);
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
