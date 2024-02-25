@extends('layouts.main')

@section('content')
<div class="card p-0 m-0 shadow border-0">
    <div class="card-body">
        <div class="row mt-2">
            <div class="mb-4 d-flex justify-content-between ">
                <div class="d-flex">

                </div>
                <div class="">
                    <div class="input-group">
                        <input type="text" name="search" id="search" class="form-control">
                        <button class="btn btn-info" id="btn-search"><i class="bi bi-search"></i></button>
                    </div>
                </div>
            </div>
            <div class="overflow-y-auto">
                <div>
                    <div id="skeleton-container" class="placeholder-glow">
                        @for ($a=0; $a < 5; $a++)
                        <div class="placeholder rounded w-100 mb-2 bg-secondary" style="height: 50px;"></div>
                        @endfor
                    </div>
                    <div id="content-container">
                    </div>
                    <div id="pagination-container"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')

@endpush
