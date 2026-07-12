/**
 * produktivitas-keuangan.js
 * Manager – Produktivitas Keuangan (Invoice & Pengiriman)
 * Dashboard pantauan jumlah invoice dan pengiriman dengan ApexCharts.
 */

'use strict';

let keuTable   = null;  // DataTables invoice
let trenChart  = null;  // ApexCharts line/area
let donutChart = null;  // ApexCharts donut
let activeFilter = { date_range: null, petugas_id: null, status_invoice: null };
let filterComp = false;

$(function () {
    // Inisialisasi FilterComponent
    filterComp = new FilterComponent('list-filter', {
        jenis: 'produktivitas-keuangan',
        filter: {
            date_range: true,
            petugas_keuangan: true,
            status: true
        },
        placeholder: {
            petugas_keuangan: 'Semua Petugas',
            status: 'Semua Status'
        },
        showOnLoad: true
    });

    document.addEventListener('filter.change', function () {
        applyFilter();
    });

    // Inisialisasi DataTables & load data pertama kali
    initDataTable();
});

// ── Helpers ───────────────────────────────────────────────────────────────────
function formatDate(d) {
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const dd = String(d.getDate()).padStart(2, '0');
    return `${y}-${m}-${dd}`;
}

function formatRupiah(val) {
    const n = parseInt(val) || 0;
    return 'Rp ' + n.toLocaleString('id-ID');
}

// ── Apply & Clear Filter ──────────────────────────────────────────────────────
function applyFilter() {
    let dateRange = $('#filterDateRange').val();
    let splitDate = dateRange ? dateRange.split(' to ') : [];
    
    if (splitDate.length === 2) {
        activeFilter.date_range = [splitDate[0], splitDate[1]];
    } else {
        activeFilter.date_range = null;
    }

    activeFilter.petugas_id    = $('#filterpetugas_keuangan').val() || null;
    activeFilter.status_invoice = $('#filterStatus').val() || null;
    
    let count = 0;
    if (activeFilter.date_range) count++;
    if (activeFilter.petugas_id) count++;
    if (activeFilter.status_invoice) count++;
    
    if (count > 0) {
        $('#countFilter').removeClass('d-none').text(count);
    } else {
        $('#countFilter').addClass('d-none');
    }

    if (keuTable) keuTable.ajax.reload(null, false);
}

function clearFilter() {
    activeFilter = { date_range: null, petugas_id: null, status_invoice: null };
    if (filterComp && filterComp.fp) filterComp.fp.clear();
    $('#filterpetugas_keuangan').val('').trigger('change.select2');
    $('#filterStatus').val('').trigger('change.select2');
    $('#countFilter').addClass('d-none');
    
    if (keuTable) keuTable.ajax.reload(null, false);
}

function reloadData() {
    if (keuTable) keuTable.ajax.reload(null, false);
}

// ── Init DataTables ───────────────────────────────────────────────────────────
function initDataTable() {
    if (keuTable) { keuTable.destroy(); keuTable = null; }

    keuTable = $('#tbl-invoice-keuangan').DataTable({
        processing : true,
        serverSide : true,
        responsive : false,
        scrollX    : true,
        ajax: {
            url : dataUrl,
            type: 'GET',
            data: function (d) {
                if (activeFilter.date_range)     d.date_range     = activeFilter.date_range;
                if (activeFilter.petugas_id)     d.petugas_id     = activeFilter.petugas_id;
                if (activeFilter.status_invoice) d.status_invoice = activeFilter.status_invoice;
                return d;
            },
            dataSrc: function (json) {
                // Perbarui semua widget setiap kali data dimuat
                updateSummaryCards(json.summary);
                updateTrenChart(json.chart);
                updateDonutChart(json.invoice_breakdown);
                updateInvoiceBreakdown(json.invoice_breakdown, json.summary);
                updateEkspedisiBreakdown(json.ekspedisi_breakdown);
                return json.data;
            }
        },
        columns: [
            {
                data: 'no_invoice',
                title: 'No. Invoice',
                render: function (data) {
                    return `<span class="font-monospace fw-semibold text-primary small">${data || '-'}</span>`;
                }
            },
            { data: 'pelanggan', title: 'Pelanggan' },
            { data: 'layanan',   title: 'Layanan' },
            {
                data: 'total_harga',
                title: 'Total Harga',
                className: 'text-end align-middle',
                render: function (data) {
                    return `<span class="fw-semibold">${formatRupiah(data)}</span>`;
                }
            },
            {
                data: 'status',
                title: 'Status',
                className: 'text-center align-middle',
                render: function (data, type, row) {
                    return `<span class="badge rounded-pill bg-${row.status_color}">${row.status_label}</span>`;
                }
            },
            { data: 'metode',   title: 'Metode' },
            { data: 'tanggal',  title: 'Tgl. Dibuat', className: 'text-nowrap' },
            { data: 'paid_at',  title: 'Tgl. Lunas',  className: 'text-nowrap' },
        ],
        pageLength: 10,
        lengthMenu: [10, 25, 50],
        language: {
            search: '<i class="bi bi-search me-1"></i>',
            searchPlaceholder: 'Cari no. invoice / pelanggan...',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_–_END_ dari _TOTAL_ invoice',
            infoEmpty: 'Tidak ada data',
            infoFiltered: '(difilter dari _MAX_ data)',
            zeroRecords: '<div class="text-center py-4 text-muted"><i class="bi bi-inbox display-6 d-block mb-2"></i>Tidak ada invoice ditemukan</div>',
            paginate: {
                next    : '<i class="bi bi-chevron-right"></i>',
                previous: '<i class="bi bi-chevron-left"></i>'
            },
            processing: '<div class="d-flex align-items-center gap-2"><div class="spinner-border spinner-border-sm text-primary"></div> Memuat data...</div>'
        },
        dom: '<"d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3"lf>rtip',
        drawCallback: function () {
            $('#tbl-invoice-keuangan_filter input').addClass('form-control form-control-sm rounded-3');
        }
    });
}

