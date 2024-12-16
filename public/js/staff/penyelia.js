let signaturePad = false;
let tmpPetugas = [];
let nowSelect = false;
$(function () {
    switchLoadTab(1);

    const content = document.getElementById("content-ttd");
    signaturePad = signature(content, {
        text: 'Penyelia'
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

    $('#inputStartDate').flatpickr({
        altInput: true,
        locale: "id",
        minDate: 'today',
        dateFormat: "Y-m-d",
        altFormat: "j F Y",
        onChange: (selectedDates, dateStr, instance) => {
            $('#inputEndDate').val('');
            $('#inputEndDate').removeClass('bg-secondary-subtle');
            $('#inputEndDate').attr('readonly', false);

            $('#inputEndDate').flatpickr({
                altInput: true,
                locale: "id",
                minDate: dateStr,
                dateFormat: "Y-m-d",
                altFormat: "j F Y",
            })
        }
    });

    $('#inputPetugas').select2({
        theme: "bootstrap-5",
        placeholder: "Pilih petugas",
        dropdownParent: $('#suratTugasModal'),
        minimumInputLength: 1,
        allowClear: true,
        ajax: {
          url: `${base_url}/api/v1/penyelia/listPetugas`,
          dataType: 'json',
          type: 'GET',
          delay: 250,
          headers: {
            'Authorization': `Bearer ${bearer}`,
            'Content-Type': 'application/json'
        },
          data: function(params) {
            let queryParams = {
                text: params.term
            }
            return queryParams;
          },
          processResults: function (data) {
            let items = [];
            for (const d of data) {
                let cek = tmpPetugas.find(v => v.id === d.user_hash);
                if(!cek){
                    items.push({
                        'id': d.user_hash,
                        'text': d.name,
                        'jobs': JSON.parse(d.jobs)
                    })
                }
            }
            
            return {
                results: items
            };
          }
        },
        cache: true,
        templateResult : (state) => {
            if (!state.id) return state.text;
            let $content = $(
                `
                    <div class="d-flex flex-column">
                        <div class="row">
                            <div>${state.text}</div>
                        </div>
                        <div class="text-body-secondary fs-6">${state.jobs.length > 0 ? state.jobs.join(', ') : ''}</div>
                    </div>
                `
            )

            return $content;
        },
    }).on('select2:select', function (e) {
        // Bersihkan input pencarian setelah pemilihan
        $(this).data('petugas', e.params.data)
    });
});

function switchLoadTab(menu){
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
        limit: 10,
        page: page,
        menu: menu
    };
    
    $(`#list-placeholder-${menu}`).show();
    $(`#list-container-${menu}`).hide();
    ajaxGet(`api/v1/penyelia/list`, params, result => {
        let html = '';
        for (const [i, penyelia] of result.data.entries()) {
            const permohonan = penyelia.permohonan;
            let periode = JSON.parse(permohonan.periode_pemakaian);
            let btnAction = '';
            switch (menu) {
                case 'surattugas':
                    if(penyelia.status == 1) {
                        btnAction = `<a class="btn btn-outline-primary btn-sm" title="Buat Surat Tugas" href="${base_url}/staff/penyelia/surat_tugas/c/${penyelia.penyelia_hash}"><i class="bi bi-plus"></i> Buat Surat Tugas</a>`;
                    }else if(penyelia.status == 2) {
                        btnAction = `
                            <a class="btn btn-outline-info btn-sm mb-1" href="${base_url}/staff/penyelia/surat_tugas/s/${penyelia.penyelia_hash}"><i class="bi bi-eye"></i> Show</a>
                            <a class="btn btn-outline-warning btn-sm mb-1" href="${base_url}/staff/penyelia/surat_tugas/e/${penyelia.penyelia_hash}"><i class="bi bi-pencil-square"></i> Edit</a>
                            <button class="btn btn-outline-danger btn-sm mb-1" onclick="btnDelete(this)"><i class="bi bi-trash"></i> Delete</button>
                        `;
                    }else{
                        btnAction = `
                            <a class="btn btn-outline-info btn-sm mb-1" href="${base_url}/staff/penyelia/surat_tugas/s/${penyelia.penyelia_hash}"><i class="bi bi-eye"></i> Show</a>
                        `;
                    }

                    let divInfoTugas = '';
                    if(penyelia.start_date && penyelia.end_date){
                        divInfoTugas = `
                            <div class="col-md-12">
                                <div class="rounded bg-secondary-subtle p-2 text-body-secondary d-flex justify-content-between">
                                    <span>Durasi pelaksanaan layanan ${dateFormat(penyelia.start_date, 4)} s/d ${dateFormat(penyelia.end_date, 4)}</span>
                                    <span>Status : ${statusFormat('penyelia', penyelia.status)}</span>
                                </div>
                            </div>
                        `;
                    }
                    html += `
                        <div class="card mb-2">
                            <div class="card-body row align-items-center">
                                <div class="col-12 col-md-3">
                                    <div class="title">Layanan ${permohonan.layanan_jasa.nama_layanan}</div>
                                    <small class="subdesc text-body-secondary fw-light lh-sm">
                                        <div>${permohonan.jenis_tld.name}</div>
                                        <div>Periode : ${periode.length} Bulan</div>
                                        <div>Created : ${dateFormat(permohonan.created_at, 4)}</div>
                                    </small>
                                </div>
                                <div class="col-6 col-md-2 my-3">${permohonan.jenis_layanan_parent.name}-${permohonan.jenis_layanan.name}</div>
                                <div class="col-6 col-md-2 my-3 text-end text-md-start">
                                    <div>${permohonan.tipe_kontrak}</div>
                                    <small class="subdesc text-body-secondary fw-light lh-sm">${permohonan.kontrak.no_kontrak}</small>
                                </div>
                                <div class="col-6 col-md-2">${permohonan.pelanggan.perusahaan.nama_perusahaan}</div>
                                <div class="col-6 col-md-3 text-center" data-idpenyelia='${penyelia.penyelia_hash}'>
                                    ${btnAction}
                                </div>
                                ${divInfoTugas}
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
                                        <div>Periode : ${periode.length} Bulan</div>
                                        <div>Created : ${dateFormat(permohonan.created_at, 4)}</div>
                                    </small>
                                </div>
                                <div class="col-6 col-md-3 my-3 text-end text-md-start">
                                    <div>${permohonan.tipe_kontrak}</div>
                                    <small class="subdesc text-body-secondary fw-light lh-sm">${permohonan.kontrak.no_kontrak}</small>
                                </div>
                                <div class="col-6 col-md-4 text-center">
                                    <div class="fw-bolder">Start date</div>
                                    <div>${dateFormat(penyelia.start_date, 4)}</div>
                                    <div class="fw-bolder">End date</div>
                                    <div>${dateFormat(penyelia.end_date, 4)}</div>
                                </div>
                                <div class="col-6 col-md-2 text-center" data-penyelia='${JSON.stringify(penyelia)}' data-surattugas='${penyelia.no_surat_tugas}'>
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

        $(`#list-container-${menu}`).html(html);

        $(`#list-pagination-${menu}`).html(createPaginationHTML(result.pagination));

        $(`#list-placeholder-${menu}`).hide();
        $(`#list-container-${menu}`).show();
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
    })
}

let suratTugasMode = 'create'; // 'create' or 'verify'

function openSuratTugasModal(obj, mode) {
    const penyelia = $(obj).parent().data("penyelia");
    $('#txtIdPenyelia').val(penyelia.penyelia_hash);
    loadListPetugas();
    $('#suratTugasModal').modal('show');
}

function openProgressModal(obj) {
    const penyelia = $(obj).parent().data("penyelia");
    $('#statusDone').prop('checked', true);
    nowSelect = penyelia;

    // Mengambil proses jobs 
    const prosesNow = nowSelect.penyelia_map.find(d => d.jobs.status == nowSelect.status);
    const prosesPrev = nowSelect.penyelia_map.find(d => d.order == (prosesNow.order - 1));
    
    $('#dateProgress').flatpickr({
        altInput: true,
        locale: "id",
        dateFormat: "Y-m-d",
        altFormat: "j F Y",
        defaultDate: 'today'
    });

    nowSelect.prosesNow = prosesNow;
    nowSelect.prosesPrev = prosesPrev;

    $('#prosesNow').val(prosesNow.jobs.name);
    $('#prosesNext').val('Finish');

    $('#updateProgressModal').modal('show');
}

function tambahPetugas(){
    let petugas = $('#inputPetugas').data('petugas');

    if(petugas){
        tmpPetugas.push(petugas);
        tmpPetugas = tmpPetugas.filter((value, index, self) =>
            index === self.findIndex((t) => t.id === value.id)
        );
        loadListPetugas();
        $('#inputPetugas').val(null).trigger('change');
    }
}

function closeModal() {
    signaturePad.clear();
    tmpPetugas = [];
    $('#inputStartDate').val('');
    $('#inputStartDate')[0]._flatpickr?.setDate('');

    $('#inputEndDate').val('');
    $('#inputEndDate')[0]._flatpickr?.destroy();
    $('#inputEndDate').addClass('bg-secondary-subtle');
    $('#inputEndDate').attr('readonly', true);
    $('#inputPetugas').val(null).trigger('change');
    $('#txtIdPenyelia').val('');
}

function loadListPetugas(){
    let html = '';
    tmpPetugas.forEach((data, index) => {
        html += `
            <div class="card mb-2 border-dark">
                <div class="card-body align-items-center d-flex p-2 px-2">
                    <span class="me-2">${index+1}</span>
                    <div class="d-flex flex-column me-auto">
                        <span class="subbody-medium text-submain text-truncate fw-bolder">${data.text}</span>
                        <small class="text-body-secondary fw-light">${data.jobs.length > 0 ? data.jobs.join(', ') : ''}</small>
                    </div>
                    <button class="btn btn-sm btn-outline-danger" onclick="removePetugas(${index})"><i class="bi bi-trash"></i></button>
                </div>
            </div>
        `;
    });

    if(tmpPetugas.length == 0){
        html = `
            <div class="d-flex flex-column align-items-center py-3">
                <img src="${base_url}/images/no_data2_color.svg" style="width:100px" alt="">
                <small class="fw-bold mt-3 text-muted">No Data Available</small>
            </div>
        `;
    }

    $('#list-petugas').html(html);
}

function removePetugas(index){
    tmpPetugas.splice(index, 1);
    loadListPetugas();
}

function simpanSuratTugas(obj){
    const idPenyelia = $('#txtIdPenyelia').val();
    const startDate = $('#inputStartDate').val();
    const endDate = $('#inputEndDate').val();
    const petugas = tmpPetugas.map(v => v.id);
    const signature = signaturePad.toDataURL();

    const formData = new FormData();
    formData.append('idPenyelia', idPenyelia);
    formData.append('startDate', startDate);
    formData.append('endDate', endDate);
    formData.append('status', 2);
    formData.append('petugas', JSON.stringify(petugas));
    formData.append('ttd', signature);
    formData.append('ttd_by', userActive.user_hash);

    spinner('show', $(obj));
    ajaxPost(`api/v1/penyelia/action`, formData, result => {
        spinner('hide', $(obj));
        if(result.meta.code == 200){
            Swal.fire({
                icon: "success",
                text: 'Surat tugas berhasil dibuat',
            });
            $('#suratTugasModal').modal('hide');
            loadData(1, 'surattugas');
        }else{
            Swal.fire({
                icon: "error",
                text: result.data.msg,
            });
        }
    }, error => {
        Swal.fire({
            icon: "error",
            text: 'Server error',
        });
        spinner('hide', $(obj));
        console.error(error.responseJSON.data.msg);
    });
}

function simpanProgress(obj){
    let sProgress = $(`[name="statusProgress"]:checked`).val();
    let note = $('#inputNote').val();
    let status = sProgress == 'done' ? 3 : nowSelect?.prosesPrev?.jobs?.status;
    const document = $('#upload_document')[0].files[0];

    const form = new FormData();
    form.append('idPenyelia', nowSelect?.penyelia_hash);
    form.append('status', status);
    form.append('note', note);
    form.append('document', document);

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
        Swal.fire({
            icon: "error",
            text: 'Server error',
        });
        spinner('hide', $(obj));
        console.error(error.responseJSON.data.msg);
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