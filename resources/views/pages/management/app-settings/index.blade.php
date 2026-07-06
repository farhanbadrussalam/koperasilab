@extends('layouts.main')

@section('content')
    <div class="card p-0 m-0 shadow-sm border-0 rounded-3 overflow-hidden">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div class="d-flex align-items-center">
                    <div class="icon-box bg-primary-subtle text-primary rounded-circle p-2 me-3 d-flex align-items-center justify-content-center"
                        style="width: 45px; height: 45px;">
                        <i class="bi bi-gear-fill fs-5"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold text-dark">App Settings</h4>
                        <p class="text-muted small mb-0">Kelola pengaturan aplikasi seperti identitas, keuangan, dan teknis.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body px-4 pb-4 pt-4">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('app-settings.store') }}" method="POST">
                @csrf
                
                <ul class="nav nav-tabs" id="settingsTab" role="tablist">
                    @php $first = true; @endphp
                    @foreach($settings as $group => $items)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $first ? 'active' : '' }}" id="{{ $group }}-tab" data-bs-toggle="tab" data-bs-target="#{{ $group }}-tab-pane" type="button" role="tab" aria-controls="{{ $group }}-tab-pane" aria-selected="{{ $first ? 'true' : 'false' }}">
                                {{ ucfirst($group) }}
                            </button>
                        </li>
                        @php $first = false; @endphp
                    @endforeach
                </ul>

                <div class="tab-content pt-4" id="settingsTabContent">
                    @php $first = true; @endphp
                    @foreach($settings as $group => $items)
                        <div class="tab-pane fade {{ $first ? 'show active' : '' }}" id="{{ $group }}-tab-pane" role="tabpanel" aria-labelledby="{{ $group }}-tab" tabindex="0">
                            <div class="row">
                                @foreach($items as $item)
                                    <div class="col-md-6 mb-3">
                                        <label for="setting_{{ $item->key }}" class="form-label fw-bold">{{ ucwords(str_replace('_', ' ', $item->key)) }}</label>
                                        @if($item->key === 'lab_lokasi' || $item->key === 'lab_address' || $item->description)
                                            <small class="d-block text-muted mb-2">{{ $item->description }}</small>
                                        @endif
                                        <input type="text" class="form-control" id="setting_{{ $item->key }}" name="{{ $item->key }}" value="{{ $item->value }}">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @php $first = false; @endphp
                    @endforeach
                </div>

                <div class="d-flex justify-content-end mt-4 border-top pt-3">
                    <button type="submit" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm d-flex align-items-center gap-2">
                        <i class="bi bi-save"></i>
                        <span>Simpan Pengaturan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
