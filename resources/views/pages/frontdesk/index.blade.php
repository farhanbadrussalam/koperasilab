@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active">Front desk</li>
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
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="kiplhu-tab" data-bs-toggle="tab"
                                    data-bs-target="#kiplhu-tab-pane" type="button" role="tab"
                                    aria-controls="kiplhu-tab-pane" aria-selected="true">KIP / LHU</button>
                            </li>
                        </ul>
                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade show active pt-3" id="layanan-tab-pane" role="tabpanel"
                                aria-labelledby="layanan-tab" tabindex="0">
                                Lorem ipsum dolor sit amet consectetur adipisicing elit. Ea voluptatum dicta totam, minus
                                earum quo veritatis beatae. Vitae, libero quod consequuntur molestias quos voluptates
                                debitis quasi nemo? Labore excepturi hic culpa. Harum impedit laudantium architecto ipsam ab
                                delectus fugiat eum aut totam dignissimos quo, obcaecati voluptatem at magnam? Dolore, esse?
                            </div>
                            <div class="tab-pane fade pt-3" id="kiplhu-tab-pane" role="tabpanel"
                                aria-labelledby="kiplhu-tab" tabindex="0">
                                Lorem ipsum dolor sit amet consectetur adipisicing elit. Exercitationem porro reiciendis
                                temporibus aperiam quia! Soluta harum mollitia dolorum blanditiis id. Odio, quos! Cum
                                explicabo maxime odio aliquid voluptates animi quidem voluptatum est distinctio dolorum
                                accusamus porro tempore magnam, dolor nobis.
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
    $(function() {
        $.ajax({
            url: "{{ route('frontdesk.getData') }}",
            method: "GET",
        }).success(result => {
            console.log(result);
        })
    })
</script>
@endpush
