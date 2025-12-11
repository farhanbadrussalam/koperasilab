@extends('layouts.main')

@section('content')
<div class="card shadow-sm m-4 mt-2">
    <div class="card-body">
        <div class="d-flex">
            <div class="flex-grow-1">
                <button class="btn btn-outline-secondary btn-sm" onclick="reload()"><i class="bi bi-arrow-clockwise"></i> Refresh data</button>
                <button class="btn btn-outline-secondary btn-sm" onclick="clearFilter()">
                    <i class="bi bi-funnel"></i> Clear Filter <span class="badge text-bg-secondary d-none" id="countFilter">4</span>
                </button>
            </div>
        </div>
        <div id="list-filter"></div>
        <div class="my-3">
            <div class="body-placeholder my-3" id="list-placeholder-list">

            </div>
            <div class="body my-3" id="list-container-list">
            </div>
            <div aria-label="Page navigation example" id="list-pagination-list">

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/staff/pengiriman_permohonan.js') }}"></script>
@endpush
