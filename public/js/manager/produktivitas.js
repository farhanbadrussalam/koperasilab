/**
 * produktivitas.js
 * Manager – Produktivitas Petugas
 * Menampilkan DataTables + Chart.js untuk pemantauan produktivitas petugas lab.
 */

let prodTable = null;   // DataTables instance
let prodChart = null;   // Chart.js instance
let activeFilter = {};    // Filter yang sedang aktif 
let filterComp = false;

// ─── Flatpickr: Date pickers ─────────────────────────────────────────────────
$(function () {
    filterComp = new FilterComponent('list-filter', {
        jenis: 'produktivitas',
        filter: {
            date_range: true,
            satuan_kerja: true
        },
        showOnLoad: true
    })

    filterComp.on('filter.change', () => {
        if (prodTable) prodTable.ajax.reload(null, false);
    });
    // Inisialisasi DataTables
    initDataTable();
});

// ─── Build DataTables columns dari masterJobIds ──────────────────────────────
function buildColumns() {
    const cols = [
        {
            data: 'nama_petugas',
            title: 'Nama Petugas',
            render: function (data, type, row) {
                return `
                    <div class="d-flex align-items-center gap-2">
                        ${row.avatar}
                        <div>
                            <span class="fw-semibold">${data}</span>
                            <div><span class="text-muted">${row.jabatan || '-'}</span></div>
                        </div>
                    </div>`;
            }
        }
    ];

    // Kolom Total Selesai
    cols.push({
        data: 'total_selesai',
        title: '<span class="text-success">✔ Selesai</span>',
        className: 'text-start align-middle',
        render: function (data, type, row) {
            const jobs = row.jobs || [];
            let html = '';
            jobs.forEach(function (jobId) {
                const selesai = parseInt(row['job_' + jobId + '_s']) || 0;
                if (selesai >= 0) {
                    const idx = masterJobIds.findIndex((id) => id === jobId);
                    const jobName = idx !== -1 ? masterJobNames[idx] : ('Job ' + jobId);
                    html += `
                        <div class="d-flex align-items-center justify-content-between gap-3 my-1" style="min-width:150px">
                            <span class="text-secondary small">${jobName}</span>
                            <span class="badge rounded-pill px-2" style="background:rgba(28,200,138,.15);color:#157347;font-size:.74rem">${selesai}</span>
                        </div>`;
                }
            });
            return html || '<span class="text-muted">—</span>';
        }
    });

    // Kolom Total Dikerjakan
    cols.push({
        data: 'total_dikerjakan',
        title: '<span class="text-warning">⟳ Proses</span>',
        className: 'text-start align-middle',
        render: function (data, type, row) {
            const jobs = row.jobs || [];
            let html = '';
            jobs.forEach(function (jobId) {
                const dikerjakan = parseInt(row['job_' + jobId + '_d']) || 0;
                if (dikerjakan >= 0) {
                    const idx = masterJobIds.findIndex((id) => id === jobId);
                    const jobName = idx !== -1 ? masterJobNames[idx] : ('Job ' + jobId);
                    html += `
                        <div class="d-flex align-items-center justify-content-between gap-3 my-1" style="min-width:150px">
                            <span class="text-secondary small">${jobName}</span>
                            <span class="badge rounded-pill px-2" style="background:rgba(246,194,62,.18);color:#856404;font-size:.74rem">${dikerjakan}</span>
                        </div>`;
                }
            });
            return html || '<span class="text-muted">—</span>';
        }
    });

    // Kolom Grand Total
    cols.push({
        data: 'total',
        title: 'Total',
        className: 'text-center fw-bold',
        render: function (data) {
            const val = parseInt(data) || 0;
            return `<span class="badge bg-secondary rounded-pill px-3">${val}</span>`;
        }
    });

    return cols;
}

