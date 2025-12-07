<div class="card shadow-sm border-0 rounded-4 h-100">
    <div class="card-header bg-white py-3 border-bottom rounded-top-4">
        <h6 class="m-0 font-weight-bold text-dark">
            <i class="bi bi-pie-chart-fill me-2 text-success"></i>Pendapatan per Layanan
        </h6>
    </div>
    <div class="card-body d-flex flex-column align-items-center justify-content-center">
        @if(!$serviceData['isEmpty'])
        <div id="chartServices" style="width: 100%; min-height: 250px;"></div>
        @else
        <div class="text-center pb-3">
            <div class="mb-3">
                <i class="bi bi-bar-chart text-muted opacity-25" style="font-size: 4rem;"></i>
            </div>
            <h6 class="text-gray-800 font-weight-bold">Tidak ada data saat ini.</h6>
        </div>
        @endif
    </div>
</div>

@if(!$serviceData['isEmpty'])
<script>
    {
        const srvData = @json($serviceData);

        // --- 2. CONFIG CHART LAYANAN (Donut) ---
        const optServices = {
            series: srvData.series,
            labels: srvData.labels,
            chart: {
                type: 'donut',
                height: 300,
                fontFamily: 'Nunito, sans-serif'
            },
            colors: ['#4e73df', '#36b9cc', '#f6c23e', '#858796'],
            dataLabels: { enabled: false },
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total',
                                formatter: function (w) {
                                    return w.globals.seriesTotals.reduce((a, b) => {
                                        return a + b
                                    })
                                }
                            }
                        }
                    }
                }
            },
            legend: { position: 'bottom' }
        };

        new ApexCharts(document.querySelector("#chartServices"), optServices).render();
    }
</script>
@endif
