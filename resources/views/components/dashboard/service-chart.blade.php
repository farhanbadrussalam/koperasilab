@props(['charts' => [], 'data_layanan' => [], 'isEmpty' => false, 'title' => 'Statistik', 'icon' => ''])

<div class="card shadow-sm border-0 rounded-4 mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center rounded-top-4">
        <h6 class="m-0 font-weight-bold text-dark">
            <i class="bi bi-{{ $icon }} me-2 text-primary"></i>{{$title}}
        </h6>
        @if(!empty($data_layanan) && count($data_layanan) > 0)
        <div class="w-25">
            <select name="layanan" id="select_layanan" class="form-select form-select-sm">
                @foreach($data_layanan as $layanan)
                    <option value="{{ $layanan->layanan_hash }}">{{ $layanan->nama_layanan }}</option>
                @endforeach
            </select>
        </div>
        @endif
    </div>

    <div class="card-body d-flex flex-row align-items-center justify-content-center">
        @if(!$isEmpty && count($charts) > 0)
            @foreach($charts as $index => $chart)
                <div id="serviceChart_{{ $chart['id_chart'] }}" style="width: {{ 100 / count($charts) }}%; min-height: 300px;"></div>
            @endforeach
        @else
        <div class="text-center pb-3">
            <div class="mb-3">
                <i class="bi bi-{{ $icon }} text-muted opacity-25" style="font-size: 4rem;"></i>
            </div>
            <h6 class="text-gray-800 font-weight-bold">Tidak ada data saat ini.</h6>
        </div>
        @endif
    </div>
</div>

@if(!$isEmpty && count($charts) > 0)
<script>
    {
        const chartsData = @json($charts);
        
        chartsData.forEach((chartConfig, index) => {
            const chartId = "#serviceChart_" + chartConfig.id_chart;
            let options = {};
            
            if (['pie', 'donut'].includes(chartConfig.type)) {
                options = {
                    series: chartConfig.data.value,
                    labels: chartConfig.data.category,
                    chart: {
                        type: chartConfig.type,
                        height: chartConfig.height || 320,
                        fontFamily: 'Nunito, sans-serif'
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '70%',
                                labels: {
                                    show: true,
                                    total: {
                                        show: true,
                                        label: chartConfig.total_label || 'Total',
                                        formatter: function (w) {
                                            return w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                                        }
                                    }
                                }
                            }
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    legend: {
                        position: chartConfig.legend_position || 'bottom',
                        offsetY: 0
                    },
                    tooltip: {
                        y: {
                            formatter: function(value) {
                                return value + " " + (chartConfig.tooltip_suffix || '')
                            }
                        }
                    }
                };
            } else if (['bar', 'line', 'area'].includes(chartConfig.type)) {
                options = {
                    series: [{
                        name: chartConfig.series_name || 'Data',
                        data: chartConfig.data.value
                    }],
                    chart: {
                        type: chartConfig.type,
                        height: chartConfig.height || 320,
                        stacked: chartConfig.stacked || false,
                        fontFamily: 'Nunito, sans-serif'
                    },
                    xaxis: {
                        categories: chartConfig.data.category,
                    },
                    yaxis: {
                        title: {
                            text: chartConfig.yaxis_title || ''
                        },
                        labels: {
                            formatter: function (value) {
                                if (Math.floor(value) === value) {
                                    return value;
                                }
                                return value;
                            }
                        }
                    },
                    plotOptions: {
                        bar: {
                            distributed: chartConfig.distributed || false,
                            horizontal: chartConfig.horizontal || false,
                            columnWidth: '45%',
                            borderRadius: 4,
                            borderRadiusApplication: 'end'
                        }
                    },
                    dataLabels: {
                        enabled: true
                    },
                    legend: {
                        show: chartConfig.show_legend !== undefined ? chartConfig.show_legend : false
                    },
                    tooltip: {
                        y: {
                            formatter: function(value) {
                                return value + " " + (chartConfig.tooltip_suffix || '')
                            }
                        }
                    }
                };

                if (chartConfig.color) {
                    options.colors = chartConfig.color;
                }

                if (chartConfig.horizontal) {
                    // options.xaxis.labels = {
                    //     formatter: function (value) {
                    //         if (Math.floor(value) === value) {
                    //             return value;
                    //         }
                    //     }
                    // };
                    options.yaxis.labels = {
                        formatter: function (value) {
                            return value;
                        }
                    };
                }

                if (chartConfig.title) {
                    options.title = {
                        text: chartConfig.title,
                        align: chartConfig.title_align || 'center',
                        floating: chartConfig.title_floating !== undefined ? chartConfig.title_floating : true
                    };
                }
            }
            
            // Allow override of any ApexCharts options
            if (chartConfig.options) {
                Object.assign(options, chartConfig.options);
            }

            let chart = new ApexCharts(document.querySelector(chartId), options);
            chart.render();
        });
    }
</script>
@endif