// ─── Init DataTables ─────────────────────────────────────────────────────────
function initDataTable() {
    if (prodTable) {
        prodTable.destroy();
        prodTable = null;
    }

    prodTable = $('#tbl-produktivitas').DataTable({
        processing: true,
        serverSide: true,
        responsive: false,
        scrollX: true,
        ajax: {
            url: dataUrl,
            type: 'GET',
            data: function (d) {
                let filterValue = filterComp && filterComp.getAllValue();
                // Kirim filter aktif ke server
                if (filterValue.satuan_kerja) d.satuan_kerja = filterValue.satuan_kerja;
                if (filterValue.search) d.pencarian = filterValue.search;
                if (filterValue.date_range && filterValue.date_range.length == 2) d.date_range = filterValue.date_range;

                return d;
            },
            dataSrc: function (json) {
                // Update summary cards & chart setiap kali data dimuat
                updateSummaryCards(json.summary);
                updateChart(json.chart);
                updateBreakdown(json.data);
                return json.data;
            }
        },
        columns: buildColumns(),
        pageLength: 10,
        lengthMenu: [10, 25, 50],
        language: {
            search: '<i class="bi bi-search me-1"></i>',
            searchPlaceholder: 'Cari nama / jabatan...',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_–_END_ dari _TOTAL_ petugas',
            infoEmpty: 'Tidak ada data yang tersedia',
            infoFiltered: '(difilter dari _MAX_ total data)',
            zeroRecords: '<div class="text-center py-4 text-muted"><i class="bi bi-inbox display-6 d-block mb-2"></i>Tidak ada data produktivitas ditemukan</div>',
            paginate: {
                next: '<i class="bi bi-chevron-right"></i>',
                previous: '<i class="bi bi-chevron-left"></i>'
            },
            processing: '<div class="d-flex align-items-center gap-2"><div class="spinner-border spinner-border-sm text-primary"></div> Memuat data...</div>'
        },
        dom: '<"d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3"lf>rtip',
        drawCallback: function () {
            // Style search input
            $('#tbl-produktivitas_filter input').addClass('form-control form-control-sm rounded-3');
        }
    });
}

// ─── Update Summary Cards ────────────────────────────────────────────────────
function updateSummaryCards(summary) {
    if (!summary) return;

    $('#stat_total_petugas').html(
        `<span class="prod-stat-count">${summary.total_petugas}</span>`
    );
    // Tampilkan selesai + dikerjakan di card kedua
    $('#stat_total_selesai').html(
        `<span class="prod-stat-count">${summary.total_selesai}</span>`
    );
    $('#stat_label_selesai').html(
        `Total Selesai &nbsp;<span class="badge rounded-pill" style="background:rgba(246,194,62,.2);color:#856404;font-size:.7rem">+${summary.total_dikerjakan} proses</span>`
    );
    $('#stat_avg').html(
        `<span class="prod-stat-count">${summary.avg_per_petugas}</span>`
    );
    $('#stat_top_performer').html(
        `<span class="prod-stat-count text-truncate d-block" title="${summary.top_performer}">${summary.top_performer}</span>`
    );
    $('#stat_top_total').html(
        `Top Performer · <strong>${summary.top_total}</strong> selesai`
    );
}

// ─── Update ApexCharts Bar Chart ──────────────────────────────────────────────
function updateChart(chartData) {
    const wrapper = document.getElementById('chart-wrapper');
    const placeholder = document.getElementById('chart-placeholder');
    const container = document.getElementById('prodChart');
    $(placeholder).removeClass('d-none').addClass('d-flex');
    if (!chartData || !chartData.labels || chartData.labels.length === 0) {
        // Tampilkan placeholder kosong, sembunyikan container
        container.style.display = 'none';
        placeholder.innerHTML = `
            <div class="text-center text-muted py-5">
                <i class="bi bi-bar-chart display-5 d-block mb-2 opacity-25"></i>
                <small>Belum ada data untuk ditampilkan</small>
            </div>`;
        placeholder.style.display = 'flex';
        return;
    }

    // Destroy chart lama jika ada
    if (prodChart) {
        prodChart.destroy();
        prodChart = null;
    }

    // ── PENTING: set ukuran wrapper & tampilkan container SEBELUM new ApexCharts() ──
    wrapper.style.height = '260px';
    wrapper.style.position = 'relative';
    $(placeholder).removeClass('d-flex').addClass('d-none');
    container.style.display = 'block';
    container.style.width = '100%';
    container.style.height = '100%';

    const options = {
        series: [
            {
                name: 'Selesai',
                data: chartData.values
            },
            {
                name: 'Dikerjakan',
                data: chartData.values_prog || []
            }
        ],
        chart: {
            type: 'bar',
            height: '100%',
            fontFamily: 'Nunito, sans-serif',
            toolbar: {
                show: false
            }
        },
        colors: ['#1cc88a', '#f6c23e'], // Hijau untuk selesai, Kuning untuk Proses
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '55%',
                borderRadius: 4,
                borderRadiusApplication: 'end'
            },
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            show: true,
            width: 2,
            colors: ['transparent']
        },
        xaxis: {
            categories: chartData.labels,
            labels: {
                style: {
                    fontSize: '11px'
                },
                rotate: -35,
                rotateAlways: false,
                trim: true
            }
        },
        yaxis: {
            labels: {
                formatter: function (val) {
                    return Math.round(val);
                }
            }
        },
        fill: {
            opacity: 1
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return val + " pekerjaan";
                }
            }
        },
        legend: {
            position: 'top',
            horizontalAlign: 'center',
            fontSize: '11px',
            markers: {
                radius: 12
            }
        }
    };

    prodChart = new ApexCharts(container, options);
    prodChart.render();
}


