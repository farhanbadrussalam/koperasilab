let filterComp = false;
let signaturePad = false;
let dataPenyelia = false;
$(function () {
    loadData();

    detail = new Detail({
        jenis: 'surattugas',
        activeTab: 'proses',
        tab: {
            proses: true,
            log: true
        },
    });

    filterComp = new FilterComponent('list-filter', {
        jenis: 'penyelia',
        filter : {
            status : true,
            jenis_tld : true,
            jenis_layanan : true,
            no_kontrak : true,
            perusahaan: true
        }
    })

    // SETUP FILTER
    filterComp.on('filter.change', () => loadData());

    // SIGNATURE
    const canvas = document.getElementById('content-ttd');
    signaturePad = new SignatureSelect(canvas, {
        inputId: 'managerValid',
        label: 'Nyatakan valid & Benar',
        placeholder: 'Menunggu validasi petugas...',
        signerUser: userActive
    });

    loadEvent();
});

function loadEvent() {
    $('#btnApprove').on('click', (obj) => {
        let [ttdValue, ttdBy] = signaturePad.getValue();
        if(!ttdValue) {
            return Swal.fire({
                icon: "warning",
                text: "Harap berikan tanda tangan terlebih dahulu.",
            });
        }
        spinner('show', $(obj.target));
        const idPenyelia = $('#txt_id_penyelia').val();
        const params = new FormData();
        params.append('ttd', ttdValue);
        params.append('ttd_by', ttdBy);
        params.append('idPenyelia', idPenyelia);
        params.append('type', 'approve');
        ajaxPost('api/v1/penyelia/approvePengujian', params, result => {
            if(result.meta.code == 200) {
                spinner('hide', $(obj.target));
                $('#verify_modal_surat_pengujian').modal('hide');
                loadData();
            }
        }, error => {
            spinner('hide', $(obj.target));
        });
    });

    $('#btnDecline').on('click', (obj) => {
        let [ttdValue, ttdBy] = signaturePad.getValue();
        if(!ttdValue) {
            return Swal.fire({
                icon: "warning",
                text: "Harap berikan tanda tangan terlebih dahulu.",
            });
        }
        spinner('show', $(obj.target));
        const idPenyelia = $('#txt_id_penyelia').val();
        const params = new FormData();
        params.append('ttd', ttdValue);
        params.append('ttd_by', ttdBy);
        params.append('idPenyelia', idPenyelia);
        params.append('type', 'decline');
        ajaxPost('api/v1/penyelia/approvePengujian', params, result => {
            if(result.meta.code == 200) {
                spinner('hide', $(obj.target));
                $('#verify_modal_surat_pengujian').modal('hide');
                loadData();
            }
        }, error => {
            spinner('hide', $(obj.target));
        });
    });
}

