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
function openProgressModal(obj){
    const index = $(obj).parent().data("index");
    ajaxGet(`api/v1/penyelia/getById/${dataPenyelia[index].penyelia_hash}`, false, result => {
        nowSelect = result.data ?? false;
        $('#statusDone').prop('checked', true);
        // Mengambil proses jobs
        const listJobsAktif = nowSelect.penyelia_map.filter(d => listJobs.includes(d.jobs_hash) && d.status == 1);

        let idxPetugas = 0;
        let htmlJobs = listJobsAktif.map((d, index) => {
            let petugasInJobs = nowSelect.petugas.find(y => y.map_hash == d.map_hash && y.user_hash == userActive.user_hash);

            if(petugasInJobs){
                if(idxPetugas == 0){
                    setProses(d);
                }
                idxPetugas++;
                return `<option value="${d.map_hash}" ${index == 0 ? 'selected' : ''}>${d.jobs.name}</option>`;
            }
        });

        $('#prosesNow').html(htmlJobs.join(''));

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

        // add rincian TLD
        let htmlRincianTld = '';
        for (const detail of nowSelect.permohonan?.kontrak?.rincian_list_tld) {
            let html = ``;
            let inPenyimpanan = detail.status == 5 ? true : false;
            for (const TLD of detail.tld) {
                html += `
                    <div class="card card-default mb-1">
                        <div class="card-body d-flex justify-content-between py-2">
                            <span>${TLD.no_seri_tld}</span>
                            <div class="">
                                <small class="text-${inPenyimpanan ? 'secondary' : 'success'}">${inPenyimpanan ? 'Penyimpanan' : 'Aktif'}</small>
                            </div>
                        </div>
                    </div>
                `;
            }

            if(!detail.pengguna){
                htmlRincianTld = html + htmlRincianTld;
            } else {
                htmlRincianTld += html;
            }
        }

        if(htmlRincianTld == ''){
            htmlRincianTld = `
                <div class="card card-default mb-1">
                    <div class="card-body text-center py-2">
                        <span>Tidak ada TLD</span>
                    </div>
                </div>
            `;
        }

        $('#detailTld').html(htmlRincianTld);

        documentLhu = new UploadComponent('upload_document', {
            camera: false,
            allowedFileExtensions: ['pdf'],
            multiple: true,
            urlUpload: {
                url: `api/v1/penyelia/uploadDokumenLhu`,
                urlDestroy: `api/v1/penyelia/destroyDokumenLhu`,
                idHash: nowSelect.penyelia_hash
            }
        });

        if(nowSelect.media.length > 0){
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
