let dataPenyelia = [];
let detail = false;
let filterComp = false;
let thisTab = 'progress';
$(function () {
    loadData();

    // mengambil params url
    let urlParams = new URLSearchParams(window.location.search);
    if(urlParams.has('md')) {
        let md = urlParams.get('md');
        openProgressModal(false, md);
    }

    detail = new Detail({
        jenis: 'penyelia',
        tab: {
            pengguna: true,
            tld: true,
            dokumen: true,
            log: true
        }
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

function switchLoadTab(menu){
    thisTab = menu;
    loadData(1);
}

function loadData(page = 1) {
    let params = {
        limit: 10,
        page: page,
        status: listJobs,
        filter: {},
        menu: thisTab
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
            let btnAction2 = '';
            // Mengecek array listJobs apakah ada di jobsAktive
            // Algoritma ini opsional jika dimunculkan semuanya
            /*
                let jobsAktive = lhu.penyelia_map.filter(x => x.status == 1);
                let jobsAktiveHash = jobsAktive.map(x => x.jobs_hash);
                const hasCommonValue = jobsAktiveHash.some(hash => listJobs.includes(hash));
            */

            let showPenyelia = lhu.status == 3 ? false : true;
            let divInfoTugas = `
                <div class="col-md-12 mt-2 fs-7">
                    <div class="rounded bg-secondary-subtle ps-2 text-body-secondary d-flex justify-content-between align-items-center">
                        <span>Durasi pelaksanaan layanan ${dateFormat(lhu.start_date, 4)} s/d ${dateFormat(lhu.end_date, 4)}</span>
                        <a class="py-1 px-2 text-decoration-none border rounded-2 ${showPenyelia ? 'd-none' : ''}" href="#timeline-progress-${lhu.penyelia_hash}" data-bs-toggle="collapse"
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
            let isPenyimpanan = false;
            let htmlStatus = statusFormat('penyelia', lhu.status);

            // button action
            btnAction += `
                <li>
                    <a class="dropdown-item small cursor-pointer" title="Show detail" onclick="showDetail(this)">
                        <i class="bi bi-info-circle"></i> Detail
                    </a>
                </li>
                <li>
                    <a class="dropdown-item small cursor-pointer" title="Lihat Surat Pengantar" href="${base_url}/laporan/surpeng/${lhu.permohonan.kontrak.kontrak_hash}/${lhu.permohonan.periode}" target="_blank">
                        <i class="bi bi-eye"></i> Surat Pengantar
                    </a>
                </li>
            `;
            let btnLabel = `
                <li>
                    <a class="dropdown-item small cursor-pointer" title="Print Label" href="${base_url}/laporan/label/${lhu.penyelia_hash}" target="_blank">
                        <i class="bi bi-printer"></i> Cetak Label
                    </a>
                </li>
                `;

            if(thisTab == "selesai") {
                const selesaiJobs = lhu.penyelia_map.filter(d => listJobs.includes(d.jobs_hash) && d.status == 2);
                selesaiJobs.map(d => {
                    let petugasInJobs = lhu.petugas.find(y => y.map_hash == d.map_hash && y.user_hash == userActive.user_hash);
                    if(petugasInJobs){
                        d.jobs.status == 20 ? isPelabelan = true : false;
                        let txtStatus = statusFormat('penyelia', d.jobs.status);
                        txtStatus = txtStatus.replace('Proses', 'Selesai').replace('bg-primary-subtle', 'bg-success-subtle');
                        htmlStatus += txtStatus;
                    }
                })
            } else {
                const aktifJobs = lhu.penyelia_map.filter(d => listJobs.includes(d.jobs_hash) && d.status == 1);
                aktifJobs.map(d => {
                    let petugasInJobs = lhu.petugas.find(y => y.map_hash == d.map_hash && y.user_hash == userActive.user_hash);
                    if(petugasInJobs){
                        d.jobs.status == 20 ? isPelabelan = true : false;
                        d.jobs.status == 17 ? isPenyimpanan = true : false;
                        htmlStatus += statusFormat('penyelia', d.jobs.status);
                    }
                })

                let btnUpdateProgress = `<button class="btn btn-outline-primary btn-sm" title="Verifikasi" onclick="openProgressModal(this)"><i class="bi bi-check2-circle"></i> update progress</button>`;
                let showPenyimpanan = `
                    <li>
                        <a class="dropdown-item small cursor-pointer" title="Lihat Penyimpanan" onclick="openPenyimpananModal(this)">
                            <i class="bi bi-eye"></i> Lihat penyimpanan
                        </a>
                    </li>
                `;
                if(!isPenyimpanan){
                    btnAction2 += btnUpdateProgress;
                } else {
                    let filterPeriodeNext = lhu.permohonan.kontrak.periode.filter(d => d.periode == lhu.periode + 1 && d.status == 1);
                    if(filterPeriodeNext.length > 0){
                        let reminderPeriod = isReminderPeriod(filterPeriodeNext[0].start_date, 1);

                        if(filterPeriodeNext[0].tld_in_periode && filterPeriodeNext[0].tld_in_periode[0].status == 5 || reminderPeriod){
                            btnAction2 += btnUpdateProgress;
                        } else {
                            envirotment == 'production' ? btnAction += showPenyimpanan : btnAction2 += btnUpdateProgress;
                        }
                    } else {
                        if(envirotment == 'production' && permohonan.kontrak.is_have_tld == 1 && permohonan.kontrak.jenis_layanan.name != 'Sewa') {
                            let TldPeriodeDigunakan = lhu.permohonan.kontrak.periode.find(d => d.periode == lhu.periode);
                            // mengambil periode berikutnya
                            let startDate = new Date(TldPeriodeDigunakan.end_date);
                            // awal bulan setelah startDate
                            startDate.setDate(1);
                            startDate.setMonth(startDate.getMonth() + 4);

                            let reminderPeriod = isReminderPeriod(startDate, 1);
                            if(reminderPeriod){
                                btnAction += btnUpdateProgress;
                            } else {
                                envirotment == 'production' ? btnAction += showPenyimpanan : btnAction2  += btnUpdateProgress;
                            }
                        } else {
                            envirotment == 'production' ? btnAction += showPenyimpanan : btnAction2  += btnUpdateProgress;
                        }
                    }
                }
            }
            isPelabelan ? btnAction += btnLabel : '';

            // mengambil 2 periode
            let htmlLeftTime = '';
            if(thisTab == 'progress'){
                let TldPeriodeDigunakan = lhu.permohonan.kontrak.periode.find(d => d.periode == lhu.periode + 1 && d.status == 1);
                let time = '';
                let title = '';
                if(TldPeriodeDigunakan && envirotment == 'production'){
                    if(TldPeriodeDigunakan.periode == 1){
                        time = '';
                    } else {
                        time = timeLeftUntilHMinusOneMonth(new Date(TldPeriodeDigunakan.start_date));
                        title = `Sebelum Periode ${TldPeriodeDigunakan.periode}`;
                    }
                } else if(envirotment == 'production' && permohonan.kontrak.is_have_tld == 1 && permohonan.kontrak.jenis_layanan.name != 'Sewa') {
                    TldPeriodeDigunakan = lhu.permohonan.kontrak.periode.find(d => d.periode == lhu.periode);
                    // mengambil periode berikutnya
                    let startDate = new Date(TldPeriodeDigunakan.end_date);
                    // awal bulan setelah startDate
                    startDate.setDate(1);
                    startDate.setMonth(startDate.getMonth() + 4);

                    time = timeLeftUntilHMinusOneMonth(startDate);
                    title = `Sebelum Pengembalian`;
                }
                if(time != 'Hari ini' && time != ''){
                    htmlLeftTime = `<div class="fs-6 text-body-tertiary fw-bold text-end">${time}<br><small>${title}</small></div>`;
                }
            }

            const params = {
                index: i,
                tipeKontrak: permohonan.tipe_kontrak,
                jenisLayananParent: permohonan.kontrak.jenis_layanan_parent.name,
                jenisLayanan: permohonan.kontrak.jenis_layanan.name,
                format: 'penyelia',
                statusPenyelia: htmlStatus,
                jenisTld: permohonan.jenis_tld?.name ?? '-',
                namaLayanan: permohonan.layanan_jasa?.nama_layanan,
                periode: permohonan.periode,
                created_at: permohonan.created_at,
                kontrak: permohonan.kontrak.no_kontrak,
                id: lhu.penyelia_hash,
                is_have_tld: permohonan.kontrak.is_have_tld,
                is_zerocek: permohonan.kontrak.is_zerocek,
                note: '',
                pelanggan: permohonan.pelanggan.name,
                divInfoTugas: divInfoTugas,
                divTimelineTugas: timeline,
                htmlLeftTime: htmlLeftTime,
                status: lhu.status,
                perusahaan: permohonan.pelanggan.perusahaan.nama_perusahaan
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

        $(`#list-container-lhu`).html(html);

        $(`#list-pagination-lhu`).html(createPaginationHTML(result.pagination));

        divTimelineTugas.map(d => d.render());
        $(`#list-placeholder-lhu`).hide();
        $(`#list-container-lhu`).show();
    })
}

function reload(){
    loadData();
}

function showDetail(obj){
    const id = $(obj).parent().parent().data("id");
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
