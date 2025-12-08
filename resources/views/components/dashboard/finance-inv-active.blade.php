<div class="card shadow-sm border-0 rounded-4 h-100">
    <div class="card-header bg-white py-3 border-bottom rounded-top-4">
        <h6 class="m-0 font-weight-bold text-dark">
            <i class="bi bi-funnel-fill me-2 text-warning"></i>Status Invoice Aktif
        </h6>
    </div>
    <div class="card-body">
        @if($funnelData['isEmpty'])
        <div class="text-center pb-3">
            <div class="mb-3">
                <i class="bi bi-receipt text-muted opacity-25" style="font-size: 4rem;"></i>
            </div>
            <h6 class="text-gray-800 font-weight-bold">Tidak ada invoice saat ini.</h6>
        </div>
        @else
        <div id="chartFunnel" style="width: 100%; min-height: 250px;"></div>
            @if($funnelData['data'][2] != 0)
            <div class="alert alert-warning d-flex align-items-center p-2 mt-3 mb-0 small">
                <i class="bi bi-exclamation-circle-fill me-2"></i>
                <span><strong>{{ $funnelData['data'][2] }} Pembayaran</strong> menunggu verifikasi Anda.</span>
            </div>
            @endif
        @endif
    </div>
</div>
@if(!$funnelData['isEmpty'])
<script>
    {
        const fnlData = @json($funnelData);

        // --- 3. CONFIG CHART FUNNEL (Horizontal Bar) ---
        const optFunnel = {
            series: [{
                name: 'Jumlah Invoice',
                data: fnlData.data
            }],
            chart: {
                type: 'bar',
                height: 300,
                stacked: true,
                fontFamily: 'Nunito, sans-serif',
                toolbar: { show: true }
            },
            plotOptions: {
                bar: {
                    horizontal: true, // Bar Tidur
                    barHeight: '55%',
                    borderRadius: 4,
                    distributed: true // Agar warna beda-beda tiap bar
                }
            },
            colors: ['#858796', '#4e73df', '#f6c23e', '#1cc88a', '#e74a3b'], // Abu, Biru, Kuning, Hijau, Merah
            dataLabels: {
                enabled: true,
                textAnchor: 'start',
                style: { colors: ['#fff'] },
                formatter: function (val, opt) {
                    return val + " Inv"
                },
                offsetX: 0,
            },
            xaxis: {
                categories: fnlData.categories,
                labels: {
                    formatter: function (value) {
                        // Memastikan hanya angka bulat yang ditampilkan pada sumbu
                        if (Math.floor(value) === value) {
                            return value;
                        }
                    }
                }
            },
            legend: {
                position: 'bottom',
                itemMargin: { horizontal: 10, vertical: 20 }
            }
        };

        new ApexCharts(document.querySelector("#chartFunnel"), optFunnel).render();
    }
</script>
@endif
