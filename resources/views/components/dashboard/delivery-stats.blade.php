@props(['dataStatistics', 'url'])
<div class="card shadow-sm border-0 rounded-4 mb-4 h-100">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center rounded-top-4">
        <h6 class="m-0 font-weight-bold text-dark">
            <i class="bi bi-truck-front-fill me-2 text-info"></i>Monitoring Logistik
        </h6>
        <a href="{{ $url }}" class="text-decoration-none small fw-bold text-info">
            Ke Menu Utama <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <div class="card-body">
        <div class="row g-2 mb-4">
            @foreach ($dataStatistics as $key => $statistic)
                <div class="{{ $key == (count($dataStatistics) - 1) && $key % 2 == 0 ? 'col-md-12' : 'col-md-6' }}">
                    <div class="p-2 rounded-3 bg-{{ $statistic['color'] }}-subtle border border-{{ $statistic['color'] }} text-center h-100">
                        <i class="bi {{ $statistic['icon'] }} text-{{ $statistic['color'] }} fs-3 mb-2"></i>
                        <h4 class="fw-bold text-dark mb-0" id="count-{{ $statistic['status'] }}">{{ $statistic['count'] }}</h4>
                        <small class="text-muted" style="font-size: 0.75rem;">{{ $statistic['name'] }}</small>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="bg-light p-3 rounded-3 border">
            <label class="form-label small fw-bold text-secondary text-uppercase mb-2">Cek Resi Cepat</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0">
                    <i class="bi bi-search text-muted"></i>
                </span>
                <input type="text" class="form-control border-start-0 ps-2" id="no_resi_quick_track" placeholder="Masukkan No Resi...">
                <button class="btn btn-primary" type="button" onclick="openModalQuickTrack()">Lacak</button>
            </div>
            <small class="text-muted mt-2 d-block fst-italic" style="font-size: 0.7rem;">
                *Masukkan nomor dan tekan Enter untuk melihat detail popup.
            </small>
        </div>
    </div>
</div>

<script>
    {
        const modalQuickTrack = $('#modalQuickTrack');
        const modalQuickTrackBody = $('#modalQuickTrackBody');
        const noResiQuickTrack = $('#no_resi_quick_track');

        noResiQuickTrack.on('keypress', function (e) {
            if (e.which == 13) {
                openModalQuickTrack();
            }
        });

        function openModalQuickTrack(data) {
            const keyword = noResiQuickTrack.val();

            if(!keyword) {
                swal({
                    title: 'Oops...',
                    text: 'Data tidak boleh kosong',
                    icon: 'warning'
                });
                return;
            }

            modalQuickTrackBody.html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-3 text-muted">Mencari data <b>${keyword}</b>...</p>
                </div>
            `);

            modalQuickTrack.modal('show');

            // get data
            ajaxGet(`dashboard/widgets/track-search`, {keyword: keyword}, result => {
                modalQuickTrackBody.html(result.html);
            }, error => {
                modalQuickTrackBody.html(`
                    <div class="text-center py-5">
                        <i class="bi bi-exclamation-circle text-danger display-3"></i>
                        <p class="mt-3 text-muted">Data tidak ditemukan</p>
                    </div>
                `);
            }, {
                onErrorPopup: false
            });
        }
    }
</script>
