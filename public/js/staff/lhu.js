let dataPenyelia = [];
let detail = false;
let filterComp = false;
let thisTab = 'progress';
let selectedItems = [];
let selectedDataMap = {};
let modalDoc = new ModalDocument();
$(function () {
    loadData();

    // mengambil params url
    let urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('md')) {
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
        filter: {
            jenis_tld: true,
            jenis_layanan: true,
            no_kontrak: true,
            perusahaan: true,
            date_range: true
        }
    });

    // SETUP FILTER
    filterComp.on('filter.change', () => loadData());

    $('#list-pagination-lhu').on('click', 'a', function (e) {
        e.preventDefault();
        const pageno = e.target.dataset.page;

        loadData(pageno);
    });
});

function switchLoadTab(menu) {
    thisTab = menu;
    selectedItems = [];
    selectedDataMap = {};
    loadData(1);
}

function loadData(page = 1) {
    let params = {
        limit: 5,
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

    if (Object.keys(params.filter).length > 0) {
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
            selectedDataMap[lhu.penyelia_hash] = lhu;
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

            const timeline = new Timeline({
                timeline: lhu.penyelia_map,
                status: lhu.status,
                id: lhu.penyelia_hash,
                startDate: lhu.start_date,
                endDate: lhu.end_date
            });
            divTimelineTugas.push(timeline);

            // let htmlPeriode = `
            //     <div>${periode?.length ?? '0'} Periode</div>
            // `;
            // if(permohonan.periode){
            //     htmlPeriode = `<div>Periode ${permohonan.periode}</div>`;
            // }

            // status jobs yang aktif
            let isPelabelan = false;
            let isPenyimpanan = false;
            let canUpdateProgress = false;
            let isUploadDoc = false;
            let htmlStatus = statusFormat('penyelia', lhu.status);
            const permohonan_periode = permohonan.periode == 0 ? 1 : permohonan.periode;

            // button action
            btnAction += `
                <li>
                    <a class="dropdown-item small cursor-pointer" title="Show detail" onclick="showDetail(this)">
                        <i class="bi bi-info-circle"></i> Detail
                    </a>
                </li>
                <li>
                    <button class="dropdown-item small cursor-pointer" title="Lihat Surat Pengantar"
                        data-url="laporan/surpeng/${permohonan.kontrak.kontrak_hash}/${permohonan_periode}"
                        data-title="Surat Pengantar ${permohonan.kontrak.no_kontrak} - Periode ${permohonan.periode == 0 ? 'zero check' : permohonan.periode}"
                        onclick="btnShowDoc(this)">
                        <i class="bi bi-eye"></i> Surat Pengantar
                    </button>
                </li>
            `;
            let btnLabel = `
                <li>
                    <button class="dropdown-item small cursor-pointer" title="Print Label"
                        data-url="laporan/label/${lhu.penyelia_hash}"
                        data-title="Label ${permohonan.kontrak.no_kontrak} - Periode ${permohonan.periode == 0 ? 'zero check' : permohonan.periode}"
                        onclick="btnShowDoc(this)">
                        <i class="bi bi-printer"></i> Cetak Label
                    </button>
                </li>
                `;

            if (thisTab == "selesai") {
                const selesaiJobs = lhu.penyelia_map.filter(d => listJobs.includes(d.jobs_hash) && d.status == 2);
                selesaiJobs.map(d => {
                    let petugasInJobs = lhu.petugas.find(y => y.map_hash == d.map_hash && y.user_hash == userActive.user_hash);
                    if (petugasInJobs) {
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
                    if (petugasInJobs) {
                        d.jobs.status == 20 ? isPelabelan = true : false;
                        d.jobs.status == 17 ? isPenyimpanan = true : false;
                        d.jobs.upload_doc == 1 ? isUploadDoc = true : false;
                        htmlStatus += statusFormat('penyelia', d.jobs.status);
                    }
                })

                // <input class="form-check-input check-lhu cursor-pointer shadow-sm border-secondary-subtle" type="checkbox" value="${lhu.penyelia_hash}" data-index="${i}" style="width: 1.3em; height: 1.3em;">
                let btnUpdateProgress = `
                    <button class="btn btn-outline-primary btn-sm" title="Verifikasi" onclick="openProgressModal(this)">
                        <i class="bi bi-check2-circle"></i> progress
                    </button>
                `;
                let showPenyimpanan = `
                    <li>
                        <a class="dropdown-item small cursor-pointer" title="Lihat Penyimpanan" onclick="openPenyimpananModal(this)">
                            <i class="bi bi-eye"></i> Lihat penyimpanan
                        </a>
                    </li>
                `;
                if (!isPenyimpanan) {
                    btnAction2 += btnUpdateProgress;
                    canUpdateProgress = true;
                } else {
                    let filterPeriodeNext = lhu.permohonan.kontrak.periode.filter(d => d.periode == lhu.periode + 1 && d.status == 1);
                    if (filterPeriodeNext.length > 0) {
                        let reminderPeriod = isReminderPeriod(filterPeriodeNext[0].start_date, 1);

                        if (filterPeriodeNext[0].tld_in_periode && filterPeriodeNext[0].tld_in_periode[0].status == 5 || reminderPeriod) {
                            btnAction2 += btnUpdateProgress;
                            canUpdateProgress = true;
                        } else {
                            if (envirotment == 'production') {
                                btnAction += showPenyimpanan;
                            } else {
                                btnAction2 += btnUpdateProgress;
                                canUpdateProgress = true;
                            }
                        }
                    } else {
                        if (envirotment == 'production' && permohonan.kontrak.is_have_tld == 1 && permohonan.kontrak.jenis_layanan.name != 'Sewa') {
                            let TldPeriodeDigunakan = lhu.permohonan.kontrak.periode.find(d => d.periode == lhu.periode);
                            // mengambil periode berikutnya
                            let startDate = new Date(TldPeriodeDigunakan.end_date);
                            // awal bulan setelah startDate
                            startDate.setDate(1);
                            startDate.setMonth(startDate.getMonth() + 4);

                            let reminderPeriod = isReminderPeriod(startDate, 1);
                            if (reminderPeriod) {
                                btnAction += btnUpdateProgress;
                                canUpdateProgress = true;
                            } else {
                                if (envirotment == 'production') {
                                    btnAction += showPenyimpanan;
                                } else {
                                    btnAction2 += btnUpdateProgress;
                                    canUpdateProgress = true;
                                }
                            }
                        } else {
                            if (envirotment == 'production') {
                                btnAction += showPenyimpanan;
                            } else {
                                btnAction2 += btnUpdateProgress;
                                canUpdateProgress = true;
                            }
                        }
                    }
                }
            }
            isPelabelan ? btnAction += btnLabel : '';

            let htmlLeftTime = '';
            if (thisTab == 'progress') {
                let TldPeriodeDigunakan = lhu.permohonan.kontrak.periode.find(d => d.periode == lhu.periode + 1 && d.status == 1);
                let time = '';
                let title = '';

                if (TldPeriodeDigunakan) {
                    if (TldPeriodeDigunakan.periode != 1) {
                        time = timeLeftUntilHMinusOneMonth(new Date(TldPeriodeDigunakan.start_date));
                        title = `Sebelum Periode ${TldPeriodeDigunakan.periode}`;
                    }
                } else if (permohonan.kontrak.is_have_tld == 1 && permohonan.kontrak.jenis_layanan.name != 'Sewa') {
                    TldPeriodeDigunakan = lhu.permohonan.kontrak.periode.find(d => d.periode == lhu.periode);
                    if (TldPeriodeDigunakan) {
                        // mengambil periode berikutnya
                        let startDate = new Date(TldPeriodeDigunakan.end_date);
                        // awal bulan setelah startDate
                        startDate.setDate(1);
                        startDate.setMonth(startDate.getMonth() + 4);

                        time = timeLeftUntilHMinusOneMonth(startDate);
                        title = `Sebelum Pengembalian`;
                    }
                }

                if (time !== '') {
                    let badgeClass = 'bg-primary-subtle text-primary border-primary-subtle';
                    let icon = 'bi-clock-history';

                    if (time.includes('Lewat')) {
                        badgeClass = 'bg-danger-subtle text-danger border-danger-subtle';
                        icon = 'bi-exclamation-triangle-fill';
                    } else if (time === 'Hari ini' || time === 'Hari Ini') {
                        badgeClass = 'bg-warning-subtle text-warning border-warning-subtle';
                        icon = 'bi-exclamation-circle-fill';
                    } else if (time.includes('Sisa') && !time.includes('bulan')) {
                        // Kurang dari sebulan (hanya tersisa hari)
                        badgeClass = 'bg-warning-subtle text-warning border-warning-subtle';
                        icon = 'bi-hourglass-split';
                    }

                    htmlLeftTime = `
                        <div class="mt-2 d-inline-flex align-items-center gap-1.5 fs-8 fw-semibold px-2 py-1 rounded-pill border ${badgeClass}" style="font-size: 0.75rem;">
                            <i class="bi ${icon}"></i>
                            <span>${time} (${title})</span>
                        </div>
                    `;
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
                divTimelineTugas: timeline,
                // htmlLeftTime: htmlLeftTime,
                perusahaan: permohonan.pelanggan.perusahaan.nama_perusahaan
            }

            if (typeof canUpdateProgress !== 'undefined' && canUpdateProgress && !isUploadDoc) {
                params.checkbox = true;
                if (selectedItems.includes(lhu.penyelia_hash)) {
                    params.isChecked = true;
                }
            }

            html += cardComponent(params, { btnMenuAction: btnAction, btnAction: btnAction2 });
        }

        if (result.data.length == 0) {
            html = htmlNoData();
        }

        $(`#list-container-lhu`).html(html);

        $(`#list-pagination-lhu`).html(createPaginationHTML(result.pagination));

        divTimelineTugas.map(d => d.render());
        $(`#list-placeholder-lhu`).hide();
        $(`#list-container-lhu`).show();

        updateCollectiveContainer();
    })
}

function updateCollectiveContainer() {
    let count = selectedItems.length;
    let visibleCheckboxes = $('.check-lhu');
    let visibleChecked = $('.check-lhu:checked');

    if (count > 0) {
        $('#container-collective-update').removeClass('d-none').addClass('d-flex');
        $('#countSelected').text(count);
    } else {
        $('#container-collective-update').removeClass('d-flex').addClass('d-none');
    }

    if (visibleCheckboxes.length > 0 && visibleCheckboxes.length === visibleChecked.length) {
        $('#checkAllLhu').prop('checked', true);
    } else {
        $('#checkAllLhu').prop('checked', false);
    }
}

$(document).on('change', '.check-lhu', function () {
    let val = $(this).val();
    if ($(this).is(':checked')) {
        if (!selectedItems.includes(val)) {
            selectedItems.push(val);
        }
    } else {
        selectedItems = selectedItems.filter(item => item !== val);
    }
    updateCollectiveContainer();
});

$(document).on('change', '#checkAllLhu', function () {
    let isChecked = $(this).is(':checked');
    $('.check-lhu').each(function () {
        let val = $(this).val();
        $(this).prop('checked', isChecked);
        if (isChecked) {
            if (!selectedItems.includes(val)) {
                selectedItems.push(val);
            }
        } else {
            selectedItems = selectedItems.filter(item => item !== val);
        }
    });
    updateCollectiveContainer();
});

function reload() {
    loadData();
}

function showDetail(obj) {
    const id = $(obj).parent().parent().data("id");
    detail.show(`api/v1/penyelia/getById/${id}`);
}

function clearFilter() {
    filterComp.clear();
    loadData();
}

function btnShowDoc(obj) {
    const url = $(obj).data('url');
    const title = $(obj).data('title') || 'Dokumen';
    modalDoc.show(url, {
        title: title
    });
}
