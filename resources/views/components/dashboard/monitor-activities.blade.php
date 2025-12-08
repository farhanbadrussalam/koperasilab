@props(['activities'])
<div class="card border-0 shadow-sm rounded-4 mb-3">
    <div class="card-header bg-white py-3 border-bottom rounded-top-4">
        <h6 class="m-0 font-weight-bold text-dark">
            <i class="bi bi-activity me-2 text-warning"></i>Aktivitas Terbaru
        </h6>
    </div>
    <div class="card-body p-3">
        <div class="timeline-simple overflow-auto" style="max-height: 30vh;">
            @forelse($activities as $log)
                <div class="d-flex mb-3">
                    <div class="me-3 mt-1">
                        @if($log['type'] == 'approve')
                            <i class="bi bi-check-circle-fill text-success"></i>
                        @elseif($log['type'] == 'job_done')
                            <i class="bi bi-file-earmark-check-fill text-primary"></i>
                        @else
                            <i class="bi bi-info-circle-fill text-muted"></i>
                        @endif
                    </div>
                    <div>
                        <p class="mb-0 small text-dark fw-bold">{{ $log['message'] }}</p>
                        <small class="text-muted" style="font-size: 0.75rem;">
                            {{ $log['time_ago'] }}
                        </small>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-4">
                    <i class="bi bi-inbox text-muted opacity-25" style="font-size: 4rem;"></i>
                    <h6 class="text-gray-800 font-weight-bold mt-3">Tidak Ada Aktivitas Terbaru</h6>
                    <p class="text-muted small mb-0 px-4">
                        Aktivitas terbaru akan ditampilkan di sini.
                    </p>
                </div>
            @endforelse
        </div>
    </div>
</div>
