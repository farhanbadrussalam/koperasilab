@extends('layouts.main')

@section('content')
<div class="m-4">
    <h4><i class="bi bi-person-badge me-2"></i> Penugasan PLT Saya</h4>
    @if($assignments->isEmpty())
        <div class="alert alert-info" role="alert">
            Tidak ada penugasan PLT aktif.
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Manajer Asli</th>
                                <th>Role</th>
                                <th>Masa Berlaku</th>
                                <th>Surat Tugas</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($assignments as $idx => $plt)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td>{{ $plt->originalUser->name ?? '-' }}</td>
                                <td>{{ $plt->role_name }}</td>
                                <td>
                                    {{ $plt->start_date ? $plt->start_date->format('d/m/Y') : '-' }}
                                    <i class="bi bi-arrow-right mx-1"></i>
                                    {{ $plt->end_date ? $plt->end_date->format('d/m/Y') : '-' }}
                                </td>
                                <td>
                                    @if($plt->surat_tugas_path)
                                        <a href="{{ asset('storage/' . $plt->surat_tugas_path) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                            <i class="bi bi-file-earmark-pdf"></i> Lihat File
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($plt->status == 1 && $plt->end_date >= now())
                                        <span class="badge bg-success">Aktif</span>
                                    @elseif($plt->status == 1 && $plt->end_date < now())
                                        <span class="badge bg-secondary">Kadaluarsa</span>
                                    @else
                                        <span class="badge bg-danger">Dicabut</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
