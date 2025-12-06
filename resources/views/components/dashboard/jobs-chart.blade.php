<div class="card shadow-sm border-0 rounded-4 mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center rounded-top-4">
        <h6 class="m-0 font-weight-bold text-dark">
            <i class="bi bi-list-check me-2 text-primary"></i>Monitoring
        </h6>
    </div>
    <div class="card-body">
        <div id="jobChart" style="width: 100%; min-height: 300px;"></div>
    </div>
</div>

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
        // let options = {
        //     series: chartData.value,
        //     labels: chartData.category,
        //     chart: {
        //         type: 'donut',
        //         height: 320,
        //         fontFamily: 'Nunito, sans-serif' // Sesuaikan font web Anda
        //     },
        //     plotOptions: {
        //         pie: {
        //             donut: {
        //                 size: '70%', // Ketebalan donat
        //                 labels: {
        //                     show: true,
        //                     total: {
        //                         show: true,
        //                         label: 'Total',
        //                         formatter: function (w) {
        //                             // Menjumlahkan semua data
        //                             return w.globals.seriesTotals.reduce((a, b) => {
        //                                 return a + b
        //                             }, 0)
        //                         }
        //                     }
        //                 }
        //             }
        //         }
        //     },
        //     dataLabels: {
        //         enabled: false
        //     },
        //     legend: {
        //         position: 'bottom'
        //     },
        //     tooltip: {
        //         y: {
        //             formatter: function(value) {
        //                 return value + " Penyeliaan"
        //             }
        //         }
        //     }
        // }

        let chart = new ApexCharts(document.querySelector("#jobChart"), options1);
        chart.render();
    }
</script>
