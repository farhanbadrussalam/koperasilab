@props(['data_chart_1', 'data_chart_2', 'data_layanan'])
<div class="card shadow-sm border-0 rounded-4 mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center rounded-top-4">
        <h6 class="m-0 font-weight-bold text-dark">
            <i class="bi bi-pie-chart-fill me-2 text-primary"></i>Statistik Layanan
        </h6>
        <div class="w-25">
            <select name="layanan" id="select_layanan" class="form-select form-select-sm">
                @foreach($data_layanan as $layanan)
                    <option value="{{ $layanan->layanan_hash }}">{{ $layanan->nama_layanan }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="card-body d-flex flex-row align-items-center justify-content-center">
        <div id="serviceChart_1" style="width: 100%; min-height: 300px;"></div>
        <div id="serviceChart_2" style="width: 100%; min-height: 300px;"></div>

        <div id="emptyChartMessage" class="d-none text-center text-muted py-4">
            <i class="bi bi-bar-chart opacity-25" style="font-size: 3rem;"></i>
            <p class="small mt-2">Belum ada data pengajuan masuk.</p>
        </div>
    </div>
</div>

<script>
    {
        const data_chart_1 = @json($data_chart_1);
        const data_chart_2 = @json($data_chart_2);
        // Chart 1
        let options = {
            series: [{
                name: 'Kontrak',
                data: data_chart_1.value
            }],
            chart: {
                type: 'bar', // Tipe Bar
                height: 320,
                fontFamily: 'Nunito, sans-serif' // Sesuaikan font web Anda
            },
            xaxis: {
                categories: data_chart_1.category
            },
            yaxis: {
                title: {
                    text: 'Jumlah Kontrak'
                }
            },
            plotOptions: {
                bar: {
                    distributed: true,
                    columnWidth: '45%',
                    borderRadius: 4,
                    borderRadiusApplication: 'end'
                }
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

        let chart = new ApexCharts(document.querySelector("#serviceChart_1"), options);
        chart.render();

        // Chart 2
        let options2 = {
            series: data_chart_2.value, // Contoh: [10, 5, 2]
            labels: data_chart_2.category,
            chart: {
                type: 'donut', // Tipe Donut
                height: 320,
                fontFamily: 'Nunito, sans-serif' // Sesuaikan font web Anda
            },
            // colors: ['#f64e60', '#f6c23e', '#1cc88a', '#36b9cc'], // merah, kuning, hijau, biru
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%', // Ketebalan donat
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total',
                                formatter: function (w) {
                                    // Menjumlahkan semua data
                                    return w.globals.seriesTotals.reduce((a, b) => {
                                        return a + b
                                    }, 0)
                                }
                            }
                        }
                    }
                }
            },
            dataLabels: {
                enabled: false // Agar tidak penuh tulisan angka di dalam donat
            },
            legend: {
                position: 'bottom',
                offsetY: 0
            },
            tooltip: {
                y: {
                    formatter: function(value) {
                        return value + " Pengajuan"
                    }
                }
            }
        };

        let chart2 = new ApexCharts(document.querySelector("#serviceChart_2"), options2);
        chart2.render();
    }
</script>