// ─── Update Breakdown Panel ──────────────────────────────────────────────────
function updateBreakdown(data) {
    $('#breakdown-placeholder').removeClass('d-none').addClass('d-flex');
    if (!data || data.length === 0) {
        $('#breakdown-placeholder').html(`
            <div class="text-center text-muted py-4">
                <i class="bi bi-pie-chart display-5 d-block mb-2 opacity-25"></i>
                <small>Belum ada data untuk ditampilkan</small>
            </div>
        `).show();
        $('#breakdown-list').addClass('d-none').html('');
        return;
    }

    // Hitung total per jenis pekerjaan dan cari petugas teraktif per proses
    const jobTotals = {};
    masterJobIds.forEach((id, idx) => {
        jobTotals[id] = {
            name: masterJobNames[idx],
            total: 0,
            topStaffName: null,
            topStaffAvatar: null,
            topStaffCount: 0
        };
    });

    data.forEach(row => {
        masterJobIds.forEach(id => {
            const selesai = parseInt(row['job_' + id + '_s']) || 0;
            jobTotals[id].total += selesai;

            if (selesai > jobTotals[id].topStaffCount) {
                jobTotals[id].topStaffCount = selesai;
                jobTotals[id].topStaffName = row.nama_petugas;
                jobTotals[id].topStaffAvatar = row.avatar;
            }
        });
    });

    const sorted = Object.values(jobTotals).filter(j => j.total > 0).sort((a, b) => b.total - a.total);
    const maxTotal = sorted.length ? sorted[0].total : 1;
    const colors = ['#4e73df', '#1cc88a', '#f6c23e', '#e74a3b', '#8b5cf6', '#36b9cc'];

    if (sorted.length === 0) {
        $('#breakdown-placeholder').html(`
            <div class="text-center text-muted py-4">
                <i class="bi bi-pie-chart display-5 d-block mb-2 opacity-25"></i>
                <small>Belum ada data untuk ditampilkan</small>
            </div>
        `).show();
        $('#breakdown-list').addClass('d-none').html('');
        return;
    }

    let html = '';
    sorted.forEach((job, idx) => {
        const pct = Math.round((job.total / maxTotal) * 100);
        const color = colors[idx % colors.length];
        html += `
            <div class="col-12 col-md-6">
                <div class="breakdown-item d-flex flex-column gap-1.5 p-3 rounded-3 border bg-light h-100">
                    <div class="w-100">
                        <div class="d-flex justify-content-between mb-1 align-items-center">
                            <span class="fw-semibold small" style="color:${color}">${job.name}</span>
                            <span class="fw-bold small text-dark">${job.total} <span class="text-muted" style="font-size:0.75rem;font-weight:normal;">selesai</span></span>
                        </div>
                    </div>
                    ${job.topStaffName ? `
                    <div class="d-flex align-items-center mt-1 text-muted gap-2" style="font-size: 0.72rem;">
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-secondary"><i class="bi bi-fire text-warning me-0.5"></i> Teraktif:</span>
                            <span class="fw-semibold text-dark">${job.topStaffName}</span>
                        </div>
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill font-monospace fw-bold" style="font-size: 0.68rem; padding: 0.25em 0.6em;">
                            ${job.topStaffCount} selesai
                        </span>
                    </div>
                    <div class="bg-secondary bg-opacity-10 rounded-pill mt-1" style="height:3px">
                        <div class="breakdown-bar" style="width:${pct}%;background:${color}"></div>
                    </div>
                    ` : ''}
                </div>
            </div>`;
    });

    $('#breakdown-placeholder').removeClass('d-flex').addClass('d-none');
    $('#breakdown-list').removeClass('d-none').html(html);
}

function reloadData() {
    if (prodTable) prodTable.ajax.reload(null, false);
}

function clearFilter() {
    filterComp.clear();
    if (prodTable) prodTable.ajax.reload(null, false);
}
