@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item active">Penyelia LAB</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content col-xl-8 col-md-12">
        <div class="container">
            <div class="card card-default color-palette-box shadow">
                <div class="card-body">
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="layanan-tab" data-bs-toggle="tab"
                                data-bs-target="#layanan-tab-pane" type="button" role="tab"
                                aria-controls="layanan-tab-pane" aria-selected="true">Layanan</button>
                        </li>
                    </ul>
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active pt-3" id="layanan-tab-pane" role="tabpanel"
                            aria-labelledby="layanan-tab" tabindex="0">
                            <table class="table table-borderless w-100" id="layanan-table"></table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
@push('scripts')
<script>
    let idPermohonan = false;
    let dt_layanan = false;

    $(function () {
        dt_layanan = $('#layanan-table').DataTable({
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
                url: "{{ route('jobs.getData') }}",
                data: function(d) {
                    d.jobs = 'penyelia';
                    d.type = 'layanan';
                }
            },
            columns: [
                { data: 'content', name: 'content', orderable: false, searchable: false}
            ]
        });
    })
</script>
@endpush
