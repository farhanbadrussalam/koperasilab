@props(['tasks'])

<div class="card shadow-sm border-0 rounded-4 mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center rounded-top-4">
        <h6 class="m-0 font-weight-bold text-dark">
            <i class="bi bi-list-check me-2 text-primary"></i>Monitoring Surat Tugas
        </h6>
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                Semua Status
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#">Menunggu TTD</a></li>
                <li><a class="dropdown-item" href="#">Proses Lab</a></li>
            </ul>
        </div>
    </div>

    <div class="card-body p-3 custom-scrollbar" style="max-height: 60vh; overflow-y: auto;">

        @forelse($tasks as $task)
            <div class="card border-0 shadow-sm mb-3 border-start border-primary bg-light">
                <div class="card-body p-3">
                    <div class="row align-items-center">

                        <div class="col-md-5 mb-3 mb-md-0">
                            <div class="d-flex align-items-center mb-1">
                                <a href="#" class="fw-bold text-decoration-none text-primary" style="font-size: 1.05rem;">
                                    {{ $task['nomor_surat'] }}
                                </a>
                                <span class="badge bg-secondary-subtle text-secondary ms-2 small">
                                    {{ $task['periode'] }}
                                </span>
                            </div>
                            <h6 class="text-dark fw-bold mb-1">
                                <i class="bi bi-building me-1 text-muted"></i> {{ $task['nama_perusahaan'] }}
                            </h6>
                            <small class="text-muted d-block">
                                Ref: {{ $task['nomor_referensi'] }}
                            </small>
                            <div class="mt-2 d-flex align-items-center">
                                <div class="rounded-circle bg-secondary text-white d-flex justify-content-center align-items-center me-2"
                                     style="width: 24px; height: 24px; font-size: 0.7rem;">
                                    {{ substr($task['nama_petugas'], 0, 1) }}
                                </div>
                                <span class="small text-muted">{{ $task['nama_petugas'] }}</span>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="fw-bold text-primary">Step {{ $task['current_step'] }} / 6</small>
                                <small class="text-dark fw-bold">{{ $task['step_name'] }}</small>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-primary" role="progressbar"
                                     style="width: {{ ($task['current_step'] / 6) * 100 }}%"
                                     aria-valuenow="{{ $task['current_step'] }}" aria-valuemin="0" aria-valuemax="6">
                                </div>
                            </div>

                            @if($task['current_step'] >= 3)
                                <div class="mt-2 d-flex gap-2">
                                    <span class="badge {{ $task['is_labeled'] ? 'bg-success' : 'bg-warning text-dark' }} border">
                                        <i class="bi {{ $task['is_labeled'] ? 'bi-check-circle' : 'bi-exclamation-circle' }} me-1"></i>
                                        Label
                                    </span>
                                    <span class="badge {{ $task['is_stored'] ? 'bg-success' : 'bg-secondary' }} border">
                                        <i class="bi {{ $task['is_stored'] ? 'bi-check-circle' : 'bi-box-seam' }} me-1"></i>
                                        Simpan
                                    </span>
                                </div>
                            @endif
                        </div>

                        <div class="col-md-3 text-md-end text-start">
                            <a href="#" class="btn btn-sm btn-primary shadow-sm px-3 w-100 mb-2">
                                <i class="bi bi-eye me-1"></i> Detail
                            </a>
                            @if($task['current_step'] == 0)
                                <button class="btn btn-sm btn-outline-success w-100" title="Tanda Tangan Manager">
                                    <i class="bi bi-pen me-1"></i> TTD
                                </button>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5">
                <i class="bi bi-clipboard-check opacity-25" style="font-size: 3rem;"></i>
                <h6 class="text-muted fw-bold mt-3">Tidak ada surat tugas aktif</h6>
            </div>
        @endforelse

    </div>

    <div class="card-footer bg-white py-3 d-flex justify-content-end rounded-bottom-4">
        <nav aria-label="Page navigation example">
            <ul class="pagination pagination-sm mb-0 d-flex align-items-center justify-content-end mt-1">
                <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                </li>
                <li class="page-item"><a class="page-link" href="#">1</a></li>
                <li class="page-item active" aria-current="page">
                    <a class="page-link" href="#">2</a>
                </li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item">
                    <a class="page-link" href="#">Next</a>
                </li>
            </ul>
        </nav>
    </div>
</div>
