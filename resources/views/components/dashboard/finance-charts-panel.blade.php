<div class="card shadow-sm border-0 rounded-4 h-100">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center rounded-top-4">
        <h6 class="m-0 font-weight-bold text-dark">
            <i class="bi bi-bar-chart-line-fill me-2 text-primary"></i>Arus Kas & Piutang (2025)
        </h6>
        <span class="badge bg-light text-muted border">Dalam Juta Rupiah</span>
    </div>
    <div class="card-body">
        @if(!$cashFlowData['isEmpty'])
        <div id="chartCashFlow" style="min-height: 300px;"></div>
        @else
        <div class="text-center pb-3">
            <div class="mb-3">
                <i class="bi bi-receipt text-muted opacity-25" style="font-size: 4rem;"></i>
            </div>
            <h6 class="text-gray-800 font-weight-bold">Tidak ada arus kas saat ini.</h6>
        </div>
        @endif
    </div>
</div>

@if(!$cashFlowData['isEmpty'])
<script>
    {
        // --- DATA DARI CONTROLLER ---
        const cfData = @json($cashFlowData);

        // --- 1. CONFIG CHART CASH FLOW (Stacked Column) ---
        const optCashFlow = {
            series: [{
                name: 'Sudah Lunas (Uang Masuk)',
                data: cfData.lunas
            }, {
                name: 'Belum Lunas (Piutang)',
                data: cfData.piutang
            }],
            chart: {
                type: 'bar',
                height: 300,
                stacked: true, // Agar bertumpuk
                fontFamily: 'Nunito, sans-serif',
                toolbar: { show: true }
            },
            colors: ['#1cc88a', '#e74a3b'], // Hijau (Aman), Merah (Piutang)
            plotOptions: {
                bar: { horizontal: false, columnWidth: '55%', borderRadius: 4 }
            },
            dataLabels: { enabled: false },
            xaxis: { categories: cfData.categories },
            yaxis: {
                labels: { formatter: (val) => formatRupiah((val / 1000000)) + " Jt" }
            },
            legend: { position: 'top' },
            tooltip: {
                y: { formatter: (val) => formatRupiah(val) }
            }
        };


        // --- RENDER SEMUA ---
        // Cek dulu apakah library sudah load (sesuai diskusi kita sebelumnya)
        if (typeof ApexCharts !== 'undefined') {
            new ApexCharts(document.querySelector("#chartCashFlow"), optCashFlow).render();
        } else {
            console.error("ApexCharts belum diload di window app.js");
        }
    }
</script>
@endif
