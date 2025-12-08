@props(['stafflist'])
<div class="card border-0 shadow-sm rounded-4 mb-3">
    <div class="card-header bg-white py-3 border-bottom rounded-top-4">
        <h6 class="m-0 font-weight-bold text-dark">
            <i class="bi bi-people-fill me-2 text-primary"></i>Status Petugas Lab
        </h6>
    </div>
    <div class="card-body p-0">
        <ul class="list-group list-group-flush overflow-auto" style="max-height: 30vh;">
            @forelse($stafflist as $staff)
                <li class="list-group-item d-flex justify-content-between align-items-center py-3 hover-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle d-flex justify-content-center align-items-center me-3 text-white fw-bold shadow-sm"
                                style="width: 40px; height: 40px; background-color: {{ $staff['color'] }};">
                            {{ substr($staff['name'], 0, 1) }}
                        </div>

                        <div>
                            <h6 class="mb-0 text-sm font-weight-bold">{{ $staff['name'] }}</h6>
                            <small class="{{ $staff['workload_class'] }}">
                                {{ $staff['status_text'] }}
                            </small>
                        </div>
                    </div>

                    <div class="text-end">
                        <span class="badge rounded-pill {{ $staff['badge_class'] }}" style="font-size: 0.9em;">
                            {{ $staff['active_jobs_count'] }} Job
                        </span>
                    </div>
                </li>
            @empty
                <li class="list-group-item text-center text-muted py-2">
                    <i class="bi bi-people text-muted opacity-25" style="font-size: 4rem;"></i>
                    <h6 class="text-gray-800 font-weight-bold mt-3">Tidak Ada Petugas Terdaftar</h6>
                    <p class="text-muted small mb-0 px-4">
                        Data petugas laboratorium tidak ditemukan.
                    </p>
                </li>
            @endforelse
        </ul>
    </div>
    <div class="card-footer bg-white text-center py-2 rounded-bottom-4">
        <a href="#" class="text-decoration-none small fw-bold text-primary">Lihat Semua Petugas <i class="bi bi-arrow-right"></i></a>
    </div>
</div>
