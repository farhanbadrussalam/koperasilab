@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('permohonan.index') }}">Pemohonan</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content col-xl-8 col-md-12">
        <div class="container">
            <div class="card card-default color-palette-box shadow bg-white">
                <div class="card-header d-flex ">
                    <h3 class="card-title flex-grow-1">
                      List layanan
                    </h3>
                </div>
                <div class="card-body">
                   <table class="table table-borderless w-100" id="listLayanan-table">
                   </table>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
@push('scripts')
    <script>
        let datatable_layanan = false;

        $(function () {
            datatable_layanan = $('#listLayanan-table').DataTable({
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
                ajax:{
                    url: "{{ route('permohonan.getDTListLayanan') }}"
                },
                columns: [
                    { data: 'content', name: 'content', orderable: false, searchable: false}
                ]
            })
        })
    </script>
@endpush
