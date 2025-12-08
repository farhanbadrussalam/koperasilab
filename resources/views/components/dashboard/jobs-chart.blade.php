<div class="card shadow-sm border-0 rounded-4 mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center rounded-top-4">
        <h6 class="m-0 font-weight-bold text-dark">
            <i class="bi bi-list-check me-2 text-primary"></i>Monitoring
        </h6>
    </div>
    <div class="card-body">
        @if(!$chartData['isEmpty'])
        <div id="jobChart" style="width: 100%; min-height: 300px;"></div>
        @else
        <div class="text-center pb-3">
            <div class="mb-3">
                <i class="bi bi-list-check text-muted opacity-25" style="font-size: 4rem;"></i>
            </div>
            <h6 class="text-gray-800 font-weight-bold">Tidak ada data saat ini.</h6>
        </div>
        @endif
    </div>
</div>

@if(!$chartData['isEmpty'])
<script>
    {
        const chartData = @json($chartData);
        // chart
        let options1 = {
            series: [{
                name: 'Penyeliaan',
                data: chartData.value
            }],
            chart: {
                type: 'bar', // Tipe Bar
                height: 320,
                fontFamily: 'Nunito, sans-serif', // Sesuaikan font web Anda
                toolbar: {
                    export: {
                        csv: {
                            filename: 'monitoring-penyeliaan-data',
                        },
                        svg: {
                            filename: 'monitoring-penyeliaan-chart',
                        },
                        png: {
                            filename: 'monitoring-penyeliaan-chart',
                        }
                    }
                }
            },
            xaxis: {
                categories: chartData.category,
                labels: {
                    formatter: function (value) {
                        // Memastikan hanya angka bulat yang ditampilkan pada sumbu
                        if (Math.floor(value) === value) {
                            return value;
                        }
                    }
                }
            },
            plotOptions: {
                bar: {
                    distributed: true,
                    columnWidth: '45%',
                    borderRadius: 4,
                    borderRadiusApplication: 'end',
                    horizontal: true
                }
            },
            colors: chartData.color,
            title: {
                text: 'Penyeliaan',
                align: 'center',
                floating: true
            },
            dataLabels: {
                enabled: true
            },
            legend: {
                show: false
            },
            tooltip: {
                y: {
                    formatter: function(value) {
                        return value
                    }
                }
            }
        };

        let chart = new ApexCharts(document.querySelector("#jobChart"), options1);
        chart.render();
    }
</script>
@endif
