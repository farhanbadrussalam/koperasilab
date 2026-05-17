@extends('layouts.main')

@section('content')
    <div class="content-wrapper row">
        <div class="col-md-12 p-2">
            <div class="card p-0 m-0 shadow border-0">
                <div class="card-body">
                    <h5>Document</h5>
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col" width="5%">No</th>
                                <th scope="col">Nama Document</th>
                                <th scope="col" width="20%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="list-body">
                        </tbody>
                    </table>
                    <div id="pagination-body"></div>
                </div>
            </div>
        </div>
        <div class="col-md-6 p-2">
            <div class="card p-0 m-0 shadow border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title">Header</h5>
                        <a href="{{ route('document.create', ['type' => 'header']) }}" class="btn btn-primary">Tambah</a>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col" width="5%">No</th>
                                <th scope="col">Nama Header</th>
                                <th scope="col" width="20%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="list-header">
                        </tbody>
                    </table>
                    <div id="pagination-header"></div>
                </div>
            </div>
        </div>

        <div class="col-md-6 p-2">
            <div class="card p-0 m-0 shadow border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title">Footer</h5>
                        <a href="{{ route('document.create', ['type' => 'footer']) }}" class="btn btn-primary">Tambah</a>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col" width="5%">No</th>
                                <th scope="col">Nama Header</th>
                                <th scope="col" width="20%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="list-footer">
                        </tbody>
                    </table>
                    <div id="pagination-footer"></div>
                </div>
            </div>
        </div>

        <table class="table mt-3">
        </table>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('js/management/document.js') }}"></script>
@endpush