function loadData(page=1) {
    let params = {
        limit: 10,
        page: page,
        menu: 'ttd-surat',
        filter: {}
    };

    let filterValue = filterComp && filterComp.getAllValue();
    filterValue.status && (params.filter.status = filterValue.status);
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

    $(`#list-placeholder`).show();
    $(`#list-container`).hide();
    ajaxGet(`api/v1/penyelia/list`, params, result => {
        let html = '';
        const divTimelineTugas = [];
        dataPenyelia = result.data;
        for (const [i, lhu] of result.data.entries()) {
            const permohonan = lhu.permohonan;
            let btnAction = `
                <li>
                    <a class="dropdown-item small cursor-pointer" title="Show detail" onclick="showDetail(this)">
                        <i class="bi bi-info-circle me-2"></i> Detail
                    </a>
                </li>
            `;
            let btnAction2 = ``;

            if(lhu.status == 2) {
                btnAction2 += `<a class="btn btn-outline-primary btn-sm" title="Verifikasi" href="${base_url}/manager/surat_tugas/v/${lhu.penyelia_hash}"><i class="bi bi-check2-circle"></i> Verifikasi</a>`;
            } else if (lhu.status == 6) {
                btnAction2 += `
                    <button class="btn btn-outline-primary btn-sm" title="Verifikasi" onclick="verifikasiPengujian(this)">
                        <i class="bi bi-check2-circle"></i> Verifikasi Pengujian
                    </button>
                `;
            }else{
                btnAction += `
                    <li>
                        <a class="dropdown-item small cursor-pointer" href="${base_url}/manager/surat_tugas/s/${lhu.penyelia_hash}">
                            <i class="bi bi-eye me-2"></i> Lihat Surat
                        </a>
                    </li>
                `;
            }

            let badgeClass = 'bg-primary-subtle';
            if(permohonan.tipe_kontrak == 'kontrak lama') {
                badgeClass = 'bg-success-subtle';
            }

            let divInfoTugas = ``;
            if(lhu.start_date && lhu.end_date) {
                divInfoTugas = `
                    <div class="col-md-12 mt-2 fs-7">
                        <div class="rounded bg-secondary-subtle ps-2 text-body-secondary d-flex justify-content-between align-items-center">
                            <span>Durasi pelaksanaan layanan ${dateFormat(lhu.start_date, 4)} s/d ${dateFormat(lhu.end_date, 4)}</span>
                            <a class="py-1 px-2 text-decoration-none border rounded-2" href="#timeline-progress-${lhu.penyelia_hash}" data-bs-toggle="collapse"
                            onclick="showHideProgress(this)">Lihat Progress LAB</a>
                        </div>
                    </div>
                `;
            }

            const timeline = new Timeline({
                timeline: lhu.penyelia_map,
                status: lhu.status,
                id: lhu.penyelia_hash
            });
            divTimelineTugas.push(timeline);

            // status jobs yang aktif
            let htmlStatus = statusFormat('penyelia', lhu.status);
            if(lhu.status == 10) {
                const aktifJobs = lhu.penyelia_map.filter(d => d.status == 1);

                aktifJobs.map(d => {
                    htmlStatus += statusFormat('penyelia', d.jobs.status);
                })
            }

            const params = {
                tipeKontrak: permohonan.tipe_kontrak,
                jenisLayananParent: permohonan.jenis_layanan_parent.name,
                jenisLayanan: permohonan.jenis_layanan.name,
                format: 'penyelia',
                status: lhu.status,
                jenisTld: permohonan.jenis_tld?.name ?? '-',
                namaLayanan: permohonan.layanan_jasa?.nama_layanan,
                periode: permohonan.periode,
                created_at: permohonan.created_at,
                kontrak: permohonan.kontrak?.no_kontrak,
                id: lhu.penyelia_hash,
                is_have_tld: permohonan.is_have_tld,
                is_zerocek: permohonan.is_zerocek,
                note: '',
                pelanggan: permohonan.pelanggan.name,
                divInfoTugas: divInfoTugas,
                divTimelineTugas: timeline
            }

            html += cardComponent(params, {btnMenuAction: btnAction, btnAction: btnAction2});
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

        $(`#list-pagination`).html(createPaginationHTML(result.pagination));
        divTimelineTugas.map(d => d.render());
        $(`#list-placeholder`).hide();
        $(`#list-container`).show();
    });
}

function reload() {
    loadData();
}
function showDetail(obj){
    const idPenyelia = $(obj).parent().parent().data("id");
    detail.show(`api/v1/penyelia/getById/${idPenyelia}`);
}
function clearFilter(){}
function showHideProgress(obj){
    const collapse = obj;
    if(!collapse.classList.contains('show')) {
        collapse.innerText = 'Lebih sedikit';
    } else {
        collapse.innerText = 'Lihat Progress LAB';
    }
    collapse.classList.toggle('show');
}

function verifikasiPengujian(obj){
    const idPenyelia = $(obj).parent().parent().data("id");
    let find = dataPenyelia.find(d => d.penyelia_hash == idPenyelia);

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
    let dataSurat = find.permohonan.dokumen.find(d => d.doc_template?.name == 'SuratPengujian');
    let template = find.template_surat.find(d => d.name == 'SuratPengujian');

    // periode
    for (const periode of kontrak.periode) {
        let startDate = dateFormat(periode.start_date, 6);
        let endDate = dateFormat(periode.end_date, 6);

        $('#list-sample').append(`
            <div>${kontrak.jumlah_kontrol} + ${kontrak.jumlah_pengguna} ${startDate} - ${endDate}</div>
        `);
    }

    // load pertanyaan
    let htmlPertanyaan = '';
    for (const [i,pertanyaan] of template.data_pertanyaan.entries()) {
        // mengambil jawaban
        let answer = dataSurat.content_value?.alasan.find(d => d.id == pertanyaan.id_pertanyaan).answer;
        htmlPertanyaan += `
            <div class="mb-3">
                <label for="" class="mb-2">${pertanyaan.pertanyaan}</label>
                <div class="rounded border p-2 overflow-auto max-h-max">${answer ? answer : '-'}</div>
            </div>
        `;
    }

    $('#content-pertanyaan').html(htmlPertanyaan);
    $('#inputJenisPengujian').text(jenisPengujian);
    $('#inputPemilik').text(find.permohonan.pelanggan.perusahaan.nama_perusahaan);
    $('#inputAlamat').text(find.permohonan.pelanggan.perusahaan.alamat[0].alamat);
    $('#txt_id_penyelia').val(idPenyelia);
    $('#verify_modal_surat_pengujian').modal('show');
}