// ── Summary Cards ─────────────────────────────────────────────────────────────
function updateSummaryCards(s) {
    if (!s) return;

    $('#stat_total_invoice').html(
        `<span class="prodkeu-stat-count">${s.total_invoice}</span>`
    );

    $('#stat_invoice_lunas').html(
        `<span class="prodkeu-stat-count">${s.invoice_lunas}</span>`
    );
    const pctLunas = s.total_invoice > 0 ? Math.round((s.invoice_lunas / s.total_invoice) * 100) : 0;
    $('#stat_label_lunas').html(
        `Invoice Lunas &nbsp;<span class="badge rounded-pill" style="background:rgba(28,200,138,.15);color:#157347;font-size:.65rem">${pctLunas}%</span>`
    );

    $('#stat_total_pengiriman').html(
        `<span class="prodkeu-stat-count">${s.total_pengiriman}</span>
         <div class="mt-1" style="font-size:.68rem;color:#6c757d">
             <i class="bi bi-send-check text-success me-1"></i>${s.pengiriman_diterima} diterima
             &nbsp;|&nbsp;
             <i class="bi bi-send text-warning me-1"></i>${s.pengiriman_dikirim} dikirim
         </div>`
    );

    $('#stat_nilai_lunas').html(
        `<span class="prodkeu-stat-count" style="font-size:.9rem">${formatRupiah(s.nilai_lunas)}</span>`
    );
}

// ── Tren Area Chart ───────────────────────────────────────────────────────────
function updateTrenChart(chartData) {
    const wrapper     = document.getElementById('tren-chart-wrapper');
    const placeholder = document.getElementById('tren-chart-placeholder');
    const container   = document.getElementById('trenChart');

    $(placeholder).removeClass('d-none').addClass('d-flex');

    if (!chartData || !chartData.labels || chartData.labels.length === 0) {
        container.style.display = 'none';
        placeholder.innerHTML = emptyChartHtml('bi-graph-up');
        placeholder.style.display = 'flex';
        return;
    }

    if (trenChart) { trenChart.destroy(); trenChart = null; }

    wrapper.style.height   = '240px';
    wrapper.style.position = 'relative';
    $(placeholder).removeClass('d-flex').addClass('d-none');
    container.style.display = 'block';
    container.style.width   = '100%';
    container.style.height  = '100%';

    trenChart = new ApexCharts(container, {
        series: [
            { name: 'Invoice',     data: chartData.invoice },
            { name: 'Pengiriman',  data: chartData.pengiriman }
        ],
        chart: {
            type      : 'area',
            height    : '100%',
            fontFamily: 'Nunito, sans-serif',
            toolbar   : { show: false },
            zoom      : { enabled: false }
        },
        colors   : ['#4e73df', '#f6c23e'],
        fill: {
            type    : 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom   : 0.35,
                opacityTo     : 0.05,
                stops         : [0, 95, 100]
            }
        },
        stroke    : { curve: 'smooth', width: 2 },
        dataLabels: { enabled: false },
        xaxis: {
            categories: chartData.labels,
            labels: {
                style   : { fontSize: '10px' },
                rotate  : -30,
                trim    : true
            }
        },
        yaxis: {
            labels: {
                formatter: function (val) { return Math.round(val); }
            }
        },
        tooltip: {
            y: { formatter: function (val, opts) {
                const name = opts.seriesName;
                return `${val} ${name === 'Invoice' ? 'invoice' : 'pengiriman'}`;
            }}
        },
        legend: { position: 'top', horizontalAlign: 'center', fontSize: '11px' },
        grid  : { borderColor: '#f1f3f5', strokeDashArray: 4 }
    });
    trenChart.render();
}

