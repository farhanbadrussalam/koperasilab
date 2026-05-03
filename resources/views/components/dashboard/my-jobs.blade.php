@props(['jobs'])
@php
    $url = url('staff/lhu').'?';
@endphp

<div class="card border-0 shadow-sm rounded-4 mb-3">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom rounded-top-4">
        <h6 class="m-0 font-weight-bold">
            <i class="bi bi-list-task me-2 text-primary"></i>Tugas Saya
        </h6>
        @if(count($jobs) > 0)
            <span class="badge bg-danger rounded-pill">{{ count($jobs) }} Pending</span>
        @endif
    </div>

    <div class="card-body p-3 custom-scrollbar" style="max-height: 40vh; overflow-y: auto;">

        @forelse($jobs as $job)
            <div class="card border-0 shadow-sm mb-3 border-start border-primary bg-light">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-1">
                        <span class="badge bg-primary me-2">
                            {{ $job['current_step_name'] }} </span>
                        <small class="text-muted">{{ $job['nomor_surat'] }}</small>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="font-weight-bold mb-1 text-dark">
                                {{ $job['nama_perusahaan'] }}
                            </h6>

                            <small class="text-danger">
                                <i class="bi bi-clock-history me-1"></i>
                                Deadline: {{ convert_date($job['deadline'], 4) }}
                            </small>
                        </div>

                        <div class="ms-3">
                            <a href="{{ $url . 'md='.$job['id_penyelia'] }}" class="btn btn-sm btn-primary shadow-sm"
                                 title="Kerjakan">
                                <i class="bi bi-play-fill me-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center pb-3">
                <div class="mb-3">
                    <i class="bi bi-cup-hot text-muted opacity-25" style="font-size: 4rem;"></i>
                </div>
                <h6 class="text-gray-800 font-weight-bold">Tidak Ada Tugas Aktif</h6>
                <p class="text-muted small mb-0 px-4">
                    Kerja bagus! Anda telah menyelesaikan semua tanggung jawab saat ini.
                    Silakan tunggu surat tugas berikutnya.
                </p>
            </div>
        @endforelse

    </div>
</div>
