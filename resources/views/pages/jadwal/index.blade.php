@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Jadwal</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content col-md-12">
        <div class="container">
            <div class="card card-default color-palette-box shadow">
                <div class="card-header d-flex ">
                    <h3 class="card-title flex-grow-1">
                      Jadwal layanan
                    </h3>
                    @can('Penjadwalan.create')
                    <a href="{{ route('jadwal.create') }}" class="btn btn-primary btn-sm">Add jadwal</a>
                    @endcan
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap">
                        <div class="m-2">
                            <label for="filterInputSearch" class="form-label">Searching</label>
                            <div>
                                <input type="text" name="filterInputSearch" id="filterInputSearch" class="form-control" aria-describedby="btnSearch" placeholder="Name layanan">
                            </div>
                        </div>
                        @can('Penjadwalan.confirm')
                        <div class="m-2">
                            <label for="filterStatus" class="form-label">Status</label>
                            <select name="filterStatus" id="filterStatus" class="form-select">
                                <option value="" selected>All</option>
                                <option value="{{ encryptor(1) }}">Confirm</option>
                                <option value="{{ encryptor(2) }}">Bersedia</option>
                                <option value="{{ encryptor(9) }}">Menolak</option>
                            </select>
                        </div>
                        @endcan
                        <div class="m-2">
                            <label for="filterPrice" class="form-label">Price</label>
                            <div class="input-group">
                                <span class="input-group-text" id="">Rp</span>
                                <input type="text" name="filterPriceMin" id="filterPriceMin" class="form-control rupiah" placeholder="Price Minimum">
                            </div>
                        </div>
                        <div class="m-2">
                            <label for="filterPrice" class="form-label">&nbsp;</label>
                            <div class="input-group">
                                <span class="input-group-text" id="">Rp</span>
                                <input type="text" name="filterPriceMax" id="filterPriceMax" class="form-control rupiah" placeholder="Price Maximum">
                            </div>
                        </div>
                        <div class="m-2 col-4">
                            <label for="filterStartDate" class="form-label">Start date</label>
                            <input type="text" name="filterStartDate" id="filterStartDate" class="form-control" placeholder="Choose Date" />
                        </div>
                        <div class="m-2">
                            <label for="btnFilter" class="form-label">&nbsp;</label>
                            <div>
                                <button class="btn btn-outline-secondary" type="button" id="btnFilter">Filter</button>
                            </div>
                        </div>
                    </div>
                    <table class="table table-borderless w-100" id="jadwal-table">
                        <thead>
                            <th></th>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
@push('scripts')
    <script>
        let datatable_jadwal = false;
        $(function () {
            datatable_jadwal = $('#jadwal-table').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ordering: false,
                lengthChange: false,
                infoCallback: function( settings, start, end, max, total, pre ) {
                    var api = this.api();
                    var pageInfo = api.page.info();

                    return 'Page '+ (pageInfo.page+1) +' of '+ pageInfo.pages;
                },
                ajax: {
                    url: "{{ route('jadwal.getData') }}",
                    data: function (d) {
                        d.search = $('#filterInputSearch').val(),
                        d.status = $('#filterStatus').val(),
                        d.priceMin = $('#filterPriceMin').val(),
                        d.priceMax = $('#filterPriceMax').val(),
                        d.startDate = $('#filterStartDate').val()
                    }
                },
                columns: [
                    { data: 'content', name: 'content', orderable: false, searchable: false}
                ]
            });
            $('#filterStartDate').flatpickr({
                mode: "range"
            });

            $('#btnFilter').on('click', obj => {
                datatable_jadwal?.ajax.reload();
            })
        });
            // { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false }

        function btnDelete(id) {
            deleteGlobal(() => {
                $.ajax({
                    url: "{{ url('/api/deleteJadwal') }}/"+id,
                    method: 'DELETE',
                    dataType: 'json',
                    processData: true,
                    headers: {
                        'Authorization': `Bearer {{ $token }}`,
                        'Content-Type': 'application/json'
                    }
                }).done((result) => {
                    if(result.message){
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: result.message
                        });
                        datatable_jadwal?.ajax.reload();
                    }
                }).fail(function(message) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: message.responseJSON.message
                    });
                });
            });
        }
    </script>
@endpush