// ── Donut Chart ───────────────────────────────────────────────────────────────
function updateDonutChart(breakdown) {
    const wrapper     = document.getElementById('donut-chart-wrapper');
    const placeholder = document.getElementById('donut-chart-placeholder');
    const container   = document.getElementById('donutChart');

    $(placeholder).removeClass('d-none').addClass('d-flex');

    const filtered = (breakdown || []).filter(b => b.total > 0);
    if (!filtered.length) {
        container.style.display = 'none';
        placeholder.innerHTML = emptyChartHtml('bi-pie-chart');
        placeholder.style.display = 'flex';
        return;
    }

    if (donutChart) { donutChart.destroy(); donutChart = null; }

    wrapper.style.height   = '200px';
    $(placeholder).removeClass('d-flex').addClass('d-none');
    container.style.display = 'block';
    container.style.width   = '100%';
    container.style.height  = '100%';

    donutChart = new ApexCharts(container, {
        series: filtered.map(b => b.total),
        chart : { type: 'donut', height: '100%', fontFamily: 'Nunito, sans-serif' },
        labels: filtered.map(b => b.label),
        colors: filtered.map(b => b.color),
        legend: { position: 'bottom', fontSize: '11px' },
        plotOptions: {
            pie: { donut: { size: '62%', labels: { show: true,
                total: { show: true, label: 'Total', fontSize: '12px',
                    formatter: function (w) { return w.globals.seriesTotals.reduce((a,b)=>a+b,0); }
                }
            }}}
        },
        dataLabels: { enabled: false },
        tooltip: { y: { formatter: function (val) { return val + ' invoice'; }}}
    });
    donutChart.render();
}

// ── Invoice Breakdown (progress bars) ────────────────────────────────────────
function updateInvoiceBreakdown(breakdown, summary) {
    const placeholder = $('#invoice-breakdown-placeholder');
    const list        = $('#invoice-breakdown-list');

    placeholder.removeClass('d-none').addClass('d-flex');

    const filtered = (breakdown || []).filter(b => b.total > 0);
    if (!filtered.length) {
        placeholder.html(emptyDataHtml('bi-list-check')).show();
        list.addClass('d-none').html('');
        return;
    }

    const maxVal = Math.max(...filtered.map(b => b.total), 1);
    const colors = { 'Draft': '#6c757d', 'Menunggu Bayar': '#f6c23e', 'Lunas': '#1cc88a', 'Ditolak': '#e74a3b' };

    let html = '';
    filtered.forEach(b => {
        const pct   = Math.round((b.total / maxVal) * 100);
        const color = colors[b.label] || '#4e73df';
        const total = summary ? summary.total_invoice : 0;
        const pctOfTotal = total > 0 ? Math.round((b.total / total) * 100) : 0;
        html += `
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small fw-semibold" style="color:${color}">${b.label}</span>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge rounded-pill" style="background:${color}22;color:${color};font-size:.7rem">${pctOfTotal}%</span>
                    <span class="fw-bold small">${b.total}</span>
                </div>
            </div>
            <div class="bg-secondary bg-opacity-10 rounded-pill" style="height:6px">
                <div class="keu-breakdown-bar" style="width:${pct}%;background:${color}"></div>
            </div>
        </div>`;
    });

    placeholder.removeClass('d-flex').addClass('d-none');
    list.removeClass('d-none').html(html);
}

// ── Ekspedisi Breakdown ───────────────────────────────────────────────────────
function updateEkspedisiBreakdown(data) {
    const placeholder = $('#ekspedisi-breakdown-placeholder');
    const list        = $('#ekspedisi-breakdown-list');

    placeholder.removeClass('d-none').addClass('d-flex');

    if (!data || !data.length) {
        placeholder.html(emptyDataHtml('bi-truck')).show();
        list.addClass('d-none').html('');
        return;
    }

    const maxVal = Math.max(...data.map(e => e.total), 1);
    const colors = ['#f6c23e','#4e73df','#1cc88a','#e74a3b','#8b5cf6','#36b9cc'];

    let html = '';
    data.forEach((e, idx) => {
        const pct   = Math.round((e.total / maxVal) * 100);
        const color = colors[idx % colors.length];
        html += `
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small fw-semibold text-truncate" style="max-width:60%">${e.nama}</span>
                <div class="d-flex align-items-center gap-1">
                    <span class="badge rounded-pill bg-success bg-opacity-10 text-success" style="font-size:.65rem">
                        <i class="bi bi-check2"></i> ${e.diterima}
                    </span>
                    <span class="badge rounded-pill bg-warning bg-opacity-10 text-warning" style="font-size:.65rem">
                        <i class="bi bi-send"></i> ${e.dikirim}
                    </span>
                    <span class="fw-bold small ms-1">${e.total}</span>
                </div>
            </div>
            <div class="bg-secondary bg-opacity-10 rounded-pill" style="height:5px">
                <div class="keu-ekspedisi-bar" style="width:${pct}%;background:${color}"></div>
            </div>
        </div>`;
    });

    placeholder.removeClass('d-flex').addClass('d-none');
    list.removeClass('d-none').html(html);
}

// ── Empty state helpers ───────────────────────────────────────────────────────
function emptyChartHtml(icon) {
    return `<div class="text-center text-muted py-4">
        <i class="bi ${icon} display-5 d-block mb-2 opacity-25"></i>
        <small>Belum ada data untuk ditampilkan</small>
    </div>`;
}

function emptyDataHtml(icon) {
    return `<div class="text-center text-muted py-4 w-100">
        <i class="bi ${icon} display-6 d-block mb-2 opacity-25"></i>
        <small>Belum ada data</small>
    </div>`;
}
