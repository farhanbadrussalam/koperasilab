@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Users management</li>
                        <li class="breadcrumb-item active">Roles</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content col-xl-6 col-md-12">
        <div class="container">
            <div class="card card-default color-palette-box bg-white shadow">
                <div class="card-header d-flex ">
                    <h3 class="card-title flex-grow-1">
                      Roles
                    </h3>
                    <button class="btn btn-primary btn-sm">Add role</button>
                </div>
                <div class="card-body">
                    <table class="table table-hover  w-100" id="user-table">
                        <thead>
                            <th>ID</th>
                            <th>Name role</th>
                            <th>Action</th>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
